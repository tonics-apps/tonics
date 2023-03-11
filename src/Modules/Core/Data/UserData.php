<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use JetBrains\PhpStorm\ArrayShape;

class UserData extends AbstractDataLayer
{

    const ADMIN_REDIRECTION_NAME = 'admin';
    const CUSTOMER_REDIRECTION_NAME = 'customer';

    /**
     * @throws \Exception
     */
    public static function USER_REDIRECTION_ADMIN_PAGE(): array
    {
        return [
            self::ADMIN_REDIRECTION_NAME => route('admin.dashboard'),
            self::CUSTOMER_REDIRECTION_NAME => route('customer.dashboard')
        ];
    }

    public function getUsersColumn()
    {
        return Tables::$TABLES[Tables::USERS];
    }

    public function getUsersTable(): string
    {
        return Tables::getTable(Tables::USERS);
    }

    public function getRolesTable(): string
    {
        return Tables::getTable(Tables::ROLES);
    }

    public function getCustomersColumn()
    {
        return Tables::$TABLES[Tables::CUSTOMERS];
    }

    public function getCustomersTable(): string
    {
        return Tables::getTable(Tables::CUSTOMERS);
    }

    /**
     * @param array $userData
     * @param array $return
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function insertForUser(array $userData, array $return = []): bool|\stdClass
    {
        if (empty($return)) {
            $return = $this->getUsersColumn();
        }

        try {
            $returning = null;
            db(onGetDB: function ($db) use ($return, $userData, &$returning){
                $returning = $db->insertReturning($this->getUsersTable(), $userData, $return, 'user_id');
            });
            return $returning;
        } catch (\Exception $exception) {
            // Log..
        }
        return false;
    }

    /**
     * @param array $userData
     * @param array $return
     * @return bool|\stdClass
     * @throws \Exception
     */
    public function insertForCustomer(array $userData, array $return = []): bool|\stdClass
    {
        if (empty($return)) {
            $return = $this->getCustomersColumn();
        }

        try {
            $returning = null;
            db(onGetDB: function ($db) use ($return, $userData, &$returning){
                $returning = $db->insertReturning($this->getCustomersTable(), $userData, $return, 'customer_id');
            });
            return $returning;
        } catch (\Exception $exception) {
            // Log..
        }
        return false;
    }


    /**
     * Validates User Data, if validation is true, store the userInfo in auth session, regenerates the session_id, and return the data,
     * you get false if the user data is invalid
     * @param string $email
     * @param string $pass
     * @return bool
     * @throws \Exception
     */
    public function validateUser(string $email, string $pass): bool
    {
        $userInfo = null;
        db(onGetDB: function ($db) use ($email, &$userInfo){
            $userTable = $this->getUsersTable();
            $rolesTable = $this->getRolesTable();

            $userInfo = $db->Select('*, role_id as role')->From($userTable)
                ->Join($rolesTable, "{$rolesTable}.id", "{$userTable}.role")
                ->WhereEquals('email', $email)
                ->FetchFirst();
        });

        $verifyPass = false;
        if ($userInfo instanceof \stdClass) {
            $verifyPass = helper()->verifyPassword($pass, $userInfo->user_password);
            $userInfo->user_table = Tables::USERS;
            unset($userInfo->user_password);
        }

        if ($verifyPass === false) {
            return false;
        }
        $settings = json_decode($userInfo->settings);

        ## Saving User settings in session auth_info might be unnecessary and won't sync, but we would see.
        ## $userInfo->{'settings'} = json_decode( $userInfo->{'settings'});
        session()->append(Session::SessionCategories_AuthInfo, $userInfo);
        session()->regenerate();

        # Store the active session
        if (is_object($settings)) {
            if (!property_exists($settings, 'active_sessions')) {
                $settings->active_sessions = [session()->getCookieID(session()->sessionName())];
            } else {
                $settings->active_sessions[] = session()->getCookieID(session()->sessionName());
            }

            db(onGetDB: function ($db) use ($userInfo, $settings) {
                $db->FastUpdate($this->getUsersTable(), ['settings' => json_encode($settings)], db()->Where('email', '=', $userInfo->email));
            });
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public function validateCustomer(string $email, string $pass): bool
    {
        $userInfo = null;
        db(onGetDB: function ($db) use ($email, &$userInfo){
            $userTable = $this->getCustomersTable();
            $rolesTable = $this->getRolesTable();
            $userInfo =  $db->Select('*, role_id as role')->From($userTable)
                ->Join($rolesTable, "$rolesTable.id", "$userTable.role")
                ->WhereEquals('email', $email)
                ->FetchFirst();
        });

        $verifyPass = false;
        if ($userInfo instanceof \stdClass) {
            $verifyPass = helper()->verifyPassword($pass, $userInfo->user_password);
            $userInfo->user_table = Tables::CUSTOMERS;
            unset($userInfo->user_password);
        }

        if ($verifyPass === false) {
            return false;
        }

        ## Saving User settings in session auth_info might be unnecessary and won't sync, but we would see.
        ## $userInfo->{'settings'} = json_decode( $userInfo->{'settings'});
        session()->append(Session::SessionCategories_AuthInfo, $userInfo);
        session()->regenerate();

        $settings = json_decode($userInfo->settings);

        # Store the active session
        if (is_object($settings)) {
            if (!property_exists($settings, 'active_sessions')) {
                $settings->active_sessions = [session()->getCookieID(session()->sessionName())];
            } else {
                $settings->active_sessions[] = session()->getCookieID(session()->sessionName());
            }

            db(onGetDB: function ($db) use ($userInfo, $settings) {
                $db->FastUpdate($this->getCustomersTable(), ['settings' => json_encode($settings)], db()->Where('email', '=', $userInfo->email));
            });
        }
        return true;
    }

    /**
     * If customer does exist, we return the customer data, including its role_id
     * else, you get null
     * @param string $email
     * @return mixed|null
     * @throws \Exception
     */
    public function doesCustomerExist(string $email): mixed
    {
        $queryResult = null;
        $userInfo = null;
        db(onGetDB: function ($db) use ($email, &$userInfo){
            $userTable = $this->getCustomersTable();
            $rolesTable = $this->getRolesTable();
            $userInfo = $db->Select('*, role_id as role')->From($userTable)
                ->Join($rolesTable, "$rolesTable.id", "$userTable.role")
                ->WhereEquals('email', $email)
                ->FetchFirst();
        });

        if (isset($userInfo->email)){
            $queryResult = $userInfo;
        }

        return $queryResult;
    }

    /**
     * @throws \Exception
     */
    public static function getCurrentUserID()
    {
        try {
            $data = null;
            db(onGetDB: function ($db) use (&$data){
                $email = UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_UserEmail);
                $table = Tables::getTable(Tables::USERS);
                $data = $db->row("SELECT user_id, user_name FROM $table WHERE email = ?", $email);
            });
            if (is_bool($data)){
                return null;
            }
            return $data->user_id;
        } catch (\Exception) {
            // Log..
        }
        return null;
    }

    /**
     * Checks if user is authenticated (if identity has been verified; that is the userinfo and settings are in session storage),
     * returns true, otherwise false.
     *
     * Note: This could either be an admin or customer, again, this is just to verify if it exists in the session storage
     * (not to be confused with authorization)
     * @return bool
     * @throws \Exception
     */
    public static function isAuthenticated(): bool
    {
        return session()->hasValue(Session::SessionCategories_AuthInfo);
    }

    /**
     * This gets the user authenticated info property
     * @param string $key
     * e,g Session::SessionCategories_AuthInfo, Session::SessionCategories_AuthInfo_UserTable,
     * Session::SessionCategories_AuthInfo_Role, etc
     * @return mixed
     * @throws \Exception
     */
    public static function getAuthenticationInfo(string $key = Session::SessionCategories_AuthInfo): mixed
    {
        $sessionData = session()->retrieve(Session::SessionCategories_AuthInfo, jsonDecode: true);
        $result = '';
        if (empty($sessionData)) {
            return $result;
        }
        if ($key === Session::SessionCategories_AuthInfo) {
            return $sessionData;
        }
        if (isset($sessionData->{$key})){
            $result = $sessionData->{$key};
        }
        return $result;
    }

    /**
     * @param int $permission
     * e.g. Roles::CAN_ACCESS_CORE, Roles::CAN_ACCESS_CUSTOMER, Roles::CAN_ACCESS_MEDIA, Roles::CAN_READ, Roles::CAN_DELETE, ETC
     * @param null $roleAuthenticationInfo
     * - If you want to use this method multiple times, save roleAuthenticationInfo in a variable and pass it here
     * @return bool
     * @throws \Exception
     */
    public static function canAccess(int $permission, $roleAuthenticationInfo = null): bool
    {
        if ($roleAuthenticationInfo !== null) {
            return Roles::RoleHasPermission($roleAuthenticationInfo, $permission);
        }

        return Roles::RoleHasPermission(UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role), $permission);
    }

    /**
     * If user role doesn't have a permission
     * @param int $permission
     * e.g Roles::CAN_ACCESS_CORE, Roles::CAN_ACCESS_CUSTOMER, Roles::CAN_ACCESS_MEDIA, Roles::CAN_READ, Roles::CAN_DELETE, ETC
     * @return bool
     * @throws \Exception
     */
    public static function canNotAccess(int $permission): bool
    {
        return !Roles::RoleHasPermission(UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_Role), $permission);
    }

    /**
     * If user can't use the write permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessWritePermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::getPermission(Roles::CAN_WRITE))) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the update permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessUpdatePermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::getPermission(Roles::CAN_UPDATE))) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the delete permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessDeletePermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::getPermission(Roles::CAN_DELETE))) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the read permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessReadPermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::getPermission(Roles::CAN_READ))) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }


    /**
     * @throws \Exception
     */
    public function getPostAuthorHTMLSelect(int $currentSelectAuthorID = null): string
    {
        $table = $this->getUsersTable();
        $users = null;
        db(onGetDB: function ($db) use ($table, &$users){
            $users = $db->run("SELECT user_id, user_name FROM $table");
        });
        $authorFrag = '';
        $oldInputPostAuthor = \session()->getOldFormInput('post_author');
        if (count($users) > 0) {
            foreach ($users as $user) {
                if ((int)$oldInputPostAuthor === $user->user_id) {
                    $authorFrag .= "<option value='$user->user_id' selected>$user->user_name</option>";
                } elseif (!is_null($currentSelectAuthorID) && $currentSelectAuthorID === $user->user_id) {
                    $authorFrag .= "<option value='$user->user_id' selected>$user->user_name</option>";
                } else {
                    $authorFrag .= "<option value='$user->user_id'>$user->user_name</option>";
                }
            }
        }

        return $authorFrag;

    }

    public static function generateVerificationArrayDataForUser(): array
    {
        return [
            'verification_code' => null,    # the unique verification code
            'verification_code_at' => null, # time the verification code was created
            'x_verification_code' => 0,     # number of times verification code was generated or requested
            'is_verified' => 0,             # is verification code verified....1 for true, 0 for false
            'is_sent' => 0,                 # is email sent...1 for true, 0 for false
            'verified_at' => null,          # when the code was verified
            'verified_attempt' => 0,        # number of times user tried verifying
            /**
             * As you can see the expire_lock_time has in fact expired (this is default)
             * So, each time the x_verification_code is 5 and above, we add the current_time plus 5 more minutes to...
             * lock user from generating too much verification code, this is like throttling if you get me ;)
             * And whenever the time expires, user can send a new one, if they fail again, we give another 5 minutes :p
             */
            'expire_lock_time' => '1997-07-05 00:00:00',
        ];
    }

    /**
     * @throws \Exception
     */
    public static function generateAdminJSONSettings(): string
    {
        $apiTokenAndHash = helper()->generateApiTokenAndHash();
        return json_encode([
            'timezone' => "Africa/Lagos",
            'email_from' => "",     // email for sending notifications, this overrides "users email"
            'api' => [
                # The plain_token is available if you wanna auth outside this app,
                # e.g. connecting from Insomnia, Postman, etc
                'plain_token' => $apiTokenAndHash->token,
                'hash_token' => $apiTokenAndHash->hash,
                'updated_at' => helper()->date()
            ],
            'verification' => self::generateVerificationArrayDataForUser(),
            'active_sessions' => []
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function generateCustomerJSONSettings(): string
    {
        $apiTokenAndHash = helper()->generateApiTokenAndHash();
        return json_encode([
            'timezone' => "Africa/Lagos",
            'email_from' => "",     // email for sending notifications, this overrides "users email"
            'api' => [
                # The plain_token is available if you want to auth outside this app,
                # e.g. connecting from Insomnia, Postman, etc
                'plain_token' => $apiTokenAndHash->token,
                'hash_token' => $apiTokenAndHash->hash,
                'updated_at' => helper()->date()
            ],
            'verification' => self::generateVerificationArrayDataForUser(),
            'active_sessions' => []
        ]);
    }

    /**
     * each time the x_verification_code is $maxVerification (default to 5) and above, we add the current_time plus 5 more minutes to...
     * lock user from generating too much verification code, this is like throttling if you get me ;)
     * And whenever the time expires, user can send a new one, if they fail again, we give another 5 minutes :p
     * @param \stdClass $verification
     * @param int $maxVerification
     * @param callable|null $onThrottled
     * @return \stdClass
     * @throws \Exception
     */

    /**
     * If x_verification_code is greater than or equal to 5 and the current time is less than expire_lock_time,
     * we prevent the user from generating a new verification code. If the current time is greater than expire_lock_time,
     * we allow the user to generate a new verification code and update expire_lock_time to the current time plus 5 minutes.
     * @param \stdClass $verification
     * @param int $maxVerification
     * @param callable|null $onThrottled
     * @return \stdClass
     * @throws \Exception
     */
    public function handleVerificationCodeGeneration(\stdClass $verification, int $maxVerification = 5, callable $onThrottled = null): \stdClass
    {

        if (is_string($verification->expire_lock_time)){
            $verification->expire_lock_time = strtotime($verification->expire_lock_time);
        }


        if ($verification->x_verification_code >= $maxVerification && $verification->expire_lock_time > time()){
            $waitTime = $verification->expire_lock_time - time();
            session()->flash(["You are doing too much, wait $waitTime second(s) before generating a new one"], type: Session::SessionCategories_FlashMessageInfo);
            if ($onThrottled){
                $onThrottled();
            }

            return $verification;
        } else {
            // reset x_verification_code to 0 if the wait time has elapsed
            if ($verification->expire_lock_time < time()) {
                $verification->x_verification_code = 0;
            }

            $verification->expire_lock_time = time() + Scheduler::everyMinute(5);
            $verification->verification_code = random_int(000000000, 999999999);
            $verification->verification_code_at = time();
            ++$verification->x_verification_code;
        }

        return $verification;
    }

    /**
     * @param array $settings
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function verifyAndResetUserPass(array $settings = [])
    {
        $rule = $settings['resetRule'] ?? '';
        $onError = $settings['validationFails'] ?? '';
        $table = $settings['table'] ?? '';

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $rule);
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            if (is_callable($onError)){
                $onError();
            }
        }

        if (!session()->hasKey(Session::SessionCategories_PasswordReset)){
            # User that doesn't have the Session::SessionCategories_PasswordReset key should be sent back to request form
            session()->flash(['Unauthorized Access'], input()->fromPost()->all());
            if (is_callable($onError)){
                $onError();
            }
        }

        try {
            $verificationData = session()->retrieve(Session::SessionCategories_PasswordReset, jsonDecode: true);
            $verification = $verificationData->verification;

            $timeDifference = time() - $verification->verification_code_at;

            # If time is greater than an hour
            if ($timeDifference > Scheduler::everyHour(1)){
                $this->verificationInvalid($onError);
            }

            $verificationCodeFromUser = input()->fromPost()->retrieve('verification-code');
            $password = helper()->securePass(input()->fromPost()->retrieve('password'));

            # If verification code is in-valid
            if (!hash_equals((string)$verification->verification_code, $verificationCodeFromUser)){
                $this->verificationInvalid($onError);
            }

            # Remove Existing Active Sessions
            $settings = json_decode($verificationData->settings);
            $columns = array_flip(Tables::$TABLES[Tables::SESSIONS]);
            $itemsToDelete = [];
            foreach ($settings->active_sessions as $toDelete){
                $itemsToDelete[] = ['session_id' => $toDelete];
            }

            $this->deleteMultiple(Tables::getTable(Tables::SESSIONS), $columns, 'session_id', $itemsToDelete,
                onError: function () use ($onError) {
                    $this->verificationInvalid($onError);
                });

            $settings->active_sessions = [];
            # Update Password
            db(onGetDB: function ($db) use ($verificationData, $settings, $password, $table) {
                $db->FastUpdate($table, ['user_password' => $password, 'settings' => json_encode($settings), 'is_guest' => 0, 'role' => Roles::getRoleIDFromDB(Roles::ROLE_CUSTOMER)],
                    db()->Where('email', '=', $verificationData->email));
            });

            session()->flash(['Password Successfully Changed, Login'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('customer.login'));
        }catch (\Exception){
            session()->flash(['Verification code is invalid, request a new one']);
            $this->verificationInvalid($onError);
        }
    }

    /**
     * @param callable $call
     * @return void
     * @throws \Exception
     */
    private function verificationInvalid(callable $call): void
    {
        session()->flash(['Verification code is invalid, request a new one'], input()->fromPost()->all());
        if (is_callable($call)){
            $call();
        }
    }

}