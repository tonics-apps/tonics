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
use App\Modules\Core\Jobs\ForgotPasswordEmail;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use JetBrains\PhpStorm\NoReturn;

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
            $forgotPasswordData = null;

            db(onGetDB: function ($db) use ($table, $email, &$forgotPasswordData){
                $forgotPasswordData = $db->Select(table()->pickTable($table, ['user_name', 'email', 'settings']))->From($table)
                    ->WhereEquals('email', $email)->FetchFirst();
            });

            if (hash_equals(AppConfig::getKey(), $app_key) && isset($forgotPasswordData->email) && hash_equals($forgotPasswordData->email, $email)){
                if (session()->hasKey(Session::SessionCategories_PasswordReset)){
                    $verification = session()->retrieve(Session::SessionCategories_PasswordReset, jsonDecode: true);
                    $verification = $verification->verification;
                } else {
                    $verification = (object)UserData::generateVerificationArrayDataForUser();
                }
                $verification->verification_code = random_int(000000000, 999999999);
                $verification->verification_code_at = time();
                $verification->x_verification_code = $verification->x_verification_code + 1;

                $forgotPasswordData->verification = $verification;
                session()->append(Session::SessionCategories_PasswordReset, $forgotPasswordData);

                $forgotPasswordEmail = new ForgotPasswordEmail();
                $forgotPasswordEmail->setJobName('ForgotPasswordEmail');
                $forgotPasswordEmail->setData($forgotPasswordData);
                job()->enqueue($forgotPasswordEmail);

                redirect(route('admin.password.verifyEmail'));
            }
        }catch (\Exception $exception){
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
        $userData->verifyAndResetUserPass([
            'resetRule' => $this->getResetRule(),
            'validationFails' => function(){
                redirect(route('admin.password.request'));
            },
            'table' => $userData->getUsersTable()
        ]);
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
            'verification-code' => ['required', 'number'],
            'password' => ['required', 'string', 'CharLen' => [
                'min' => 5, 'max' => 1000
            ]],
        ];
    }
}
