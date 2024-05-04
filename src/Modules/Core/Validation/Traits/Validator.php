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

namespace  App\Modules\Core\Validation\Traits;

use App\Modules\Core\Validation\RulePlugins\Unique;
use App\Modules\Core\Validation\RulePlugins\ValidateCustomer;
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
            new ValidateUser(),
            new ValidateCustomer(),
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