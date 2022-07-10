<?php

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Library\AbstractDataLayer;
use App\Modules\Core\Library\View\Extensions\Interfaces\QueryModeHandlerInterface;
use App\Modules\Core\Library\View\Extensions\Traits\TonicsTemplateSystemHelper;
use App\Themes\NinetySeven\Library\PostLoop;
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
 * This handler expects 2 arguments:
 *
 * The first arguments would be a prefix for the sql argument, e.g, if you pass it: `post_query` arg to Query, then
 * it expects the sql tags to have arg:
 *
 * - `post_query_table_count`
 * - `post_query_search_table_count`
 * - `post_query_search_row_with_offset_limit`
 * - `post_query_get_row_with_offset_limit`
 *
 * <br>
 * The second argument should be a fully-qualified event class name, and should be an instance of QueryModeHandlerInterface
 *
 * Note: The result of the query would be stored in a variable with the name `post_query`
 *
 */
class QueryModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    use TonicsTemplateSystemHelper;

    private string $error = '';

    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 2, 2);
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
        $search_table_count = $variable_name .'_search_table_count';
        $search_row_with_offset_limit = $variable_name .'_search_row_with_offset_limit';
        $get_row_with_offset_limit = $variable_name .'_get_row_with_offset_limit';
        $sqlStorage = $this->getTonicsView()->getModeStorage('sql');

        if (!isset($sqlStorage[$table_count]) || !isset($sqlStorage[$search_table_count])
            || !isset($sqlStorage[$search_row_with_offset_limit]) || !isset($sqlStorage[$get_row_with_offset_limit])){
            $this->getTonicsView()->exception(TonicsTemplateInvalidNumberOfArgument::class);
        }

        $abstractDataLayer = new AbstractDataLayer();
        $customCallable = [
            'customSearchTableCount' => function () use($sqlStorage, $search_table_count) {
            $sql = $sqlStorage[$search_table_count]['sql'];
            $params = $this->expandArgs($sqlStorage[$search_table_count]['params']);
            $result = (array)db()->row($sql, ...$params);
                return $result[array_key_first($result)];
            },
            'customTableCount' => function () use ($sqlStorage, $table_count) {
                $sql = $sqlStorage[$table_count]['sql'];
                $params = $this->expandArgs($sqlStorage[$table_count]['params']);
                $result = (array)db()->row($sql, ...$params);
                return $result[array_key_first($result)];
            },
            'customSearchRowWithOffsetLimit' => function ($table, $searchTerm, $offset, $limit) use ($sqlStorage, $search_row_with_offset_limit) {
                $variable = $this->getTonicsView()->getVariableData();
                $variable['QUERY_MODE'] = [
                    'LIMIT' => $limit,
                    'OFFSET' => $offset
                ];
                $this->getTonicsView()->setVariableData($variable);
                $sql = $sqlStorage[$search_row_with_offset_limit]['sql'];
                $params = $this->expandArgs($sqlStorage[$search_row_with_offset_limit]['params']);
                return db()->run($sql, ...$params);
            },
            'customGetRowWithOffsetLimit' => function ($table, $offset, $limit) use ($get_row_with_offset_limit, $sqlStorage) {
                $variable = $this->getTonicsView()->getVariableData();
                $variable['QUERY_MODE'] = [
                    'LIMIT' => $limit,
                    'OFFSET' => $offset
                ];
                $this->getTonicsView()->setVariableData($variable);
                $sql = $sqlStorage[$get_row_with_offset_limit]['sql'];
                $params = $this->expandArgs($sqlStorage[$get_row_with_offset_limit]['params']);
                return db()->run($sql, ...$params);
            },
        ];

        try {
            $data = $abstractDataLayer->generatePaginationData('', '', '', 20, $customCallable);
            $this->getTonicsView()->addToVariableData($variable_name, $data);
            if (isset($args[1])){
                $handler = new $args[1];
                if ($handler instanceof QueryModeHandlerInterface){
                    return $handler->handleQueryData($this->getTonicsView(), $data);
                }
            }
        }catch (\Exception $exception){
            dd($exception);
            // log...
        }
        return '';
    }
}