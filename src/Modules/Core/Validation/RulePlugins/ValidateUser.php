<?php

namespace App\Modules\Core\Validation\RulePlugins;

use App\Modules\Core\Data\UserData;
use Devsrealm\TonicsValidation\Interfaces\RuleInterface;
use Devsrealm\TonicsValidation\Rule;

/**
 *
 * USAGE: `input_key' => ['Validateuser' => ['email' => 'youremail@adres.com', 'pass' => 134]]`,
 * where email is your email address, pass is your password
 *
 */
class ValidateUser extends Rule implements RuleInterface
{

    protected string $message = "Email or Password is Incorrect";

    /**
     * @throws \Exception
     */
    public function check(...$param): bool
    {
        $param = (object)$param;
        if (count($param->rule) !== 2){
            return false;
        }

        if (!key_exists('email', $param->rule) || !key_exists('pass', $param->rule)){
            return false;
        }

        $userData = new UserData();
        return $userData->validateUser($param->rule['email'], $param->rule['pass']);
    }

    /**
     * @inheritDoc
     */
    public function ruleNames(): array
    {
        return ['validate-user', 'validate_user', 'validateuser'];
    }
}