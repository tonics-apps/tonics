<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace  App\Modules\Core\Validation\Traits;

use App\Modules\Core\Validation\RulePlugins\Unique;
use App\Modules\Core\Validation\RulePlugins\ValidateUser;
use Devsrealm\TonicsValidation\Interfaces\RuleInterface;
use Devsrealm\TonicsValidation\Rules\Boolean;
use Devsrealm\TonicsValidation\Rules\CharacterLength;
use Devsrealm\TonicsValidation\Rules\Decimal;
use Devsrealm\TonicsValidation\Rules\Email;
use Devsrealm\TonicsValidation\Rules\File;
use Devsrealm\TonicsValidation\Rules\InArray;
use Devsrealm\TonicsValidation\Rules\Integer;
use Devsrealm\TonicsValidation\Rules\IsArray;
use Devsrealm\TonicsValidation\Rules\Json;
use Devsrealm\TonicsValidation\Rules\Numeric;
use Devsrealm\TonicsValidation\Rules\Required;
use Devsrealm\TonicsValidation\Rules\Same;
use Devsrealm\TonicsValidation\Rules\Text;
use Devsrealm\TonicsValidation\Rules\Url;
use Devsrealm\TonicsValidation\Validation;
use Devsrealm\TonicsValidation\ValidatorRulesRegistrar;

trait Validator
{

    protected ?ValidatorRulesRegistrar $validatorRulesRegistrar = null;

    public function addValidatorPlugins(array $plugins): static
    {
        $this->resolveValidatorRulesRegistrar();
        $this->validatorRulesRegistrar->setList($plugins);

        return $this;
    }

    public function resolveValidatorRulesRegistrar(): void
    {
        $validatorRulesRegistrar = new ValidatorRulesRegistrar([
            new Boolean(),
            new CharacterLength(),
            new Decimal(),
            new Email(),
            new File(),
            new InArray(),
            new Integer(),
            new IsArray(),
            new Json(),
            new Numeric(),
            new Required(),
            new Same(),
            new Text(),
            new Url(),
            new Unique(),
            new ValidateUser()
        ]);
        $this->validatorRulesRegistrar = $validatorRulesRegistrar;
    }

    /**
     * @return ValidatorRulesRegistrar
     */
    public function getValidatorRulesRegistrar(): ValidatorRulesRegistrar
    {
        if ($this->validatorRulesRegistrar instanceof ValidatorRulesRegistrar) {
            return $this->validatorRulesRegistrar;
        }
        $this->resolveValidatorRulesRegistrar();
        return $this->validatorRulesRegistrar;
    }


    /**
     * @throws \Exception
     */
    public function getValidator(): Validation
    {
        return new Validation($this->getValidatorRulesRegistrar());
    }

    /**
     * @throws \Exception
     */
    public function getValidatorRuleNames(): array
    {
        $listArray = [];
        $validatorLists = $this->getValidator()->getValidatorRulesRegistrar()->getList();
        foreach ($validatorLists as $list){
            if ($list instanceof RuleInterface && isset($list->ruleNames()[0])){
                $listArray[] = $list->ruleNames()[0];
            }
        }

        return $listArray;
    }

}