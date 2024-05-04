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
                db(onGetDB: function ($db) use ($param, $uniqueInfo, &$result){
                    $result = $db->run(<<<SQL
SELECT * FROM $uniqueInfo[0] WHERE $uniqueInfo[1] = ?
SQL, $param->itemData);
                });

                if (count($result) > 0) break;

            } elseif (sizeof($uniqueInfo) === 3 ){
                db(onGetDB: function ($db) use ($v, $param, $uniqueInfo, &$result){
                    $result = $db
                        ->run(<<<SQL
SELECT * FROM $uniqueInfo[0] WHERE $uniqueInfo[1] = ? AND $uniqueInfo[2] != ?
SQL, $param->itemData, $v);
                });

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