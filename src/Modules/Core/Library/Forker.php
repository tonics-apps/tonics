<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library;

class Forker
{
    private array $pIDS = [];

    public function fork($times = 1){

        for ($i = 1; $i <= $times; ++$i){
            $this->pIDS[$i] = pcntl_fork();

            if ($this->pIDS[$i] < 0 || $this->pIDS[$i] == null){
                // unable to fork...
            }

            if ($this->pIDS[$i] === 0){

                exit();
            }
        }
    }
}