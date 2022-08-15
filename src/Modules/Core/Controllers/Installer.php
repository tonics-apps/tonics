<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers;

use Ahc\Env\Loader;
use App\Modules\Core\Commands\Environmental\SetEnvironmentalKey;
use App\Modules\Core\Commands\Module\MigrateAll;
use App\Modules\Core\Commands\UpdateLocalDriveFilesInDb;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DatabaseConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Validation\Traits\Validator;
use DateTimeZone;
use Devsrealm\TonicsValidation\Validation;
use Exception;
use PDOException;

class Installer extends SimpleState
{
    use Validator;

    ## states for the install() method
    const InstallIsAppInstalledInitialStateHandler = 'InstallIsAppInstalledInitialStateHandler';
    const InstallValidateInstallationProperties = 'InstallValidateInstallationProperties';

    const InstallSetInstallationPropertiesInEnvFile = 'InstallSetInstallationPropertiesInEnvFile';
    const InstallSetEnvironmentalKey = 'InstallSetEnvironmentalKey';
    const InstallMigrateDatabases = 'InstallMigrateDatabases';
    const InstallGenerateAdminUser = 'InstallGenerateAdminUser';

    static array $INSTALL_STATES = [
        self::InstallIsAppInstalledInitialStateHandler => self::InstallIsAppInstalledInitialStateHandler,
        self::InstallValidateInstallationProperties => self::InstallValidateInstallationProperties,
        self::InstallSetInstallationPropertiesInEnvFile => self::InstallSetInstallationPropertiesInEnvFile,
        self::InstallSetEnvironmentalKey => self::InstallSetEnvironmentalKey,
        self::InstallMigrateDatabases => self::InstallMigrateDatabases,
        self::InstallGenerateAdminUser => self::InstallGenerateAdminUser,
    ];

    /***
     * @return void
     * @throws Exception
     */
    public function showInstallerForm()
    {

        $timeZoneList = DateTimeZone::listIdentifiers();
        $timeZoneListOutput = null;
        foreach ($timeZoneList as $tz) {
            $timeZoneListOutput .= "<option value=" . $tz . ">" . $tz . "</option>";
        }
        view('Modules::Core/Views/installer', ['timeZoneList' => $timeZoneListOutput]);
    }

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    public function validateInstallationProperties($requestBody): Validation
    {
        return $this->getValidator()->make($requestBody, $this->getInstallerRules());
    }

    /**
     * @throws \Exception
     */
    public function preInstall()
    {
        $requestBody = request()->getEntityBody();
        $requestBody = json_decode($requestBody);

        try {
            if ($this->validateInstallationProperties($requestBody)->fails()){
                SimpleState::displayErrorMessage(400, "An Issue Occurred in Pre-Install Validation", true);
            }

            # At this point, we save the requestEntityBody to JSON to be further processed by the install() method of the installer API
            $result = @file_put_contents(AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'install.json', request()->getEntityBody());
            if ($result === false){
                SimpleState::displayErrorMessage(400, "An Issue Occurred Creating The Temp Validation Installation JSON File", true);
            }
            response()->onSuccess([], message: 'success');

        } catch (Exception){
            # This means there is a name in the $requestBody that shouldn't be there or malformed data was supplied to make() method
            # (someone is trying something funny). Those are the only thing that can raise exceptions
            SimpleState::displayErrorMessage(400, "An Issue Occurred in Pre-Install Validation", true);
        }

    }

    /***
     * @return void
     * @throws Exception
     */
    public function install()
    {
        helper()->addEventStreamHeader();
        $runningInstallerFile = AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'runningInstaller.json';
        if (helper()->fileExists($runningInstallerFile)){
            $this->issue(self::InstallIsAppInstalledInitialStateHandler, "Relax, An Existing Installation is In Progress...", closeStream: false);
            exit(1);
        } else {
            $result = file_put_contents($runningInstallerFile, '{}');
            if ($result === false){
                $this->issue(self::InstallIsAppInstalledInitialStateHandler, "An Issue Occurred Creating runningInstaller.json");
            }
        }

        if (apcu_enabled()){ apcu_clear_cache(); }

        $initState = self::InstallIsAppInstalledInitialStateHandler;

        ## EDGE-CASE: When the client loses connection, it tries to reconnect while sending the HTTP_LAST_EVENT_ID header
        ## if by then the app is not already installed, we can use this to continue the installation, thanks to the state management.
        $lastEventID = request()->getHeaderByKey('HTTP_LAST_EVENT_ID');
        if ($lastEventID && key_exists($lastEventID, self::$INSTALL_STATES)){
            $initState = $lastEventID;
        }

        ## Start States
        $this->setCurrentState($initState);
        $this->runStates(false);
    }

    /**
     * @throws Exception
     */
    public function InstallIsAppInstalledInitialStateHandler(): string
    {
        helper()->sendMsg(self::getCurrentState(), "> Let's Get Started With The Installation, Good Luck...");
        helper()->sendMsg(self::getCurrentState(), "> Step 1 of 6: App Installation Check ✔");
        $this->switchState(self::InstallValidateInstallationProperties);
        return self::NEXT;
    }

    /**
     * @throws Exception
     */
    public function InstallValidateInstallationProperties(): string
    {
        $installationProperties = $this->getInstallationProperties();
        $requestBody = json_decode($installationProperties);
        try {
            if ($this->validateInstallationProperties($requestBody)->fails()){
                $this->issue(self::getCurrentState(), 'An Issue Occurred in Pre-Install Validation');
                return self::ERROR;
            }

            if (!hash_equals($requestBody->INSTALL_KEY, AppConfig::getAppInstallKey())){
                $this->issue(self::getCurrentState(), 'Invalid Installation Key');
                return self::ERROR;
            }

            helper()->sendMsg(self::getCurrentState(), "> Step 2 of 6: Validated Temp Installation File ✔");
            $this->switchState(self::InstallSetInstallationPropertiesInEnvFile);
            return self::NEXT;
        } catch (Exception){
            $this->issue(self::getCurrentState(), 'An Issue Occurred in Pre-Install Validation: Raised Exception');
            return self::ERROR;
        }
    }

    /**
     * @throws Exception
     */
    public function InstallSetInstallationPropertiesInEnvFile(): string
    {
        $installationProperties = $this->getInstallationProperties();
        $installationPropertiesToArray = json_decode($installationProperties, true);

        $installationPropertiesToArray['APP_URL'] = rtrim($installationPropertiesToArray['APP_URL'], '/');

        ## Remove user, email and pass from env
        unset($installationPropertiesToArray["username"], $installationPropertiesToArray["email"], $installationPropertiesToArray["password"]);

        if (helper()->updateMultipleEnvValue(AppConfig::getEnvFilePath(), $installationPropertiesToArray) === false){
            $this->issue(self::getCurrentState(), 'An Issue Occurred Updating Env File');
            return self::ERROR;
        }

        ## Reload ENV FIle
        (new Loader)->load(AppConfig::getEnvFilePath(), true);

        ## Test DB Connection
        try {
            $dsn = 'mysql:host=' . DatabaseConfig::getHost() .
                ';dbname=' . DatabaseConfig::getDatabase() .
                ';charset=' . DatabaseConfig::getCharset();
            new \PDO($dsn, DatabaseConfig::getUsername(), DatabaseConfig::getPassword());
            helper()->sendMsg(self::getCurrentState(), "> Step 3 of 6: Set Installation Properties in Env File ✔");
            helper()->sendMsg(self::getCurrentState(), "> Step 3 of 6: Can Ping The Database Server ✔");
            $this->switchState(self::InstallSetEnvironmentalKey);
            return self::NEXT;
        } catch (PDOException $e) {
            // helper()->sendMsg(self::InstallSetInstallationPropertiesInEnvFile, $e->getMessage(), 'issue');
            $this->issue(self::getCurrentState(), 'Failed To Connect To The DB Server, Wrong DB Configs?');
            return self::ERROR;
        }

    }

    /**
     * @return string
     * @throws Exception
     */
    public function InstallSetEnvironmentalKey(): string
    {
        $setEnvKey = new SetEnvironmentalKey();
        try {
            $setEnvKey->run([]);
            if ($setEnvKey->passes()){
                helper()->sendMsg(self::getCurrentState(), "> Step 4 of 6: Set Environmental Key ✔");
                $this->switchState(self::InstallMigrateDatabases);
                return self::NEXT;
            }
            $msg = $setEnvKey->getErrorMessage();
            $this->issue(self::getCurrentState(), $msg);
            return self::ERROR;
        } catch (Exception){
            $this->issue(self::getCurrentState(), 'An Error Occurred Setting Environmental Key: Raised Exception');
            return self::ERROR;
        }
    }

    /**
     * @throws Exception
     */
    public function InstallMigrateDatabases(): string
    {
        helper()->sendMsg(self::getCurrentState(), "> Step 5 of 6: Preparing Database Migration");
        try {
            $migrateAll = new MigrateAll();
            $migrateAll->run([]);
            if ($migrateAll->passes()){
                helper()->sendMsg(self::getCurrentState(), "> Step 5 of 6: Database Tables Migrated ✔");
                $this->switchState(self::InstallGenerateAdminUser);
                return self::NEXT;
            }
            $this->issue(self::getCurrentState(), 'An Error Occurred Migrating Some Database Tables');
            return self::ERROR;
        } catch (Exception|\ReflectionException $e){
            helper()->sendMsg(time(), json_encode($e->getMessage()), 'issue');
            $this->issue(self::getCurrentState(), 'An Error Occurred Migrating Some Database Tables: Raised Exception');
            return self::ERROR;
        }
    }

    /**
     * @throws Exception
     */
    public function InstallGenerateAdminUser()
    {
        ## Reload ENV FIle
        (new Loader)->load(AppConfig::getEnvFilePath(), true);

        helper()->sendMsg(self::getCurrentState(), "> Step 6 of 6: Generating Admin User, I Promise This is The Last Step :P");
        try {
            $installationProperties = $this->getInstallationProperties();
            $requestBody = json_decode($installationProperties, true);

            $userData = [
                'user_name' => $requestBody['username'],
                'email' => $requestBody['email'],
                'user_password' => helper()->securePass($requestBody['password']),
                'role' => Roles::ADMIN(),
                'settings'=> UserData::generateAdminJSONSettings()
            ];
            $newUserData = new UserData();
            $userInserted = $newUserData->insertForUser($userData);
            if ($userInserted === false){
                $this->issue(self::getCurrentState(), 'Failed To Finalize Installation');
            } else {
                helper()->sendMsg(self::getCurrentState(), "> Step 6 of 6: I Lied, We Still Gotta Update LocalDrive in Database ;(");
                $updateLocalDriveFilesINnDB = new UpdateLocalDriveFilesInDb();
                $updateLocalDriveFilesINnDB->run([]);
            }
        }catch (Exception){
            $this->issue(self::getCurrentState(), 'Failed To Finalize Installation');
        }

        $this->deleteArtifacts();
        helper()->sendMsg(self::getCurrentState(), "> Step 6 of 6: Installation Completed, Refreshing... ✔");
        $adminPage = ['page' => route('admin.login')];
        helper()->sendMsg(self::getCurrentState(), json_encode($adminPage), 'redirect');
    }

    public function getInstallerRules(): array
    {
        return [
            'username' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'CharLen' => [
                'min' => 5, 'max' => 1000
            ]],
            'DB_HOST' => ['required', 'string'],
            'DB_PORT' => ['required', 'numeric'],
            'DB_DATABASE' => ['required', 'string'],
            'DB_USERNAME' => ['required', 'string'],
            'DB_PASSWORD' => ['required', 'string'],
            'DB_PREFIX' => ['required', 'string', 'CharLen' => [
                'min' => 1, 'max' => 7
            ]],

            'INSTALL_KEY' => ['required', 'string'],
            'APP_URL' => ['required', 'string'],
            'APP_LANGUAGE' => ['required', 'numeric'],
            'APP_TIME_ZONE' => ['required', 'string'],
        ];
    }

    /**
     * @throws Exception
     */
    public function getInstallationProperties(): string
    {
        $properties = file_get_contents(AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'install.json');
        if ($properties === false){
            $this->issue(self::getCurrentState(), 'Failed To Read Temp Installation File');
            $this->emitError(false);
        }
        return $properties;
    }

    /**
     * @throws Exception
     */
    public function issue($id, $msg, $event = 'issue', $closeStream = true)
    {
        $runningInstallerFile = AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'runningInstaller.json';
        helper()->sendMsg($id, "> $msg", $event, 900000000000000000);
        if ($closeStream){
            helper()->sendMsg($id, "> Close", 'close');
            helper()->deleteFile($runningInstallerFile);
        }
    }

    /**
     * @throws Exception
     */
    public function deleteArtifacts()
    {
        $runningInstallerFile = AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'runningInstaller.json';
        helper()->deleteFile(AppConfig::getAppRoot() . DIRECTORY_SEPARATOR . 'install.json');
        helper()->deleteFile($runningInstallerFile);
    }
}