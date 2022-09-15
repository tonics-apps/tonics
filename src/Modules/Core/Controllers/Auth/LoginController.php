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

use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\Validation\Traits\Validator;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;

class LoginController extends Controller
{

    use Validator;

    /***
     * @return void
     * @throws \Exception
     */
    public function showLoginForm()
    {
        view('Modules::Core/Views/Auth/login');
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function login()
    {
        $validator = $this->getValidator()->make(input()->fromPost()->all(), $this->getLoginRules());
        if ($validator->fails()){
            session()->flash($validator->getErrors(), input()->fromPost()->all());
            redirect(route('admin.login'));
        }

        (new RedirectAuthenticated())->handle(request());
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function logout()
    {
        session()->logout();
        redirect(route('admin.login'));
    }

    /**
     * @throws \Exception
     */
    #[ArrayShape(['email' => "string[]", 'password' => "array"])] public function getLoginRules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => [
                'required',
                'string',
                'CharLen' => ['min' => 5, 'max' => 1000],
                'ValidateUser' =>
                ['email' => input()->fromPost()->retrieve('email'), 'pass' => input()->fromPost()->retrieve('password')]
            ]
        ];
    }
}
