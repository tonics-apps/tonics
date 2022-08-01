<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Library\Authentication;


use App\Modules\Core\Library\Tables;
use JetBrains\PhpStorm\Pure;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use stdClass;

class Session
{

    const SessionCategories_AuthInfo = 'auth_info';
    const SessionCategories_AuthInfo_UserType = 'user_type';
    const SessionCategories_AuthInfo_Role = 'role';

    const SessionCategories_OldFormInput = 'old_form_input';
    const SessionCategories_FlashMessageError = 'bt_flash_message_tonicsErrorMessage';
    const SessionCategories_FlashMessageInfo = 'bt_flash_message_tonicsInfoMessage';
    const SessionCategories_FlashMessageSuccess = 'bt_flash_message_tonicsSuccessMessage';

    const SessionCategories_CSRFToken = 'tonics_csrf_token';
    const SessionCategories_URLReferer = 'tonics_url_referer';
    const SessionCategories_WordPressImport = 'tonics_wordpress_import';

    private string $table;

    public function __construct()
    {
        $this->table = Tables::getTable(Tables::SESSIONS);
    }

    /**
     * Check if session exist
     * @return bool
     */
    #[Pure] public function sessionExist(): bool
    {
        return !empty($_COOKIE[$this->sessionName()]);
    }

    /**
     * @throws \Exception
     */
    public function startSession()
    {
        if ($this->sessionExist() === false) {
            ## The session would only be created in the db as soon as you start writing to it.
            $this->updateSessionIDInCookie($this->generateSessionID());
        }
    }


    /**
     * Returns all session data
     * @param bool $returnArray
     * By default, it would return a stdclass, setting to true would return an array
     * @return mixed
     * @throws \Exception
     */
    public function read(bool $returnArray = false): mixed
    {
        if ($this->sessionExist() === false) {
            return '';
        }

        $stm = db()->row(<<<SQL
SELECT `session_data`, `updated_at` FROM {$this->getTable()} WHERE session_id = ?
SQL, $_COOKIE[$this->sessionName()]);
        if (is_null($stm)) {
            return '';
        }

        ## Not necessary but if the event_scheduler hasn't cleared the old session, then we wouldn't want to return it
        ## Should we clear the old session ourselves here? yes or we get a bug where no data would be written in session
        $dateTime = helper()->date();
        $updatedAt = $stm->updated_at;
        if ($updatedAt < $dateTime) {
            session()->clear($_COOKIE[$this->sessionName()]);
            return '';
        }

        if ($returnArray) {
            return json_decode($stm->session_data, true);
        }

        return json_decode($stm->session_data);
    }

    /**
     * @param string $key
     * You can alo use dot-notation to access nested data type, e.g
     * data.nested.nested2
     * @param ?string $default
     * Returns default if $key value is empty
     * @param bool $jsonDecode
     * @return mixed
     * @throws \Exception
     */
    public function retrieve(string $key, ?string $default = '', bool $jsonDecode = false): mixed
    {
        if ($this->sessionExist()) {

            $res = $this->getValue($key);
            if ($res === null) {
                return '';
            }

            if (property_exists($res, 'row')) {
                if ($jsonDecode) {
                    return ($res->row === null) ? '' : json_decode($res->row, true);
                }
                return ($res->row === null) ? '' : $res->row;
            }
            return $default ?? '';
        }

        return '';
    }

    /**
     * Delete key in session data.
     * @param string $key
     * Pass multiple keys by adding semi-colon, e.g, key1, key2, key3
     * @param string|bool $wildCard
     * @return bool
     * @throws \Exception
     */
    public function delete(string $key, string|bool $wildCard = 'false'): bool
    {
        if ($this->sessionExist()) {
            if (is_string($wildCard)){
                $wildCard = strtolower($wildCard);
            }

            $keysToDelete = explode(',', $key);

            $sessionData = $this->read(true);
            $return = false;
            if (is_array($sessionData)) {
                foreach ($sessionData as $sessionDataKey => $sessionDataValue) {
                    foreach ($keysToDelete as $keysToDeleteValue) {
                        if ($wildCard === 'true' || $wildCard === true) {
                            if (str_starts_with($sessionDataKey, $keysToDeleteValue)) {
                                $return = true;
                                unset($sessionData[$sessionDataKey]);
                            }
                        } else {
                            if ($keysToDeleteValue === $sessionDataKey) {
                                $return = true;
                                unset($sessionData[$sessionDataKey]);
                            }
                        }

                    }
                }
            }
            if (!empty($sessionData)){
                $this->write($sessionData);
            }
            return $return;

        }

        return false;
    }

    /**
     * Append data to session data, to append unto a key, pass the key name in $key,
     * note the key data must be an array. If the key doesn't already exist, it would be created.
     * @param string $key
     * @param mixed $data
     * @return void
     * @throws \Exception
     */
    public function append(string $key, array|stdClass|string $data): void
    {
        // dd($this->sessionExist(), $_COOKIE[$this->sessionName()], $this->read(true));
        if ($this->sessionExist()) {
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }

            $sessionData = $this->read(true);
            if (empty($sessionData)) {
                $this->write([$key => $data]);
                return;
            }

            $keys = explode('.', $key);
            $sessionDataRef = &$sessionData;
            foreach ($keys as $k) {
                if (!key_exists($k, $sessionDataRef)) {
                    $sessionDataRef[$k] = [];
                }
                $sessionDataRef = &$sessionDataRef[$k];
            }

            $sessionDataRef = $data;
            $this->write($sessionData);
        }
    }

    /**
     * Check if a $key is present,
     * use hasValue to check if the key is present and value is not empty
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function hasKey(string $key): bool
    {
        if ($this->sessionExist()) {
            $sessionID = $_COOKIE[$this->sessionName()];
            $jsonPath = '$.' . $key;

            $res = db()->row(<<<SQL
SELECT JSON_EXISTS(session_data , ?) AS row FROM {$this->getTable()} WHERE session_id = ?;
SQL, $jsonPath, $sessionID);

            if ($res === null) {
                ## return false, meaning, $sessionID is invalid or $JSON_KEY no exists
                return false;
            }

            // if row is null, it would fall down since null is nothing in isset
            if (isset($res->row)){
                # if row is 0, we return false, else true
                return !(($res->row === 0));
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function getCSRFToken()
    {
        if (session()->hasKey(Session::SessionCategories_CSRFToken) === false){
            session()->append(Session::SessionCategories_CSRFToken, helper()->randomString());
        }
        return session()->retrieve(Session::SessionCategories_CSRFToken, jsonDecode: true);
    }


    /**
     * If oldFormInput exist and is equals $equals, return $return
     * @param $key
     * @param $equals
     * @param string $return
     * @return mixed
     * @throws \Exception
     */
    public function isOldFormInputEquals($key, $equals, string $return = ''): mixed
    {
        if (!str_starts_with($key, self::SessionCategories_OldFormInput .'.')){
            $key = self::SessionCategories_OldFormInput .'.' . $key;
        }
        $keys = explode('.', $key);
        $root = array_shift($keys);
        $formData = $this->getValue($root);
        if ($formData !== false && isset($formData->row)){
            $data = $formData->row;
            if (is_string($data)){
                $data = json_decode($data);
                if (!is_object($data)){
                    return '';
                }
            }
            foreach ($keys as $k) {
                if (property_exists($data, $k)) {
                    $data = $data->{$k};
                } else {
                    break;
                }
            }

            if ($data === $equals){
                return $return;
            }
        }
        return  '';
    }

    /**
     * Check if $key is present and has value
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function hasValue(string $key): bool
    {
        if ($this->sessionExist()) {
            $res = $this->getValue($key);
            if ($res === null) {
                ## return false, meaning, $sessionID is invalid or $JSON_KEY no exists
                return false;
            }

            if (is_object($res) && property_exists($res, 'row')) {
                ## If result is null, return false, else true
                return !(($res->row === null));
            }
        }

        return false;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    private function getValue(string $key): mixed
    {
        $sessionID = $_COOKIE[$this->sessionName()];

        $jsonPath = '$.' . $key;
        return db()->row(<<<SQL
SELECT JSON_EXTRACT(session_data , ?) AS row FROM {$this->getTable()} WHERE session_id = ?;
SQL, $jsonPath, $sessionID);
    }


    /**
     * @param array $sessionData
     * @return bool
     * @throws \Exception
     */
    public function write(array $sessionData): bool
    {
        $stm = null;
        if ($this->sessionExist()) {
            $sessionID = $_COOKIE[$this->sessionName()];
            ## Add 1 hours to the current time
            // $dateTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $toSave = [
                'session_id' => $sessionID,
                'session_data' => json_encode($sessionData),
            ];
            $stm = db()->insertOnDuplicate(
                table: $this->getTable(),
                data: $toSave,
                update: ['session_id', 'session_data'],
                chunkInsertRate: 1);
        }

        return $stm !== null;
    }

    /**
     * @param array|stdClass $data
     * @param array|stdClass|null $formInput
     * FormInput would store the $formInput in "old_form_input", this way you can use it for old-form-input retrieval
     * @param string $type
     * @return void
     * @throws \Exception
     */
    public function flash(array|stdClass $data, array|stdClass $formInput = null, string $type = self::SessionCategories_FlashMessageError): void
    {
        if ($formInput !== null) {
            $this->oldFormInput($formInput);
        }

        if ($type === self::SessionCategories_FlashMessageError) {
            $this->append(self::SessionCategories_FlashMessageError, $data);
        } elseif ($type === self::SessionCategories_FlashMessageInfo) {
            $this->append(self::SessionCategories_FlashMessageInfo, $data);
        } else {
            $this->append(self::SessionCategories_FlashMessageSuccess, $data);
        }
    }

    /**
     * Flash old form input, would be stored under old_form_input
     * @param array|stdClass $formInput
     * @return void
     * @throws \Exception
     */
    public function oldFormInput(array|stdClass $formInput): void
    {
        $this->append(self::SessionCategories_OldFormInput, $formInput);
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     * @throws \Exception
     */
    public function getOldFormInput(string $key, string $default = ''): mixed
    {
        if (!str_starts_with($key, self::SessionCategories_OldFormInput .'.')){
            $key = self::SessionCategories_OldFormInput .'.' . $key;
        }
        $keys = explode('.', $key);
        $root = array_shift($keys);
        $sessionData = $this->retrieve($root, default: true, jsonDecode: true);
        if (is_string($sessionData) && !empty($sessionData)) {
            $sessionData = json_decode($sessionData);
            if (!empty($sessionData)){
                foreach ($keys as $k) {
                    if (property_exists($sessionData, $k)) {
                        $sessionData = $sessionData->{$k};
                    } else {
                        $sessionData = '';
                    }
                }
            }
        }
        if (empty($sessionData)) {
            return $default;
        }
        return $sessionData;
    }

    /**
     * Render a li of error messages
     * @throws \Exception
     */
    public function renderFlashMessages(string $key, string $js = 'false'): string
    {
        $keyExploded = explode('.', $key);
        $js = strtolower($js);
        $keyExplodedCount = count($keyExploded);
        $root = array_shift($keyExploded);
        $messages = $this->retrieve($root, default: true, jsonDecode: true);

        if (is_string($messages) && !empty($messages)) {
            $messages = json_decode($messages);
            if (is_object($messages)) {
                $messages = json_decode(json_encode($messages), true) ?? [];
            }
            foreach ($keyExploded as $k) {
                if (key_exists($k, $messages)) {
                    $messages = $messages[$k];

                    // nested key, doesn't exist, return empty string
                } elseif ($keyExplodedCount > 1){
                    return '';
                }
            }
        }

        if (is_array($messages)) {
            // flatten nested array
            $messages = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($messages)), 0);
        }

        if (!is_array($messages)) {
            $messages = [];
        }

        if ($js === 'true') {
            return json_encode($messages);
        }

        $output = "<ul class='form-error'>";
        foreach ($messages as $msg) {
            $output .= "<li><span class='text list-error-span'>⚠</span>$msg</li>";
        }
        $output .= "</ul>";
        return $output;
    }

    /**
     * Regenerate a session_id.
     *
     * <br>
     * Note: if you are using this function in a loop and you are using Nginx or other web server,
     * increase the fastcgi_buffers and fastcgi_buffer_size for Nginx (find similar functionality in other webserver) to avoid crashing d server due to the fact that it may send a lot of http header.
     * I assume that d web server would keep appending `set-cookie:  ...`(the last one appended would take effect if they are the same cookie) which increases the header size.
     *
     * @return bool
     * @throws \Exception
     */
    public function regenerate(): bool
    {
        if ($this->sessionExist()) {
            db()->beginTransaction();
            $oldSessionID = $_COOKIE[$this->sessionName()];
            $newSessionID = $this->generateSessionID();
            db()->row("UPDATE {$this->getTable()} SET session_id = ? WHERE session_id = ?", $newSessionID, $oldSessionID);
            $this->updateSessionIDInCookie($newSessionID);
            return db()->commit();
        }

        return false;
    }

    /**
     * Removes a sessionID from the DB storage
     *
     * @param null $sessionID
     * If the sessionID is null, it would check if the cookie has a sessionID,
     * if it does, it would use that instead to remove the sessionID
     * @return bool
     * @throws \Exception
     */
    public function clear($sessionID = null): bool
    {
        if ($sessionID === null) {
            if ($this->sessionExist()) {
                $sessionID = $_COOKIE[$this->sessionName()];
            } else {
                return false;
            }
        }

        db()->beginTransaction();
        db()->run("DELETE FROM {$this->getTable()} WHERE `session_id` = ?", $sessionID);
        return db()->commit();
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }


    private function sessionName(): string
    {
        return 'bt_sessionID';
    }

    /**
     * @throws \Exception
     */
    private function generateSessionID(): string
    {
        return helper()->randString();
    }

    /**
     * @param $sessionID
     */
    private function updateSessionIDInCookie($sessionID)
    {
        $cookieOptions = array(
            'expires' => strtotime('+12 hours'),
            'secure' => true,     // or false
            'httponly' => true,    // or false
            'samesite' => 'Lax', // None || Lax  || Strict
            'path' => '/',
        );
        setcookie($this->sessionName(), $sessionID, $cookieOptions);
        ## Force the cookie to set on current request
        $_COOKIE[$this->sessionName()] = $sessionID;
    }
}