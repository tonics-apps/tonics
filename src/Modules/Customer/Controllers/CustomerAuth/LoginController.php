<?php
/*
 *     Copyright (c) 2022-2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Customer\Controllers\CustomerAuth;

use App\Modules\Core\Controllers\Controller;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\Tables;
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
    public function showLoginForm(): void
    {
        view('Modules::Customer/Views/Auth/login');
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
            redirect(route('customer.login'));
        }

        (new RedirectAuthenticated())->handle(request());
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    #[NoReturn] public function logout()
    {
        UserData::DeleteActiveSessions( Tables::getTable(Tables::CUSTOMERS), UserData::getAuthenticationInfo(Session::SessionCategories_AuthInfo_UserEmail));
        session()->logout();
        redirect(route('customer.login'));
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
                'ValidateCustomer' =>
                    ['email' => input()->fromPost()->retrieve('email'), 'pass' => input()->fromPost()->retrieve('password')]
            ]
        ];
    }

}
