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
use App\Modules\Core\Events\TonicsTemplateViewEvent\BeforeCombineModeOperation;
use Devsrealm\TonicsHelpers\Exceptions\FileException;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeInterface;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;
use Devsrealm\TonicsTemplateSystem\Tokenizer\Token\Events\OnTagToken;

class CombineModeHandler extends TonicsTemplateViewAbstract implements TonicsModeInterface, TonicsModeRendererInterface
{

    private string $error = '';

    /**
     * @param OnTagToken $tagToken
     * @return bool
     * @throws \Exception
     */
    public function validate(OnTagToken $tagToken): bool
    {
        $view = $this->getTonicsView();
        $view->validateMaxArg($tagToken->getArg(), $tagToken->getTagName(), 10000, 2);
        $args = $tagToken->getArg();

        $outputFile = array_shift($args);
        $rootPath = AppConfig::getPublicPath();
        if (str_starts_with($outputFile, 'APP::')){
            $rootPath = AppConfig::getAppsPath();
        } elseif (str_starts_with($outputFile, 'MODULE::')){
            $rootPath = AppConfig::getModulesPath();
        }

        $outputFile = str_replace(['MODULE::', 'APP::'], '', $outputFile);
        $finalFile = $rootPath . DIRECTORY_SEPARATOR . trim($outputFile, '/\\');

        /** @var BeforeCombineModeOperation $beforeCombineOperationEvent */
        $beforeCombineOperationEvent = event()->dispatch(new BeforeCombineModeOperation($outputFile, $rootPath));
        if ($beforeCombineOperationEvent->combineFiles() === false) {
            $tagToken->getTag()->setContent($beforeCombineOperationEvent->getOutputFile());
            $tagToken->getTag()->setArgs([]);
            return true;
        }

        $finalFileHandle = @fopen($finalFile, "w");
        if ($finalFileHandle === false) {
            throw new FileException("Cant Open File `$finalFile`, Permission Issue?");
        }

        foreach ($args as $file) {

            $fileRootPath = AppConfig::getPublicPath();
            if (str_starts_with($file, 'APP::')){
                $fileRootPath = AppConfig::getAppsPath();
            } elseif (str_starts_with($file, 'MODULE::')){
                $fileRootPath = AppConfig::getModulesPath();
            }

            $file = str_replace(['MODULE::', 'APP::'], '', $file);

            // Priority would be given to the combine type, if it doesn't exist, we check the PublicPath
            $fileToAppend = $fileRootPath . DIRECTORY_SEPARATOR . trim($file, '/\\');
            if (helper()->fileExists($fileToAppend)) {
                $data = file_get_contents($fileToAppend);
                fwrite($finalFileHandle, $data);
                $data = null;
                unset($data);
            }
        }

        fclose($finalFileHandle);
        $finalFileHandle = null;
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
     * @param array $nodes
     * @return string
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        return '';
    }
}