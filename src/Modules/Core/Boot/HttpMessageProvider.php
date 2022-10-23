<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\Boot;

use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Media\FileManager\LocalDriver;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsContainer\Interfaces\ServiceProvider;
use Devsrealm\TonicsRouterSystem\Handler\Router;

/**
 * Class HttpMessageProvider
 * @package App
 */
class HttpMessageProvider implements ServiceProvider
{

    private Router $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router){
        $this->router = $router;
    }

    /**
     * @param Container $container
     * @throws \Exception
     */
    public function provide(Container $container): void
    {
        $xmlObject = simplexml_load_file("/home/devsrealmguy/webProjects/cringe/private/uploads/exclusivemusicplus wordpress backup/exclusivemusicplus.wordpress.2022-04-26.xml", null, LIBXML_NOCDATA);
        $posts = [];
        $attachment = [];
        foreach ($xmlObject->channel->item as $post){
            if ($post->xpath('wp:post_type')[0]->__toString() === 'attachment'){
                $namespaces = $post->getNameSpaces(true);
                $attachment[$post->children($namespaces['wp'])->post_id->__toString()] =
                    $post->children($namespaces['wp'])->attachment_url->__toString();
            }
        }

        $localDriver = new LocalDriver();
        foreach ($xmlObject->channel->item as $post){
            if ($post->xpath('wp:post_type')[0]->__toString() === 'post') {
                $namespaces = $post->getNameSpaces(true);
                $name = (!empty($post->title->__toString())) ? $post->title->__toString() : "";
                $imageID = '';
                foreach ($post->xpath('wp:postmeta') as $meta) {
                    if ($meta->xpath('wp:meta_key')[0]->__toString() === '_thumbnail_id') {
                        $imageID = $meta->xpath('wp:meta_value')[0]->__toString();
                    }
                }
                $imageURL = (key_exists($imageID, $attachment)) ? $attachment[$imageID] : '';
                $postSlug = (empty($post->children($namespaces['wp'])->post_name->__toString())) ? helper()->slug($name): $post->children($namespaces['wp'])->post_name->__toString();
                if (!empty($postSlug) && !empty($imageURL)){
                    $path = DriveConfig::getWordPressImportUploadsPath().parse_url($imageURL, PHP_URL_PATH);
                    $path = str_replace("/wp-content/uploads", '', $path);
                    $fileObject = $localDriver->convertFilePathToFileObject($path);
/*                    if ($fileObject !== false){
                        $imageURL = (isset($fileObject->urlPreview)) ? $fileObject->urlPreview : $imageURL;
                        db()->Update(Tables::getTable(Tables::POSTS))
                            ->Set('image_url', $imageURL)
                            ->WhereEquals('post_slug', $postSlug)
                            ->FetchFirst();
                    }*/

                   // $posts[$postSlug] = $imageURL;
                }
            }
        }


        dd($posts);
        try {
            $this->getRouter()->dispatchRequestURL();
        } catch (\Exception | \Throwable $e) {
             $redirect_to = $this->tryURLRedirection();
            $reURL = url()->getRequestURL();
             if ($redirect_to === false){
                 if (AppConfig::canLog404()){
                     try {
                         db()->Insert(
                             Tables::getTable(Tables::BROKEN_LINKS),
                             [
                                 'from' => $reURL,
                                 'to'   => null,
                             ]
                         );
                     }catch (\Exception $exception){
                         // Log..
                     }
                 }

             } else {
                 if (isset($redirect_to->to) && !empty($redirect_to->to)){
                     redirect($redirect_to->to, $redirect_to->redirection_type);
                 } else {
                     if (!empty($reURL)){
                         $hit = $redirect_to->hit ?? 1;
                         try {
                             db()->FastUpdate(
                                 Tables::getTable(Tables::BROKEN_LINKS),
                                 [
                                     '`from`' => $reURL,
                                     '`to`'   => null,
                                     '`hit`'   => ++$hit,
                                 ],
                                 db()->WhereEquals('`from`', $reURL)
                             );
                         } catch (\Exception $exception){
                             // Log..
                         }
                     }
                 }
             }

            if (AppConfig::isProduction()){
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage());
            } else {
                SimpleState::displayErrorMessage($e->getCode(),  $e->getMessage() . $e->getTraceAsString());
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function tryURLRedirection():object|bool
    {
        try {
            $table = Tables::getTable(Tables::BROKEN_LINKS);
            $result = db()->Select('*')->From($table)->WhereEquals(table()->pickTable($table, ['from']), url()->getRequestURL())->FetchFirst();
            if (is_object($result)){
                return $result;
            }
        } catch (\Exception $exception){
            // Log..
        }

       return false;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}