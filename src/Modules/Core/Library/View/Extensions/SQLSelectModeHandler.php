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


class SQLSelectModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{
    use TonicsTemplateSystemHelper;

    private string $error = '';
    private string $sqlString = '';
    private string $fromString = ' FROM ';

    private array $validCols = [];
    private array $fromTable = [];

    private array $params = [];

    private ?Tag $currentParent = null;

    public function validate(OnTagToken $tagToken): bool
    {
        if (strtolower($tagToken->getTagName()) !== 'sql') {
            $this->error = "SQL Statements should start with an SQL Tag, i.e [[SQl(..)...]]";
            return false;
        }

        $view = $this->getTonicsView();
        return $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 10000);
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        if ($tagToken->getTag()->hasChildren()) {
            $this->handleSQLNodes($tagToken->getTag());
        }

        dd($this);
    }

    public function error(): string
    {
        return $this->error;
    }

    public function render(string $content, array $args, array $nodes = []): string
    {
        $current = $this->getTonicsView()->getCurrentRenderingContentMode();
        $tag = (new Tag($current))->setArgs($args)->setNodes($nodes)->setContent($content)->setParentNode($this->currentParent);
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
                $this->fromTable[] = $arg;
            }
        }

        $this->handleSQLNodes($tag);
    }

    private function handleFrom(Tag $tag): void
    {
        if (Tables::isTable($tag->getFirstArgChild())){
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
                    $this->sqlString .= "$table_name.$col, ";
                }
                break;
            case 'PICK';
                $cols = isset($tag->getArgs()[1]) ? explode(',', $tag->getArgs()[1]) : [];
                if (is_array($cols)) {
                    foreach ($cols as $col) {
                        $col = trim($col);
                        if (isset($this->validCols[$table_name][$col])){
                            $this->sqlString .= "$table_name.$col, ";
                        }
                    }
                }
                break;
            case 'EXCEPT';
                $cols = isset($tag->getArgs()[1]) ? explode(',', $tag->getArgs()[1]) : [];
                foreach ($this->validCols[$table_name] as $col) {
                    if (in_array($col, $cols, true)) {
                        continue;
                    }
                    $this->sqlString .= "$table_name.$col, ";
                }
                break;
        }
        $this->sqlString = rtrim($this->sqlString, ', ') . ' ';
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
            $this->sqlString .= "$table_name.$colName AS $asCol, ";
        }
        $this->sqlString = rtrim($this->sqlString, ', ') . ' ';
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
        $whereCol = $tag->getFirstArgChild();
        foreach ($this->validCols as $validCol){
            if (isset($validCol[$whereCol])){
                $this->sqlString .= " WHERE $whereCol ";
                $this->handleSQLNodes($tag);
                break;
            }
        }
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
        $args = $this->resolveArgs($tag->getTagName(), $tag->getArgs());
        $args = $args[$tag->getTagName()];
        $args = $this->expandArgs($args);
        $qmark = helper()->returnRequiredQuestionMarks($args);

        $this->sqlString .= " $qmark ";
        $this->params = [...$this->params, ...$args];
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
            if (isset($this->validCols[$table][$col])){
                return $arg;
            }
        }
        return false;
    }

    public function handleSQLNodes(Tag $tag)
    {
        // nothing to output, if any sql function makes it through, it would be tacked to the sql storage key
        foreach ($tag->getNodes() as $node) {
            $mode = $this->getTonicsView()->getModeRendererHandler($node->getTagName(), $this);
            if ($mode instanceof TonicsModeRendererInterface) {
                $this->currentParent = $tag;
                $this->getTonicsView()->setCurrentRenderingContentMode($node->getTagName());
                $mode->render($node->getContent(), $node->getArgs(), $node->getNodes());
            }
        }
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
        ];
    }
}