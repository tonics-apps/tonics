<?php

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Library\Tables;
use App\Modules\Core\Library\View\Extensions\Traits\TonicsTemplateSystemHelper;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Exceptions\TonicsTemplateRuntimeException;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Node\Tag;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;


/**
 * The whole purpose of this mode handler is for paginating data from tables, it should never be used to replace actual sql select,
 * besides, it only supports few keywords and functions that is required to work with data pagination.
 *
 * To use the sql query, you call it with the Query Mode Handler which is an interface to the pagination function.
 *
 * Note: To create a reusable sql blocks do:
 * ```
 * [[sql_block('reusable_sql')
        [[FROM('post_categories')]]
        [[JOIN('posts.post_id', 'post_categories.fk_post_id')]]
        [[JOIN('categories.cat_id', 'post_categories.fk_cat_id')]]
    ]]
 * ```
 *
 * call it with `[[reuse_sql('reusable_sql')]]` within your `[[SQL('...')...]]` tag
 */
class SQLSelectModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    use TonicsTemplateSystemHelper;

    private string $error = '';
    private string $sqlString = ' SELECT ';

    private array $validCols = [];

    private array $params = [];

    private ?Tag $currentParent = null;

    public function validate(OnTagToken $tagToken): bool
    {
        $tagname = strtolower($tagToken->getTagName());
        if ($tagname !== 'sql' && $tagname !== 'sql_block') {
            $this->error = "SQL Statements should start with an SQL Tag, i.e [[SQl(..)...]]";
            return false;
        }

        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 10000);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $tagName = strtoupper($tagToken->getTagName());
        if ($tagName === 'SQL' && $tagToken->getTag()->hasChildren()) {
            $sql_storage_name = $tagToken->getFirstArgChild();
            $storage = $this->getTonicsView()->getModeStorage('sql');
            $this->handleSQLNodes($tagToken->getTag());
            $storage[$sql_storage_name] = [
              'sql' => $this->sqlString,
              'params' => $this->params
            ];

            $this->getTonicsView()->storeDataInModeStorage('sql', $storage);

            $this->params = [];
            $this->sqlString = " SELECT ";
        }

        if ($tagName === 'SQL_BLOCK'){
            $this->handleSQLBlock($tagToken->getTag());
        }
    }

    public function error(): string
    {
        return $this->error;
    }

    public function render(string $content, array $args, array $nodes = []): string
    {

        $current = strtolower($this->getTonicsView()->getCurrentRenderingContentMode());
        $tag = (new Tag($current))->setArgs($args)->setNodes($nodes)->setContent($content)->setParentNode($this->currentParent);

        // called from a block or a nested tag
        if ($current === 'sql_block'){
            $this->handleSQLBlock($tag);
        }

        // called from a block or a nested tag
        if ($current === 'sql'){
            $this->stickToContent(new OnTagToken($tag));
        }

        if (isset($this->mapperHandler()[$current])) {
            $this->mapperHandler()[$current]($tag);
        }
        return '';
    }

    private function handleSelect(Tag $tag): void
    {
        foreach ($tag->getArgs() as $arg) {
            if (Tables::isTable($arg)) {
                $this->validCols[$arg] = [];
                $this->validCols[$arg] = [...$this->validCols[$arg], ...Tables::$TABLES[$arg]];
                $this->validCols[$arg] = array_combine($this->validCols[$arg], $this->validCols[$arg]);
            }
        }

        $this->handleSQLNodes($tag);
    }

    private function handleFrom(Tag $tag): void
    {

        if (Tables::isTable($tag->getFirstArgChild())){
            $this->sqlString = trim($this->sqlString);
            $this->sqlString = rtrim($this->sqlString, ',') . ' ';
            $this->sqlString .= " FROM ";
        }

        foreach ($tag->getArgs() as $arg){
            if (Tables::isTable($arg)){
                $table = Tables::getTable($arg);
                $this->sqlString .= " $table, ";
            }
        }

        $this->sqlString = rtrim($this->sqlString, ', ') . ' ';
    }

    private function handleCols(Tag $tag): void
    {
        if ($tag->parentNode()->getTagName() !== 'select') {
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ['Select should be a parent of cols']);
        }
        $table_name = $tag->parentNode()->getFirstArgChild();

        if (!isset($this->validCols[$table_name])) {
            return;
        }

        $colType = $tag->getFirstArgChild();
        switch ($colType) {
            case 'ALL';
                foreach ($this->validCols[$table_name] as $col) {
                    $t = Tables::getTable($table_name);
                    $this->sqlString .= "$t.$col, ";
                }
                break;
            case 'PICK';
                $cols = isset($tag->getArgs()[1]) ? explode(',', $tag->getArgs()[1]) : [];
                if (is_array($cols)) {
                    foreach ($cols as $col) {
                        $col = trim($col);
                        if (isset($this->validCols[$table_name][$col])){
                            $t = Tables::getTable($table_name);
                            $this->sqlString .= "$t.$col, ";
                        }
                    }
                }
                break;
            case 'EXCEPT';
                $cols = isset($tag->getArgs()[1]) ? explode(',', $tag->getArgs()[1]) : [];
                foreach ($this->validCols[$table_name] as $col) {
                    $t = Tables::getTable($table_name);
                    if (in_array($col, $cols, true)) {
                        continue;
                    }
                    $this->sqlString .= "$t.$col, ";
                }
                break;
        }

    }

    private function handleColAs(Tag $tag): void
    {
        if ($tag->parentNode()->getTagName() !== 'select') {
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ['Select should be a parent of col_as']);
        }

        $table_name = $tag->parentNode()->getFirstArgChild();
        if (!isset($this->validCols[$table_name]) || !isset($tag->getArgs()[1])) {
            return;
        }

        $asCol = $tag->getArgs()[1];
        if ($this->getTonicsView()->charIsAsciiAlpha($tag->getArgs()[1]) === false){
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ["col_as second arg (`$asCol`) should be asciiAlpha"]);
        }

        $colName = $tag->getFirstArgChild();
        if (isset($this->validCols[$table_name][$colName])){
            $t = Tables::getTable($table_name);
            $this->sqlString .= "$t.$colName AS $asCol, ";
        }

    }

    private function handleInnerJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " JOIN $join";
        }
    }

    private function handleRightJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " RIGHT JOIN $join";
        }
    }

    private function handleLeftJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " LEFT JOIN $join";
        }
    }

    private function handleWhere(Tag $tag): void
    {
        $tableCol = $this->validateTableDotCol($tag->getFirstArgChild());
        if ($tableCol === false){
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ['SQL Where should be in the format [[WHERE(table.col)...]]']);
        }
        $tableCol[0] = Tables::getTable($tableCol[0]);
        $where = implode('.', $tableCol);

        $this->sqlString .= " WHERE $where ";
        $this->handleSQLNodes($tag);
    }

    private function handleOP(Tag $tag): void
    {
        $op = $tag->getFirstArgChild();
        $op = strtoupper($op);

        $validOP = [
            '+', 'DIV', '/', 'MOD', '%', '*', '-', '=', 'TRUE', 'FALSE', '!=', '<', '<=', '<=>', '>', '>=', 'IS', 'IS NOT', 'IS NOT NULL',
            'IS NULL', 'NOT BETWEEN', 'AND', 'OR'
        ];

        $validOP = array_combine($validOP, $validOP);
        if (!isset($validOP[$op])){
           $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ["OP [[('$op')]] is not supported"]);
        }

        $this->sqlString .= " $op ";
    }

    /**
     * @throws \Exception
     */
    private function handleParam(Tag $tag): void
    {
        $args = $this->resolveArgsSQL($tag->getTagName(), $tag->getArgs());
        $args = $args[$tag->getTagName()];
        $args = $this->expandArgsSQL($args, $this->params);
        $qmark = helper()->delimitArrayByComma($args);
        $this->sqlString .= " $qmark ";
    }

    private function handleOrder($tag)
    {
        if (!isset($tag->getArgs()[1])){
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ["ORDER needs an order type, e.g [[ORDER('table.col', 'ASC')]]"]);
        }

        $firstArg = $tag->getFirstArgChild();
        $secondArg = strtoupper($tag->getArgs()[1]);
        if ($firstArg = ($this->validateTableDotCol($firstArg))){
            if ($secondArg !== 'ASC'){
                $secondArg = 'DESC';
            }
            $table = Tables::getTable($firstArg[0]);
            $this->sqlString .= " ORDER BY $table.$firstArg[1] $secondArg ";
            return;
        }

        $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ["ORDER first args should contain valid table and col, e.g [[ORDER('table.col', 'ASC')]]"]);

    }

    private function handleKeyword(Tag $tag)
    {
        $keywords = ['LIMIT', 'OFFSET', 'LIKE'];
        $keywords = array_combine($keywords, $keywords);

        $key = strtoupper($tag->getFirstArgChild());

        if (isset($keywords[$key])){
            $this->sqlString .= " $key ";
        }

    }

    /**
     * @param Tag $tag
     * @return void
     * @throws \Exception
     */
    private function handleSQLFunc(Tag $tag): void
    {
        if (!isset($tag->getArgs()[1])){
            $this->getTonicsView()->exception(TonicsTemplateRuntimeException::class, ["SQLFUNC requires two arg, e.g [[SQLFUNC('function_name', 'arg1,arg2,...')]]"]);
        }

        $args = explode(',', $tag->getArgs()[1]);
        $argOkay = false; $qmark = '';
        if (is_array($args)){
            $argOkay = true;
            $args = $this->resolveArgsSQL($tag->getTagName(), $args);
            $args = $args[$tag->getTagName()];
            $args = $this->expandArgsSQL($args, $this->params);
            $qmark = helper()->delimitArrayByComma($args);
        }

        $func = strtoupper($tag->getFirstArgChild());
        switch ($func){
            case 'IN':
                if ($argOkay){
                    $this->sqlString .= " IN($qmark) ";
                }
                break;
            case 'CONCAT':
                if ($argOkay){
                    $this->sqlString .= " CONCAT($qmark) ";
                }
                break;
            case 'COUNT':
                if ($argOkay){
                    $this->sqlString .= " COUNT($qmark) ";
                }
                break;
        }
    }

    private function joinValidateAndFrag(Tag $tag): bool|string
    {
        $firstArg = $tag->getFirstArgChild();
        if (!isset($tag->getArgs()[1])){
            return false;
        }

        $frag = '';

        $secondArg = $tag->getArgs()[1];

        if ($firstArg = ($this->validateTableDotCol($firstArg))){
            $table = Tables::getTable($firstArg[0]);
            $frag .= "$table ON $table.$firstArg[1] = ";
        }

        if ($secondArg = ($this->validateTableDotCol($secondArg))){
            $table = Tables::getTable($secondArg[0]);
            $frag .= "$table.$secondArg[1]";
            return $frag;
        }

        return false;
    }

    public function validateTableDotCol(string $arg): array|bool
    {
        $arg = explode('.', $arg);
        if (!isset($arg[1])){
            return false;
        }
        $table = $arg[0]; $col = $arg[1];
        if (Tables::isTable($table)){
            if (!isset($this->validCols[$table])){
                $this->validCols[$table] = Tables::$TABLES[$table];
                $this->validCols[$table] = array_combine($this->validCols[$table], $this->validCols[$table]);
            }
            if (isset($this->validCols[$table][$col])){
                return $arg;
            }
        }
        return false;
    }

    public function handleSQLNodes(Tag $tag)
    {
        // nothing to output, if any sql function makes it through, it would be tacked to the sql storage key
        /** @var Tag $node */
        foreach ($tag->getNodes() as $node) {
            if (isset($this->mapperHandler()[strtolower($node->getTagName())])){
                $mode = $this->getTonicsView()->getModeRendererHandler($node->getTagName(), $this);
            } else {
                $mode = $this->getTonicsView()->getModeRendererHandler($node->getTagName());
            }
            if ($mode instanceof TonicsModeRendererInterface) {
                $this->currentParent = $tag;
                $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
            }
        }
    }

    private function handleReuseSQL(Tag $tag)
    {
        $storage = $this->getTonicsView()->getModeStorage('sql_block');
        $sql_block_name = $tag->getFirstArgChild();
        if (isset($storage[$sql_block_name])){
            $tag->setNodes($storage[$sql_block_name]);
        }
        $this->handleSQLNodes($tag);
    }

    public function mapperHandler()
    {
        return [
            'select' => function ($tag) {
                $this->handleSelect($tag);
            },
            'from' => function ($tag) {
                $this->handleFrom($tag);
            },
            'cols' => function ($tag) {
                $this->handleCols($tag);
            },
            'col_as' => function ($tag) {
                $this->handleColAs($tag);
            },
            'inner_join' => function ($tag) {
                $this->handleInnerJoin($tag);
            },
            'join' => function ($tag) {
                $this->handleInnerJoin($tag);
            },
            'right_join' => function ($tag) {
                $this->handleRightJoin($tag);
            },
            'left_join' => function ($tag) {
                $this->handleLeftJoin($tag);
            },
            'where' => function ($tag) {
                $this->handleWhere($tag);
            },
            'op' => function ($tag) {
                $this->handleOP($tag);
            },
            'param' => function ($tag) {
                $this->handleParam($tag);
            },
            'order' => function ($tag) {
                $this->handleOrder($tag);
            },
            'keyword' => function ($tag) {
                $this->handleKeyword($tag);
            },
            'sqlfunc' => function ($tag) {
                $this->handleSQLFunc($tag);
            },
            'reuse_sql' => function ($tag) {
                $this->handleReuseSQL($tag);
            },
        ];
    }

    public function handleSQLBlock(Tag $tag)
    {
        $storage = $this->getTonicsView()->getModeStorage('sql_block');
        $storage[$tag->getFirstArgChild()] = $tag->getNodes();
        $this->getTonicsView()->storeDataInModeStorage('sql_block', $storage);
    }

    /**
     * @param $tagName
     * @param array $tagArgs
     * @return array[]
     */
    private function resolveArgsSQL($tagName, array $tagArgs = []): array
    {
        return $this->resolveArgs($tagName, $tagArgs, ['col[' => 'column']);
    }

}