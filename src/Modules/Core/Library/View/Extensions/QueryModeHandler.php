<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use App\Modules\Core\Library\View\Extensions\Traits\TonicsTemplateSystemHelper;
use App\Modules\Post\Helper\PostLoop;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateInvalidNumberOfArgument;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;


/**
 * The Query Mode Handler works with the SQLSelectModeHandler, you write the sql with the SQLSelect Handler and you
 * call it with the Query Handler which in turns connects it to the pagination API.
 *
 * <br>
 * This handler can have a min of 2 and a max of 3 arguments:
 *
 * The first arguments would be a prefix for the sql argument, e.g, if you pass it: `post_query` arg to Query, then
 * it expects the sql tags to have arg:
 *
 * - `post_query_table_count`
 * - `post_query_get_row_with_offset_limit`
 *
 * <br>
 * The second argument should be a fully-qualified event class name, and should be an instance of QueryModeHandlerInterface
 *
 * Note: The result of the query would be stored in a variable with the name `post_query`
 *
 * <br>
 * The third argument is optional, it is a callback that takes the block_name you want to use has a callback...
 *
 * Note: You also get the query result in Query_Mode.Result variable if you want some further customization...
 *
 */
class QueryModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    use TonicsTemplateSystemHelper;

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 3, 2);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent($tagToken->getTagName(), '', $tagToken->getArg());
    }

    public function error(): string
    {
        return $this->error;
    }

    /**
     * @param string $content
     * @param array $args
     * @param array $nodes
     * @return string
     * @throws \Exception
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $variable_name = $args[0];
        $table_count = $variable_name .'_table_count';
        $get_row_with_offset_limit = $variable_name .'_get_row_with_offset_limit';
        $sqlStorage = $this->getTonicsView()->getModeStorage('sql');
        if (!isset($sqlStorage[$table_count]) || !isset($sqlStorage[$get_row_with_offset_limit])){
            $this->getTonicsView()->exception(TonicsTemplateInvalidNumberOfArgument::class);
        }

        try {
            $sql = $sqlStorage[$table_count]['sql'];
            $params = $this->expandArgs($sqlStorage[$table_count]['params']);
            $result = (array)db()->row($sql, ...$params);
            $tableRows = $result[array_key_first($result)];
            $data = db()->paginate(
                tableRows: $tableRows,
                callback: function ($perPage, $offset) use ($get_row_with_offset_limit, $sqlStorage) {
                    $variable = $this->getTonicsView()->getVariableData();
                    $this->getTonicsView()->setDontCacheVariable(true);
                    $variable['QUERY_MODE'] = [
                        'LIMIT' => $perPage,
                        'OFFSET' => $offset
                    ];
                    $this->getTonicsView()->setVariableData($variable);
                    $sql = $sqlStorage[$get_row_with_offset_limit]['sql'];
                    $params = $this->expandArgs($sqlStorage[$get_row_with_offset_limit]['params']);
                    return db()->run($sql, ...$params);
                }, perPage: AppConfig::getAppPaginationMax());

            $variable = $this->getTonicsView()->getVariableData();
            $variable['QUERY_MODE']['Result'] = $data;
            $this->getTonicsView()->setVariableData($variable);

            $callback = null;
            if (isset($args[2])){
                $callback = function () use ($args) {
                    return $this->getTonicsView()->renderABlock($args[2]);
                };
            }
            if (isset($args[1])){
                $handler = new $args[1];
                if ($handler instanceof QueryModeHandlerInterface && is_object($data)){
                    return $handler->handleQueryData($this->getTonicsView(), $variable_name, $data, $callback);
                }
            }
        }catch (\Exception $exception){
            // log...
        }
        return '';
    }
}