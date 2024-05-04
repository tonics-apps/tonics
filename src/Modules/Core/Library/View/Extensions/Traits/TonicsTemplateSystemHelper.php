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

namespace App\Modules\Core\Library\View\Extensions\Traits;

trait TonicsTemplateSystemHelper
{
    public function resolveArgs($tagName, $tagArgs = [], $moreMode = [])
    {
        $mode = [
            'v[' => 'var',
            'var[' => 'var',
            'block[' => 'block',
        ];
        if (!empty($moreMode)){
            $mode = [...$mode, ...$moreMode];
        }

        $args = [];
        foreach ($tagArgs as $k => $arg){
            $catch = false;
            foreach ($mode as $mk => $mv){
                if (str_starts_with($arg, $mk)){
                    preg_match('/\\[(.*?)]/', $arg, $matches);
                    if (isset($matches[1])){
                        $args[$k] = [
                            'mode' => $mv,
                            'value' => $matches[1]
                        ];
                        $catch = true;
                    }
                    break;
                }
            }

            if ($catch === false){
                $args[$k] = [
                    'value' => $arg
                ];
            }
        }

        return [$tagName => $args ];
    }

    public function expandArgs($args = [], callable $customMode = null)
    {
        foreach ($args as $k => $arg){
            if (isset($arg['mode'])){
                if ($arg['mode'] === 'var'){
                    if (str_contains($arg['value'], '..')){
                        $variable = explode('..', $arg['value']);
                        if (is_array($variable)){
                            foreach ($variable as $var){
                                $variable = $this->getTonicsView()->accessArrayWithSeparator($var);
                                if (!empty($variable)){
                                    $args[$k] = $variable;
                                    break;
                                }
                            }
                        }
                    } else {
                        $args[$k] = $this->getTonicsView()->accessArrayWithSeparator($arg['value']);
                    }
                }

                if ($arg['mode'] === 'block'){
                    $args[$k] = $this->getTonicsView()->renderABlock($arg['value']);
                }

                if ($customMode !== null){
                    $args[$k] = $customMode($arg['mode'], $arg['value']);
                }

            } else {
                $args[$k] = $arg['value'];
            }
        }

        return $args;
    }

    public function expandArgsSQL($args = [], array &$params = [])
    {
        foreach ($args as $k => $arg){
            if (isset($arg['mode'])){

                if ($arg['mode'] === 'block'){
                    $args[$k] = '?';
                    $params[] = $arg;
                }

                if ($arg['mode'] === 'var'){
                    $getVarValue = $this->getTonicsView()->accessArrayWithSeparator($arg['value']);
                    if (is_array($getVarValue)){
                        $key = $k;
                        foreach ($getVarValue as $value){
                            $args[$key] = '?'; ++$key;
                            $params[] = ['value' => $value];
                        }
                    } else {
                        $args[$k] = '?';
                        $params[] = $arg;
                    }
                }

                if ($arg['mode'] === 'column'){
                    if (($tableCol = $this->validateTableDotCol($arg['value']))){
                        $args[$k] = $tableCol[1];
                    } else {
                        $args[$k] = '?';
                        $params[] = $arg['value'];
                    }
                }

            } else {
                $args[$k] = '?';
                $params[] = $arg;
            }
        }

        return $args;
    }
}