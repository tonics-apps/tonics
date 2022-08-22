<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Controllers\Auth;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Jobs\UserAdminForgotPasswordEmail;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Field\Data\FieldData;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Console\Helper\Table;

class ForgotPasswordController extends Controller
{
    use Validator;

    /**
     * @throws \Exception
     */
    public function showLinkRequestForm()
    {
       view('Modules::Core/Views/Auth/reset-password');
    }

    /**
     * STEP 1: REQUEST FOR A RESET EMAIL LINK
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function sendResetLinkEmail()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->getSendResetLinkEmailRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('admin.password.request'));
        }
        $email = input()->fromPost()->retrieve('email');
        $app_key = input()->fromPost()->retrieve('app_key');

        try {
            $table = Tables::getTable(Tables::USERS);
            $forgotPasswordData = db()->row("SELECT user_name, email, settings FROM $table WHERE email = ?", $email);

            if (hash_equals(AppConfig::getKey(), $app_key) && isset($forgotPasswordData->email) && hash_equals($forgotPasswordData->email, $email)){
                if (session()->hasKey(Session::SessionCategories_PasswordReset)){
                    $verification = session()->retrieve(Session::SessionCategories_PasswordReset, jsonDecode: true);
                    $verification = json_decode($verification);
                    $verification = $verification->verification;
                } else {
                    $verification = (object)UserData::generateVerificationArrayDataForUser();
                }
                $verification->verification_code = random_int(000000000, 999999999);
                $verification->verification_code_at = time();
                $verification->x_verification_code = $verification->x_verification_code + 1;

                $forgotPasswordData->verification = $verification;
                session()->append(Session::SessionCategories_PasswordReset, $forgotPasswordData);

                $userAdminForgotPasswordJob = new UserAdminForgotPasswordEmail();
                $userAdminForgotPasswordJob->setJobGroupName('UserAdminForgotPasswordEmail');
                $userAdminForgotPasswordJob->setData($forgotPasswordData);
                job()->enqueue($userAdminForgotPasswordJob);

                redirect(route('admin.password.verifyEmail'));
            }
        }catch (\Exception){
            // log..
        }

        session()->flash(['Invalid Details'], input()->fromPost()->all());
        redirect(route('admin.password.request'));
    }

    /**
     * STEP 2: VERIFY CODE SENT TO EMAIL
     * @throws \Exception
     */
    public function showVerifyCodeForm()
    {
        if (!session()->hasKey(Session::SessionCategories_PasswordReset)){
            # User that doesn't have the Session::SessionCategories_PasswordReset key should be sent back to request form
            session()->flash(['Unauthorized Access'], input()->fromPost()->all());
            redirect(route('admin.password.request'));
        }

        view('Modules::Core/Views/Auth/verify-code-reset-pass');
    }

    /**
     * We won't throttle an admin, throttling would be for customer users
     * @throws \Exception
     */
    public function reset()
    {
        $userData = new UserData();

        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->getResetRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('admin.password.request'));
        }

        if (!session()->hasKey(Session::SessionCategories_PasswordReset)){
            # User that doesn't have the Session::SessionCategories_PasswordReset key should be sent back to request form
            session()->flash(['Unauthorized Access'], input()->fromPost()->all());
            redirect(route('admin.password.request'));
        }

        try {
            $verificationData = session()->retrieve(Session::SessionCategories_PasswordReset, jsonDecode: true);
            $verificationData = json_decode($verificationData);
            $verification = $verificationData->verification;

            $timeDifference = time() - $verification->verification_code_at;

            # If time is greater than an hour
            if ($timeDifference > Scheduler::everyHour(1)){
                $this->verificationInvalid();
            }

            $verificationCodeFromUser = input()->fromPost()->retrieve('verification-code');
            $password = helper()->securePass(input()->fromPost()->retrieve('password'));

            # If verification code is in-valid
            if (!hash_equals((string)$verification->verification_code, $verificationCodeFromUser)){
                $this->verificationInvalid();
            }

            # Remove Existing Active Sessions
            $settings = json_decode($verificationData->settings);
            $columns = array_flip(Tables::$TABLES[Tables::SESSIONS]);
            $itemsToDelete = [];
            foreach ($settings->active_sessions as $toDelete){
                $itemsToDelete[] = ['session_id' => $toDelete];
            }
            $userData->deleteMultiple(Tables::getTable(Tables::SESSIONS), $columns, 'session_id', $itemsToDelete,
                onSuccess: function () use ($settings, $verificationData, $password, $userData) {
                $settings->active_sessions = [];
                # Update Password
                db()->update($userData->getUsersTable(), ['user_password' => $password, 'settings' => json_encode($settings)], ['email' => $verificationData->email]);
            }, onError: function (){
                $this->verificationInvalid();
                });

            session()->flash(['Password Successfully Changed, Login'], type: Session::SessionCategories_FlashMessageSuccess);
            redirect(route('admin.login'));
        }catch (\Exception){
            $this->verificationInvalid();
        }
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function verificationInvalid()
    {
        session()->flash(['Verification code is invalid, request a new one']);
        redirect(route('admin.password.request'));
    }

    public function getSendResetLinkEmailRule(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'app_key' => ['required', 'string'],
        ];
    }

    public function getResetRule(): array
    {
        return [
            'verification-code' => ['required', 'string'],
            'password' => ['required', 'string', 'CharLen' => [
                'min' => 5, 'max' => 1000
            ]],
        ];
    }
}
