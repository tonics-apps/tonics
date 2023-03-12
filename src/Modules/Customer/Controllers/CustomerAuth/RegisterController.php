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
use App\Modules\Core\Library\Authentication\Roles;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Validation\Traits\Validator;
use App\Modules\Customer\Jobs\CustomerRegistrationVerificationCodeEmail;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use JetBrains\PhpStorm\NoReturn;

class RegisterController extends Controller
{
    use Validator;

    private UserData $usersData;

    public function __construct(UserData $userData)
    {
        $this->usersData = $userData;
    }

    /**
     * @throws \Exception
     */
    public function showRegistrationForm()
    {
        view('Modules::Customer/Views/Auth/register');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function register()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->getRegisterRules());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('customer.register'));
        }

        $customersData = [
            'user_name' => input()->fromPost()->retrieve('username'),
            'email' => input()->fromPost()->retrieve('email'),
            'user_password' => helper()->securePass(input()->fromPost()->retrieve('password')),
            'settings'=> UserData::generateCustomerJSONSettings()
        ];

        $customerInserted = $this->getUsersData()->insertForCustomer($customersData, ['user_name', 'email', 'role']);
        if ($customerInserted === false){
            session()->flash(['Failed To Register User'], input()->fromPost()->all());
            redirect(route('customer.register'));
        } else {
            unset($customerInserted->user_password);
        }

        $customerInserted->verification = $this->handleVerificationCodeGeneration((object)UserData::generateVerificationArrayDataForUser());
        session()->append(Session::SessionCategories_NewVerification, $customerInserted);
        $this->sendNewRegistrationJob($customerInserted);
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function sendRegisterVerificationCode()
    {
        if (\session()->hasKey(Session::SessionCategories_NewVerification)){
            $data = session()->retrieve(Session::SessionCategories_NewVerification, jsonDecode: true);
            $verification = $data->verification;
            $data->verification = $this->getUsersData()->handleVerificationCodeGeneration($verification, 5,
                function () {
                    redirect(route('customer.verifyEmailForm'));
                });
            session()->append(Session::SessionCategories_NewVerification, $data);
            $this->sendNewRegistrationJob($data);
        }

        session()->flash(['Unauthorized Access']);
        redirect(route('customer.register'));
    }

    /**
     * @throws \Exception
     */
    public function showVerifyEmailForm()
    {
        view('Modules::Customer/Views/Auth/verify-email');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function verifyEmail()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->getVerificationCodeRule());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('customer.verifyEmailForm'));
        }

        if (\session()->hasKey(Session::SessionCategories_NewVerification)){
            $data = session()->retrieve(Session::SessionCategories_NewVerification, jsonDecode: true);
            if (!hash_equals(input()->fromPost()->retrieve('verification-code', helper()->randomString()), (string)$data->verification->verification_code)){
                session()->flash(['Invalid Verification Code']);
                redirect(route('customer.verifyEmailForm'));
            }

            // Once customer verifies email, if they are a guest, we remove it.
            db(onGetDB: function (TonicsQuery $db) use ($data) {
                $db->FastUpdate(
                    $this->getUsersData()->getCustomersTable(),
                    [
                        'email_verified_at' => helper()->date(),
                        'role' => Roles::getRoleIDFromDB(Roles::ROLE_CUSTOMER),
                        'is_guest' => 0
                    ],
                    db()->WhereEquals('email', $data->email));
            });

            session()->flash(['Email Successfully Verified, Please Login'], $data, Session::SessionCategories_FlashMessageSuccess);
            redirect(route('customer.login'));
        }

        session()->flash(['Unauthorized Access']);
        redirect(route('customer.register'));
    }

    /**
     * @throws \Exception
     */
    public function getRegisterRules(): array
    {
        $customerUnique = Tables::getTable(Tables::CUSTOMERS) .':email';
        return [
            'username' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'unique' => [
                $customerUnique => input()->fromPost()->retrieve('email', '')]],
            'password' => ['required', 'string', 'CharLen' => [
                'min' => 5, 'max' => 1000
            ]]
        ];
    }

    public function getVerificationCodeRule(): array
    {
        return [
            'verification-code' => ['required', 'number'],
        ];
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] private function sendNewRegistrationJob($data)
    {
        $customerVerificationCodeJob = new CustomerRegistrationVerificationCodeEmail();
        $customerVerificationCodeJob->setJobName('CustomerRegistrationVerificationCodeEmail');
        $customerVerificationCodeJob->setData($data);
        job()->enqueue($customerVerificationCodeJob);

        session()->flash(['Verification Code Sent'], type: Session::SessionCategories_FlashMessageSuccess);
        redirect(route('customer.verifyEmailForm'));
    }

    /**
     * each time the x_verification_code is 5 and above, we add the current_time plus 5 more minutes to...
     * lock user from generating too much verification code, this is like throttling if you get me ;)
     * And whenever the time expires, user can send a new one, if they fail again, we give another 5 minutes :p
     * @param \stdClass $verification
     * @return \stdClass
     * @throws \Exception
     */
    private function handleVerificationCodeGeneration(\stdClass $verification): \stdClass
    {

        if (is_string($verification->expire_lock_time)){
            $verification->expire_lock_time = strtotime($verification->expire_lock_time);
        }

        # You are generating too much verification code, wait 5 minutes to generate a new one
        if ($verification->expire_lock_time > time()){
            $waitTime = $verification->expire_lock_time - time();
            session()->flash(["Too many request, wait $waitTime second(s) to generate a new one"], type: Session::SessionCategories_FlashMessageInfo);
            redirect(route('customer.verifyEmailForm'));
        }

        if ($verification->x_verification_code >= 5){
            $verification->expire_lock_time = time() + Scheduler::everyMinute(5);
        }

        $verification->verification_code = random_int(000000000, 999999999);
        $verification->verification_code_at = time();
        $verification->x_verification_code = $verification->x_verification_code + 1;
        return $verification;
    }

    /**
     * @return UserData
     */
    public function getUsersData(): UserData
    {
        return $this->usersData;
    }
}
