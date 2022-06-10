<?php
/*
 * Copyright (c) 2021. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * This program is licensed under the PolyForm Noncommercial License 1.0.0. You should have received a copy of the PolyForm Noncommercial License 1.0.0 along with this program, if not, visit: https://polyformproject.org/licenses/noncommercial/1.0.0/
 */

namespace App\Modules\Core\Validation\RulePlugins;

use Devsrealm\TonicsValidation\Interfaces\RuleInterface;
use Devsrealm\TonicsValidation\Rule;

/**
 * Class Unique
 * @package App\Modules\Core\Validation\RulePlugins
 *
 *
 * <br>
 * USAGE:
 *
 *  'title' => [ 'unique' => [
        'bt_tracks:track_title:track_id' => 4, // table:column:columnToIgnore
        'bt_posts:post_title:post_id'  => 5
 ]
 */
class Unique extends Rule implements RuleInterface
{

    protected string $message = ":placeholder already exist";

    /**
     * @throws \Exception
     */
    public function check(...$param): bool
    {
        $param = (object)$param;

        foreach ($param->rule as $k => $v){
            $uniqueInfo =  explode(':', $k);
            $result = null;
            if (sizeof($uniqueInfo) === 2){
                $result = db()->run(<<<SQL
SELECT * FROM $uniqueInfo[0] WHERE $uniqueInfo[1] = ?
SQL, $param->itemData);

                if (count($result) > 0) break;

            } elseif (sizeof($uniqueInfo) === 3 ){
                $result = db()
                    ->run(<<<SQL
SELECT * FROM $uniqueInfo[0] WHERE $uniqueInfo[1] = ? AND $uniqueInfo[2] != ?
SQL, $param->itemData, $v);

                if (count($result) > 0) break;
            }
        }

        $this->message = $this->replacePlaceHolders($this->message, [
            ':placeholder' => $this->reDressItemName($param->itemName)
        ]);

        return empty($result);
    }

    public function ruleNames(): array
    {
        return ['unique'];
    }
}