<?php

namespace App\Modules\Core\Library\View\Extensions;

use App\Modules\Core\Configs\AppConfig;
use Devsrealm\TonicsHelpers\Exceptions\FileException;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class CombineModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    /**
     * @throws \Exception
     */
    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        $view->validateMaxArg($tagToken->getArg(), 'Combined', 10000, 2);
        $args = $tagToken->getArg();

        $outputFile = array_shift($args);
        $finalFile = AppConfig::getPublicPath() . DIRECTORY_SEPARATOR . trim($outputFile, '/\\');
        $finalFileHandle = @fopen($finalFile, "w");
        if ($finalFileHandle === false){
            throw new FileException("Cant Open File `$finalFile`, Permission Issue?");
        }

        foreach ($args as $fileToAppend){
            $fileToAppend = AppConfig::getPublicPath() . DIRECTORY_SEPARATOR . trim($fileToAppend, '/\\');
            if (helper()->fileExists($fileToAppend)){
                $data = file_get_contents($fileToAppend);
                fwrite($finalFileHandle, $data);
                $data = null;
                unset($data);
            }
        }

        fclose($finalFileHandle); $finalFileHandle = null;
        $tagToken->getTag()->setContent($outputFile);
        $tagToken->getTag()->setArgs([]);
        return true;
    }

    public function stickToContent(OnTagToken $tagToken)
    {
        $view = $this->getTonicsView();
        $view->getContent()->addToContent('combine', $tagToken->getContent(), $tagToken->getArg());
    }

    public function error(): string
    {
        return $this->error;
    }

    /**
     * @param string $content
     * @param array $args
     * @return string
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        return $content;
    }
}