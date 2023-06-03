<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\Authentication;


use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\Tables;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\Pure;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use stdClass;

class Session
{

    const SessionCategories_AuthInfo = 'auth_info';
    const SessionCategories_AuthInfo_UserTable = 'user_table';
    const SessionCategories_AuthInfo_UserEmail = 'email';
    const SessionCategories_AuthInfo_UserName = 'auth_info.user_name';
    const SessionCategories_AuthInfo_Role = 'role';

    const SessionCategories_OldFormInput = 'old_form_input';
    const SessionCategories_FlashMessageError = 'tonics_flash_message.errorMessage';
    const SessionCategories_FlashMessageInfo = 'tonics_flash_message.infoMessage';
    const SessionCategories_FlashMessageSuccess = 'tonics_flash_message.successMessage';

    const SessionCategories_CSRFToken = 'tonics_csrf_token';
    const SessionCategories_URLReferer = 'tonics_url_referer';
    const SessionCategories_WordPressImport = 'tonics_wordpress_import';

    const SessionCategories_PasswordReset = 'tonics_password_reset_info';
    const SessionCategories_NewVerification = 'tonics_user_new_verification';

    private string $table;

    public function __construct()
    {
        $this->table = Tables::getTable(Tables::SESSIONS);
    }

    /**
     * Check if session exist
     * @return bool
     */
    public function sessionExist(): bool
    {
        return !empty($this->getCookieID());
    }

    /**
     * Check if session Exist, if it doesn't we create one,
     * meaning it create a sessionID in cookie.
     *
     * <br>
     * Note: Won't touch the db here yet, this is just generating a sessionID in cookie if it doesn't already exist.
     * It would touch DB as soon as you start writing
     * @throws \Exception
     */
    public function startSession(): void
    {
        if ($this->sessionExist() === false) {
            ## The session would only be created in the db as soon as you start writing to it.
            $this->updateSessionIDInCookie($this->generateSessionID());
        }
    }

    /**
     * @param string $sessionName
     * @return mixed
     */
    public function getCookieID(string $sessionName = ''): mixed
    {
        $sessionName = (empty($sessionName)) ? $this->sessionName() : $sessionName;
        return  $_COOKIE[$sessionName] ?? '';
    }

    /**
     * Returns all session data
     * @param bool $array
     * By default, it would return a stdclass, setting to true would return an array
     * @return mixed
     * @throws \Exception
     */
    public function read(bool $array = false): mixed
    {
        if ($this->sessionExist() === false || AppConfig::TonicsIsNotReady()) {
            return '';
        }

        $data = null;
        db(onGetDB: function ($db) use (&$data){
            try {
                $data = $db->row(<<<SQL
SELECT * FROM {$this->getTable()} WHERE session_id = ?
SQL, $this->getCookieID());
            } catch (\Exception $exception){
                $data = null;
            }
        });

        if (is_null($data)) {
            return '';
        }

        ## Not necessary but if the event_scheduler hasn't cleared the old session, then we wouldn't want to return it
        ## Should we clear the old session ourselves here? yes or we get a bug where no data would be written in session
        if ($this->isOldSession($data)){
            $this->logout();
            return '';
        }

        if (helper()->isJSON($data->session_data ?? '')){
            return ($array) ? json_decode($data->session_data, true) : json_decode($data->session_data);
        }

        return '';
    }


    /**
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function isOldSession($data): bool
    {
        if (is_object($data) && isset($data->updated_at)){
            $dateTime = helper()->date();
            $updatedAt = $data->updated_at;
            if ($updatedAt < $dateTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $key
     * You can alo use dot-notation to access nested data type, e.g
     * data.nested.nested2
     * @param ?string $default
     * Returns default if $key value is empty
     * @param bool $jsonDecode
     * @param bool $jsonEncodeAsArray
     * @return mixed
     * @throws \Exception
     */
    public function retrieve(string $key, ?string $default = '', bool $jsonDecode = false, bool $jsonEncodeAsArray = false): mixed
    {
        if ($this->sessionExist()) {

            $res = $this->getValue($key);
            if (!$res) {
                return '';
            }

            if (isset($res->row)){
                if ($jsonDecode) {
                    $asArray = $jsonEncodeAsArray === true;
                    return json_decode($res->row, $asArray);
                }
                return $res->row;
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
        if ($this->sessionExist()) {

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
        if ($this->sessionExist() && AppConfig::TonicsIsReady()) {

            $res = null;
            db(onGetDB: function ($db) use ($key, &$res){
                $sessionID = $this->getCookieID();
                $jsonPath = '$.' . $key;
                try {
                    $res = $db->row(<<<SQL
SELECT JSON_EXISTS(session_data , ?) AS row FROM {$this->getTable()} WHERE session_id = ?;
SQL, $jsonPath, $sessionID);
                }catch (\Exception $exception){
                    $res = null;
                }
            });

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
        if ($this->hasKey(Session::SessionCategories_CSRFToken) === false){
            $this->append(Session::SessionCategories_CSRFToken, helper()->randomString());
        }

        return $this->retrieve(Session::SessionCategories_CSRFToken, jsonDecode: true);
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
        $res = null;
        if (AppConfig::TonicsIsReady()){
            db(onGetDB: function ($db) use ($key, &$res) {
                $sessionID = $this->getCookieID();
                try {
                    $res =  $db->Select()->JsonExtract('session_data', $key)
                        ->As('row')
                        ->From($this->getTable())
                        ->WhereEquals('session_id', $sessionID)->FetchFirst();
                }catch (\Exception $exception){
                    $res = false;
                }

            });
        }

        return $res;
    }


    /**
     * @param array $sessionData
     * @return bool
     * @throws \Exception
     */
    public function write(array $sessionData): bool
    {
        $stm = null;
        if ($this->sessionExist() && AppConfig::TonicsIsReady()) {
            db(onGetDB: function ($db) use ($sessionData, &$stm){
                $sessionID = $this->getCookieID();
                ## Add 1 hours to the current time
                // $dateTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $toSave = [
                    'session_id' => $sessionID,
                    'session_data' => json_encode($sessionData),
                ];

                $stm = $db->insertOnDuplicate(
                    table: $this->getTable(),
                    data: $toSave,
                    update: ['session_id', 'session_data'],
                    chunkInsertRate: 1);
            });
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
        if (!empty($sessionData)){
            foreach ($keys as $k) {
                $sessionData = $sessionData->{$k} ?? '';
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
        $js = strtolower($js);
        $messages = $this->retrieve($key, default: true, jsonDecode: true);

        if ($js === 'true') {
            return helper()->htmlSpecChar(json_encode($messages));
        }

        if (is_object($messages)){
            $messages = (array)$messages;
        }

        if (!is_array($messages)){
            return '';
        }

        $output = "<ul class='form-error'>";
        foreach ($messages as $msg) {
            if (str_starts_with($key, self::SessionCategories_FlashMessageError)){
                $output .= "<li><span class='text list-error-span'>⚠</span>$msg</li>";
            } elseif (str_starts_with($key, self::SessionCategories_FlashMessageInfo)){
                $output .= "<li><span class='text list-info-span'>⚠</span>$msg</li>";
            } else {
                $output .= "<li><span class='text list-success-span'>⚠</span>$msg</li>";
            }
        }
        $output .= "</ul>";
        return $output;
    }

    /**
     * Regenerate a session_id.
     *
     * <br>
     * Note: if you are using this function in a loop, and you are using Nginx or other web server,
     * increase the fastcgi_buffers and fastcgi_buffer_size for Nginx (find similar functionality in other webserver) to avoid crashing d server due to the fact that it may send a lot of http header.
     * I assume that d web server would keep appending `set-cookie:  ...`(the last one appended would take effect if they are the same cookie) which increases the header size.
     *
     * @return bool
     * @throws \Exception
     */
    public function regenerate(): bool
    {
        $result = false;
        if ($this->sessionExist() && AppConfig::TonicsIsReady()) {
            db(onGetDB: function (TonicsQuery $db) use (&$result){
                $db->beginTransaction();
                $oldSessionID = $this->getCookieID();
                $newSessionID = $this->generateSessionID();
                $db->row("UPDATE {$this->getTable()} SET session_id = ? WHERE session_id = ?", $newSessionID, $oldSessionID);
                $this->updateSessionIDInCookie($newSessionID);
                $result = $db->commit();
            });
        }
        return $result;
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

        if (AppConfig::TonicsIsNotReady()){
            return false;
        }

        if ($sessionID === null) {
            if ($this->sessionExist()) {
                $sessionID = $this->getCookieID();
            } else {
                return false;
            }
        }

        try {
            db(onGetDB: function (TonicsQuery $db) use ($sessionID){
               $db->run("DELETE FROM {$this->getTable()} WHERE `session_id` = ?", $sessionID);
            });

            return true;
        }catch (\Exception $exception){
            // Log..
            return false;
        }
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }


    public function sessionName(): string
    {
        return 'bt_sessionID';
    }

    /**
     * @throws \Exception
     */
    public function generateSessionID(): string
    {
        return helper()->randString();
    }

    /**
     * @param $sessionID
     */
    public function updateSessionIDInCookie($sessionID): void
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

    /**
     * @throws \Exception
     */
    public function logout()
    {
        $this->clear();
        $this->updateSessionIDInCookie($this->generateSessionID());
    }

}