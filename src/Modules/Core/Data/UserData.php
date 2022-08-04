<?php

namespace App\Modules\Core\Data;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use JetBrains\PhpStorm\ArrayShape;

class UserData extends AbstractDataLayer
{

    const UserAdmin_INT = 1;
    const UserCustomer_INT = 2;

    const UserAdmin_STRING = 'admin';
    const UserCustomer_STRING = 'customer';

    static array $USER_TABLES = [
        self::UserAdmin_INT => self::UserAdmin_STRING,
        self::UserCustomer_INT => self::UserCustomer_STRING
    ];

    /**
     * @throws \Exception
     */
    #[ArrayShape([self::UserAdmin_STRING => "string", self::UserCustomer_STRING => "string"])] public static function USER_REDIRECTION_ADMIN_PAGE(): array
    {
        return [
            self::UserAdmin_STRING => route('admin.dashboard'),
            self::UserCustomer_STRING => route('customer.dashboard')
        ];
    }

    /**
     * Since the user table can poly-morph sort of, we need to insert data into $userData first,
     * we then add the correlation to $subTypeData. When we insert the userData, we return the user_id which we would use to
     * finally add data to the subTypeData.
     *
     * Note: There is nothing to return as result, it is either void or an exception when an error occurs.
     * @param array $userData
     * @param array $subTypeData
     * @param int $userType
     * @throws \Exception
     */
    public function insertForUser(array $userData, array $subTypeData, int $userType = UserData::UserAdmin_INT)
    {
        if (!key_exists($userType, self::$USER_TABLES)){
            throw new \Exception("Invalid UserType");
        }
        $userData['user_type'] = $userType;

        $userTable = Tables::getTable(Tables::USERS);
        $subTypeTable = Tables::getTable(self::$USER_TABLES[$userType]);

        $userDataReturning = db()->insertReturning($userTable, $userData, ['user_id']);
        $subTypeData['user_id'] = $userDataReturning->user_id;
        db()->insertBatch($subTypeTable, $subTypeData);
    }


    /**
     * Usage:
     * <br>
     * `$newUserData->selectWithConditionFromUser(['email', 'user_name'], "email = ?", ['pascal@gmail.com']), $userType);`
     *
     * Note: Make sure you use a question-mark(?) in place u want a user input and pass the actual input in the $parameter
     * @param array $colToSelect
     * @param string $whereCondition
     * @param array $parameter
     * @param int $userType
     * @param bool $returnRow
     * If true, return a single row regardless if the result is of multiple rows,
     * if false, return an array of row
     * @return mixed
     * @throws \Exception
     */
    public function selectWithConditionFromUser(
        array $colToSelect,
        string $whereCondition,
        array $parameter,
        int $userType = UserData::UserAdmin_INT, bool $returnRow = true): mixed
    {
        if (!key_exists($userType, self::$USER_TABLES)){
            throw new \Exception("Invalid UserType");
        }
        $select = helper()->returnDelimitedColumnsInBackTick($colToSelect);

        $userTable = Tables::getTable(Tables::USERS);
        $subTypeTable = Tables::getTable(self::$USER_TABLES[$userType]);

        if ($returnRow){
            return db()->row(<<<SQL
SELECT $select FROM $userTable JOIN $subTypeTable USING(`user_id`) WHERE $whereCondition
SQL, ...$parameter);
        }

       return db()->run(<<<SQL
SELECT $select FROM $userTable JOIN $subTypeTable USING(`user_id`) WHERE $whereCondition
SQL, ...$parameter);
    }

    /**
     * Usage: `$newUserData->deleteWithCondition([], "role = ?", ['16318']);`
     * @param string $whereCondition
     * @param array $parameter
     * @param int $userType
     * @throws \Exception
     */
    public function deleteWithConditionFromUser(string $whereCondition, array $parameter, int $userType = UserData::UserAdmin_INT): void
    {
        if (!key_exists($userType, self::$USER_TABLES)){
            throw new \Exception("Invalid UserType");
        }
        $userTable = Tables::getTable(Tables::USERS);
        $subTypeTable = Tables::getTable(self::$USER_TABLES[$userType]);
        db()->run(<<<SQL
DELETE $userTable FROM $userTable JOIN $subTypeTable USING(`user_id`) WHERE $whereCondition
SQL, ...$parameter);
    }


    /**
     * Validates User Data, if validation is true, store the userInfo in auth session, regenerates the session_id, and return the data,
     * you get false if the user data is invalid
     * @param string $email
     * @param string $pass
     * @param int $userType
     * @return bool
     * @throws \Exception
     */
    public function validateUser(string $email, string $pass, int $userType = UserData::UserAdmin_INT): bool
    {
        $userInfo = $this->selectWithConditionFromUser(['email', 'user_name', 'user_type', 'user_password', 'role'], "email = ?", [$email], $userType);
        $verifyPass = false;
        if ($userInfo instanceof \stdClass){
            $verifyPass = helper()->verifyPassword($pass, $userInfo->user_password);
            unset($userInfo->user_password);
        }

        if ($verifyPass === false){
            return false;
        }

        ## Saving User settings in session auth_info might be unnecessary and won't sync, but we would see.
        ## $userInfo->{'settings'} = json_decode( $userInfo->{'settings'});
        session()->append(Session::SessionCategories_AuthInfo, $userInfo);
        session()->regenerate();
        return true;
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
     * e,g Session::SessionCategories_AuthInfo, Session::SessionCategories_AuthInfo_UserType,
     * Session::SessionCategories_AuthInfo_Role, etc
     * @return mixed
     * @throws \Exception
     */
    public static function getAuthenticationInfo( string $key = Session::SessionCategories_AuthInfo): mixed
    {
        $sessionData = session()->retrieve(Session::SessionCategories_AuthInfo, jsonDecode: true);

        $result = '';
        if (empty($sessionData)){
            return $result;
        }

        if ($key === Session::SessionCategories_AuthInfo){
            return json_decode($sessionData);
        }

        if (is_string($sessionData)){
            $sessionData = json_decode($sessionData);
            if (property_exists($sessionData, $key)){
                $result = $sessionData->{$key};
            }
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
        if ($roleAuthenticationInfo !== null){
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
        if (UserData::canNotAccess(Roles::CAN_WRITE)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the update permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessUpdatePermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::CAN_UPDATE)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the delete permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessDeletePermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::CAN_DELETE)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }

    /**
     * If user can't use the read permission, we return and un-auth error
     * @throws \Exception
     */
    public static function canNotAccessReadPermissionBoiler()
    {
        if (UserData::canNotAccess(Roles::CAN_READ)) {
            SimpleState::displayErrorMessage(SimpleState::ERROR_UNAUTHORIZED_ACCESS__CODE, SimpleState::ERROR_UNAUTHORIZED_ACCESS__MESSAGE);
        }
    }


    /**
     * @throws \Exception
     */
    public function getPostAuthorHTMLSelect(int $currentSelectAuthorID = null): string
    {
        $adminUsers = $this->selectWithConditionFromUser(['user_id', 'user_name'], "user_type = ?", [UserData::UserAdmin_INT], returnRow: false);
        $authorFrag = '';
        $oldInputPostAuthor = \session()->getOldFormInput('post_author');
        if (count($adminUsers) > 0){
            foreach ($adminUsers as $user){
                if ((int)$oldInputPostAuthor === $user->user_id){
                    $authorFrag .= "<option value='$user->user_id' selected>$user->user_name</option>";
                } elseif(!is_null($currentSelectAuthorID) && $currentSelectAuthorID === $user->user_id){
                    $authorFrag .= "<option value='$user->user_id' selected>$user->user_name</option>";
                } else {
                    $authorFrag .= "<option value='$user->user_id'>$user->user_name</option>";
                }
            }
        }

        return $authorFrag;

    }


    /**
     * @throws \Exception
     */
    public static function generateAdminJSONSettings(): string
    {
        $apiTokenAndHash = helper()->generateApiTokenAndHash();
        return json_encode([
            'mailer' => 1, // 1 stands for smtp and 0 stands for localhost
            'timezone' => "Africa/Lagos",
            'email_from' => "",     // email for sending notifications, this overrides "users email"
            'api' => [
                # The plain_token is available if you wanna auth outside this app,
                # e.g. connecting from Insomnia, Postman, etc
                'plain_token' => $apiTokenAndHash->token,
                'hash_token' => $apiTokenAndHash->hash,
                'updated_at' => helper()->date()
            ],
            'verification' => [
                'verification_code' => null,    # the unique verification code
                'verification_code_at' => null, # time the verification code was created
                'x_verification_code' => 0,     # number of times verification code was generated or requested
                'is_verified' => 0,             # is verification code verified....1 for true, 0 for false
                'is_sent' => 0,                 # is email sent...1 for true, 0 for false
                'verified_at' => null,          # when the code was verified
                'verified_attempt' => 0,        # number of times user tried verifying
                /**
                 * As you can see the expire_lock_time has in fact expired (this is default)
                 * So, each time the verification_code_at is 5 and above, we add the current_time plus 10 more minutes to...
                 * lock user from generating too many verification code, this is like throttling if you get me ;)
                 * And whenever the time expires, user can send a new one, if they fail again, we give another 10 minutes :p
                 */
                'expire_lock_time' => '1997-07-05 00:00:00',
            ],
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
            'old_page_slug' => [],
            'page_slug' => 'p', // default standalone page slug
            'old_track_slug' => [],
            'track_slug' => 'beat', // default track_slug
            'mailer' => 1, // 1 stands for smtp and 0 stands for localhost
            'timezone' => "Africa/Lagos",
            'email_from' => "",     // email for sending notifications, this overrides "users email"
            'api' => [
                # The plain_token is available if you wanna auth outside this app,
                # e.g. connecting from Insomnia, Postman, etc
                'plain_token' => $apiTokenAndHash->token,
                'hash_token' => $apiTokenAndHash->hash,
                'updated_at' => helper()->date()
            ],
            'verification' => [
                'verification_code' => null,    # the unique verification code
                'verification_code_at' => null, # time the verification code was created
                'x_verification_code' => 0,     # number of times verification code was generated or requested
                'is_verified' => 0,             # is verification code verified....1 for true, 0 for false
                'is_sent' => 0,                 # is email sent...1 for true, 0 for false
                'verified_at' => null,          # when the code was verified
                'verified_attempt' => 0,        # number of times user tried verifying
                /**
                 * As you can see the expire_lock_time has in fact expired (this is default)
                 * So, each time the verification_code_at is 5 and above, we add the current_time plus 10 more minutes to...
                 * lock user from generating too many verification code, this is like throttling if you get me ;)
                 * And whenever the time expires, user can send a new one, if they fail again, we give another 10 minutes :p
                 */
                'expire_lock_time' => '1997-07-05 00:00:00',
                # When a user is guest, this is the hash that would be used to identify the user when the user is
                # trying to transfer the guest account to an actual user account.
                'guest_hash' => null,
            ],
        ]);
    }

    public function generateSessionJsonSettings(): string
    {
        return json_encode(
            [
                'tonics_csrf_token' => '',
                'auth_info' => [
                    'email' => '',
                    'role' => '',
                    'user_name' => '',
                    
                ]
            ]
        );
    }

}