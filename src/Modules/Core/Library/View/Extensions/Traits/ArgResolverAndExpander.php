<?php

namespace App\Modules\Core\Library\View\Extensions\Traits;

trait ArgResolverAndExpander
{
    public function resolveArgs($tagName, $tagArgs = [])
    {
        $mode = [
            'v[' => 'var',
            'var[' => 'var',
            'block[' => 'block',
        ];
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

    public function expandArgs($args = [])
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
            } else {
                $args[$k] = $arg['value'];
            }
        }

        return $args;
    }
}