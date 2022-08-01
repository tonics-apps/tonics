<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Controllers\Auth;

use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\RequestInterceptor\RedirectAuthenticated;
use App\Modules\Core\Validation\Traits\Validator;
use JetBrains\PhpStorm\ArrayShape;

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

    public function logout()
    {
        session()->regenerate();
        dd(input()->fromPost()->all());
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
                ['email' => input()->fromPost()->retrieve('email'), 'pass' => input()->fromPost()->retrieve('password'), 'type' => UserData::UserAdmin_INT]
            ]
        ];
    }
}
