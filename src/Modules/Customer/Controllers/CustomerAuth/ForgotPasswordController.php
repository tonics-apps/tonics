<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Customer\Controllers\CustomerAuth;

use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Jobs\ForgotPasswordEmail;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use JetBrains\PhpStorm\NoReturn;

class ForgotPasswordController extends Controller
{
    use Validator;

    /**
     * Show the form to request a password reset link.
     * @throws \Exception
     */
    public function showLinkRequestForm()
    {
        view('Modules::Customer/Views/Auth/reset-password');
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
            redirect(route('customer.password.request'));
        }
        $email = input()->fromPost()->retrieve('email');

        try {
            $table = Tables::getTable(Tables::CUSTOMERS);
            $forgotPasswordData = db()->Select(table()->pickTable($table, ['user_name', 'email', 'settings']))->From($table)
                ->WhereEquals('email', $email)->FetchFirst();


            if (isset($forgotPasswordData->email) && hash_equals($forgotPasswordData->email, $email)){
                if (session()->hasKey(Session::SessionCategories_PasswordReset)){
                    $verification = session()->retrieve(Session::SessionCategories_PasswordReset, jsonDecode: true);
                    $verification = $verification->verification;
                } else {
                    $verification = (object)UserData::generateVerificationArrayDataForUser();
                }
                $userData = new UserData();
                $forgotPasswordData->verification = $userData->handleVerificationCodeGeneration($verification, 5,
                    function () {
                    redirect(route('customer.password.request'));
                });

                session()->append(Session::SessionCategories_PasswordReset, $forgotPasswordData);
                $forgotPasswordEmail = new ForgotPasswordEmail();
                $forgotPasswordEmail->setJobName('ForgotPasswordEmail');
                $forgotPasswordEmail->setData($forgotPasswordData);
                job()->enqueue($forgotPasswordEmail);

                redirect(route('customer.password.verifyEmail'));
            }
        } catch (\Exception $exception){
            // log..
        }

        session()->flash(['Invalid Details'], input()->fromPost()->all());
        redirect(route('customer.password.request'));
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
            redirect(route('customer.password.request'));
        }

        view('Modules::Customer/Views/Auth/verify-code-reset-pass');
    }

    /**
     * Throttle This for Customer...
     * @throws \Exception
     */
    public function reset()
    {
        $userData = new UserData();
        $userData->verifyAndResetUserPass([
            'resetRule' => $this->getResetRule(),
            'validationFails' => function(){
                redirect(route('customer.password.request'));
            },
            'table' => $userData->getCustomersTable()
        ]);
    }


    /**
     * @throws \Exception
     */
    #[NoReturn] public function verificationInvalid()
    {
        session()->flash(['Verification code is invalid, request a new one']);
        redirect(route('customer.password.request'));
    }

    public function getSendResetLinkEmailRule(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
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
