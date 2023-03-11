<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
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