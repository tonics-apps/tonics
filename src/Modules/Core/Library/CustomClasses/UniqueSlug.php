<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\CustomClasses;

trait UniqueSlug
{
    /**
     * @param $tableName
     * @param $columnToCheckAgainst
     * @param $slugName
     * @return string|null
     * @throws \Exception
     */
    public function generateUniqueSlug($tableName, $columnToCheckAgainst, $slugName): ?string
    {
        $slugFromInput = $slugName; // Slug gotten from Input
        $slug = $slugFromInput; // save it in slug variable, in,case, the while loop is false, we then use it as is or keep looping until it is false...
        $count = 1;
        while ($this->slugExist($tableName, $columnToCheckAgainst, $slug)) { // called the slugExist function from this one to pass the necessary parameters
            $slug = $slugFromInput . '-' . ++$count;
        }

        return $slug;
    }

    /**
     *  This generates a unique slug
     *
     *  USAGE: Pass in the $tableName, $columnToCheckAgainst(this is the column you are checking the duplication on), and the slugName, and the
     *         function would return either true or false depending on whether the slug already exist or not
     * @param $tableName
     * @param $columnToCheckAgainst
     * @param $slugName
     * @return bool
     *
     * @throws \Exception
     * @version 1.0.0
     * @author Devsrealm <faruq@devsrealm.com>
     */
    public function slugExist($tableName, $columnToCheckAgainst, $slugName): bool
    {
        $slug = null;
        db(onGetDB: function ($db) use ($slugName, $columnToCheckAgainst, $tableName, &$slug){
            $slug = $db->row("SELECT * FROM $tableName WHERE $columnToCheckAgainst = ?", $slugName);
        });

        if (!empty($slug)) {
            return true;
        }

        return false;
    }

    /**
     * @param $string
     * @param string $separator
     * @return string|array|null
     */
    public function formatURL($string, string $separator = '-'): string|array|null
    {
        $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
        $special_cases = array('&' => 'and', "'" => '');
        $string = mb_strtolower(trim($string), 'UTF-8');
        $string = str_replace(array_keys($special_cases), array_values($special_cases), $string);
        $string = preg_replace($accents_regex, '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        // I added (\/) to ignore slash since I'll need it.
        $string = preg_replace("/[^\/a-z0-9]/u", $separator, $string);
        return preg_replace("/[$separator]+/u", $separator, $string);
    }

}
