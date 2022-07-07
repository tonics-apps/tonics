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

    public function handleSelect(Tag $tag): void
    {
        foreach ($tag->getArgs() as $arg) {
            if (Tables::isTable($arg)) {
                $this->validCols[$arg] = [];
                $this->validCols[$arg] = [...$this->validCols[$arg], ...Tables::$TABLES[$arg]];
                $this->fromTable[] = $arg;
            }
        }

        $this->handleSQLNodes($tag);
    }

    public function handleCols(Tag $tag): void
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
                        if (in_array($col, $this->validCols[$table_name], true)) {
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

    public function handleColAs(Tag $tag): void
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
        if (in_array($colName, $this->validCols[$table_name], true)) {
            $this->sqlString .= "$table_name.$colName AS $asCol, ";
        }
        $this->sqlString = rtrim($this->sqlString, ', ') . ' ';
    }

    public function handleInnerJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " JOIN $join";
        }
    }

    public function handleRightJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " RIGHT JOIN $join";
        }
    }

    public function handleLeftJoin(Tag $tag): void
    {
        $join = $this->joinValidateAndFrag($tag);
        if ($join !== false){
            $this->sqlString .= " LEFT JOIN $join";
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
        $firstArg = explode('.', $firstArg);
        $secondArg = explode('.', $secondArg);

        // check table cols validation
        if (!isset($this->validCols[$firstArg[0]])){
            return false;
        }

        if (!isset($this->validCols[$secondArg[0]])){
            return false;
        }

        if (isset($firstArg[0]) && Tables::isTable($firstArg[0])){
            if (isset($firstArg[1]) && in_array($firstArg[1], $this->validCols[$firstArg[0]], true)){
                $frag .= "{$firstArg[0]} ON {$firstArg[0]}.{$firstArg[1]} = ";
            }
        }

        if (isset($secondArg[0]) && Tables::isTable($secondArg[0])){
            if (isset($secondArg[1]) && in_array($secondArg[1], $this->validCols[$secondArg[0]], true)){
                $frag .= "{$secondArg[0]}.{$secondArg[1]}";
                return $frag;
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
        ];
    }
}