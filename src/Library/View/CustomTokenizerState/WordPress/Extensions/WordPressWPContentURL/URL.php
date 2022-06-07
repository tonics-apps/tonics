<?php

namespace App\Library\View\CustomTokenizerState\WordPress\Extensions\WordPressWPContentURL;

use App\Configs\AppConfig;
use App\Configs\DriveConfig;
use App\Modules\Media\FileManager\LocalDriver;
use Devsrealm\TonicsTemplateSystem\AbstractClasses\TonicsTemplateViewAbstract;
use Devsrealm\TonicsTemplateSystem\Interfaces\TonicsModeRendererInterface;

class URL  extends TonicsTemplateViewAbstract implements TonicsModeRendererInterface
{

    /**
     * @throws \Exception
     */
    public function render(string $content, array $args, array $nodes = []): string
    {
        $localDriver = new LocalDriver();
        $path = DriveConfig::getWordPressImportUploadsPath() . '/' . $args['path'];
        $path = str_replace("/wp-content/uploads", '', $path);
        $fileObject = $localDriver->convertFilePathToFileObject($path);
        if ($fileObject !== false){
            $url = parse_url(AppConfig::getAppUrl(), PHP_URL_HOST);
            return $content . rtrim($url, '/').$fileObject->urlDownload;
        }

        return $content . $args['url'].$args['path'];
    }
}