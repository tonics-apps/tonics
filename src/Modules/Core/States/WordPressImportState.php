<?php
/*
 * Copyright (c) 2022. Ahmed Olayemi Faruq <faruq@devsrealm.com>
 *
 * While this program can be used free of charge,
 * you shouldn't and can't freely copy, modify, merge,
 * publish, distribute, sublicense,
 * and/or sell copies of this program without written permission to me.
 */

namespace App\Modules\Core\States;

use App\Modules\Core\Configs\DriveConfig;
use App\Modules\Core\Data\UserData;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\Tables;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressShortCode;
use App\Modules\Core\Library\View\CustomTokenizerState\WordPress\WordPressWPContentURL;
use App\Modules\Media\FileManager\LocalDriver;
use App\Modules\Post\Controllers\PostCategoryController;
use App\Modules\Post\Controllers\PostsController;
use App\Modules\Post\Data\PostData;
use App\Modules\Post\Events\OnPostCategoryCreate;
use App\Modules\Post\Events\OnPostCreate;
use Devsrealm\TonicsTemplateSystem\Loader\TonicsTemplateArrayLoader;
use SimpleXMLElement;

class WordPressImportState extends SimpleState
{
    private LocalDriver $localDriver;
    private string $uploadZip;
    private string $uploadXML;
    private string $xmlFullFilePath = '';
    private string $siteURL;
    private false|SimpleXMLElement $xmlObject = false;
    private array $categoryData = [];
    private array $urlRedirections = [];

    private array $infoOfUniqueID = [];

    # States For ExtractFileState
    const InitialState = 'InitialState';
    const PhaseOne = 'PhaseOne';
    const PhaseOne_ExtractFromLocalPath = 'PhaseOne_ExtractFromLocalPath';
    const PhaseOne_DownloadAndExtractFromURI = 'PhaseOne_DownloadAndExtractFromURI';
    const PhaseTwo = 'PhaseTwo';
    const PhaseTwo_DownloadXMLFromURI = 'PhaseTwo_DownloadXMLFromURI';
    const PhaseThree = 'PhaseThree';
    const PhaseThree_ImportCategories = 'PhaseThree_ImportCategories';
    const PhaseThree_ImportPosts = 'PhaseThree_ImportPosts';
    const PhaseDone = 'PhaseDone';

    public static array $STATES = [
        self::InitialState => self::InitialState,
        self::PhaseOne => self::PhaseOne,
        self::PhaseOne_ExtractFromLocalPath => self::PhaseOne_ExtractFromLocalPath,
        self::PhaseOne_DownloadAndExtractFromURI => self::PhaseOne_DownloadAndExtractFromURI,
        self::PhaseTwo => self::PhaseTwo,
        self::PhaseTwo_DownloadXMLFromURI => self::PhaseTwo_DownloadXMLFromURI,
        self::PhaseThree => self::PhaseThree,
        self::PhaseThree_ImportCategories => self::PhaseThree_ImportCategories,
        self::PhaseThree_ImportPosts => self::PhaseThree_ImportPosts,
        self::PhaseDone => self::PhaseDone,
    ];

    public function __construct(string $uploadZip, string $uploadXML, string $siteURL)
    {
        $this->localDriver = new LocalDriver();
        $this->uploadZip = $uploadZip;
        $this->uploadXML = $uploadXML;
        $this->siteURL = $siteURL;
    }

    public function InitialState(): string
    {
        ## Require at-least a GB
        ini_set('memory_limit','1024M');

        if (empty($this->uploadZip)){
           return $this->switchState(self::PhaseTwo, self::NEXT);
        }

        return $this->switchState(self::PhaseOne, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function PhaseOne(): string
    {
        if (helper()->fileExists(DriveConfig::getWordPressImportPath()) === false){
            helper()->createDirectoryRecursive(DriveConfig::getWordPressImportPath());
            $table =  $this->getLocalDriver()->getDriveTable();
            $rootID = db()->row("Select `drive_id` FROM $table WHERE `drive_parent_id` IS NULL");
            if (!isset($rootID->drive_id)){
                helper()->deleteFileOrDirectory(DriveConfig::getWordPressImportPath());
                helper()->sendMsg(self::getCurrentState(), "Failed To Create WordPress Import Directory", 'issue');
                return self::ERROR;
            }
            try {
                $this->getLocalDriver()
                    ->insertFileToDBReturning(
                        DriveConfig::getWordPressImportPath(),
                        $rootID->drive_id,
                        'directory', ['drive_id']);
            }catch (\Exception){
                helper()->deleteFileOrDirectory(DriveConfig::getWordPressImportPath());
                helper()->sendMsg(self::getCurrentState(), "Failed To Create WordPress Import Directory", 'issue');
                return self::ERROR;
            }
        }

        helper()->sendMsg(self::getCurrentState(), 'Checking If Uploads Zip File Needs To Be Downloaded...');
        if (!str_contains($this->uploadZip, DriveConfig::serveFilePath())){
            return $this->switchState(self::PhaseOne_DownloadAndExtractFromURI, self::NEXT);
        }

        $uploadZippedURLPath = str_replace(DriveConfig::serveFilePath(), '', parse_url($this->uploadZip, PHP_URL_PATH));
        $fileInfo = $this->localDriver->getInfoOfUniqueID($uploadZippedURLPath);
        if ($fileInfo === null){
            return $this->switchState(self::PhaseOne_DownloadAndExtractFromURI, self::NEXT);
        }

        $this->infoOfUniqueID = $fileInfo;
        return $this->switchState(self::PhaseOne_ExtractFromLocalPath, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function PhaseOne_ExtractFromLocalPath(): string
    {
        helper()->sendMsg(self::getCurrentState(), 'Extracting Zipped File');
        $res = $this->localDriver->extractFile($this->infoOfUniqueID['fullFilePath'], DriveConfig::getWordPressImportPath());
        if ($res){
            helper()->sendMsg(self::getCurrentState(), 'Extraction Completed');
            return $this->switchState(self::PhaseTwo, self::NEXT);
        }
        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function PhaseOne_DownloadAndExtractFromURI(): string
    {
        helper()->sendMsg(self::getCurrentState(), 'Getting Set To Download File');
        $randUploadString = helper()->randomString(15)."uploads.zip";
        $res = $this->localDriver->createFromURL($this->uploadZip, DriveConfig::getUploadsPath(), $randUploadString);
        if ($res){
            helper()->sendMsg(self::getCurrentState(), "Download Completed With Name: `$randUploadString`");
            helper()->sendMsg(self::getCurrentState(), 'Extracting Zipped File');
            $resExtract = $this->localDriver->extractFile(DriveConfig::getUploadsPath() . DIRECTORY_SEPARATOR . $randUploadString, DriveConfig::getWordPressImportPath());
            helper()->garbageCollect();
            if ($resExtract){
                helper()->sendMsg(self::getCurrentState(), 'Extraction Completed');
                return $this->switchState(self::PhaseTwo, self::NEXT);
            }
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function PhaseTwo(): string
    {
        helper()->sendMsg(self::getCurrentState(), 'Checking If XML File Needs To Be Downloaded...');
        if (!str_contains($this->uploadXML, DriveConfig::serveFilePath())){
            return $this->switchState(self::PhaseTwo_DownloadXMLFromURI, self::NEXT);
        }

        $uploadZippedURLPath = str_replace(DriveConfig::serveFilePath(), '', parse_url($this->uploadXML, PHP_URL_PATH));
        $fileInfo = $this->localDriver->getInfoOfUniqueID($uploadZippedURLPath);
        if ($fileInfo === null){
            return $this->switchState(self::PhaseTwo_DownloadXMLFromURI, self::NEXT);
        }

        $this->xmlFullFilePath = $fileInfo['fullFilePath'];
        helper()->sendMsg(self::getCurrentState(), "XML File Exists...Moving On...");
        return $this->switchState(self::PhaseThree, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function PhaseTwo_DownloadXMLFromURI(): string
    {
        helper()->sendMsg(self::getCurrentState(), 'Getting Set To XML File');
        $randUploadString = helper()->randomString(15)."file.xml";
        $res = $this->localDriver->createFromURL($this->uploadXML, DriveConfig::getUploadsPath(), $randUploadString);
        if ($res){
            helper()->sendMsg(self::getCurrentState(), 'XML File Download Completed');
            $this->xmlFullFilePath = DriveConfig::getUploadsPath() . DIRECTORY_SEPARATOR . $randUploadString;
            return $this->switchState(self::PhaseThree, self::NEXT);
        }

        return self::ERROR;
    }

    /**
     * @throws \Exception
     */
    public function PhaseThree()
    {
        if (!function_exists('simplexml_load_file')){
            helper()->sendMsg(self::getCurrentState(), 'simplexml_load_file function doesnt exist', 'issue');
            return self::ERROR;
        }
        $this->xmlObject = simplexml_load_file($this->xmlFullFilePath, null, LIBXML_NOCDATA);
        if ($this->xmlObject === false){
            helper()->sendMsg(self::getCurrentState(), 'Failed To Open XML File, Invalid FileType?', 'issue');
            return self::ERROR;
        }

        return $this->switchState(self::PhaseThree_ImportCategories, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function PhaseThree_ImportCategories()
    {
        helper()->sendMsg(self::getCurrentState(), 'Getting Set To Import Categories');
        $table = Tables::getTable(Tables::CATEGORIES);
        $lastHighestID = db()->row("SELECT cat_id FROM $table ORDER BY cat_id DESC LIMIT 1;");
        $lastHighestID = (isset($lastHighestID->cat_id)) ? $lastHighestID->cat_id + 1: 1;
        $cat = [];
        $postData = container()->get(PostData::class);
        $postCategory = container()->get(PostCategoryController::class);

        foreach ($this->xmlObject->channel->xpath('wp:category') as $category){
            $id = $lastHighestID;
            $name = (key_exists(0,  $category->xpath('wp:cat_name'))) ? $category->xpath('wp:cat_name')[0]->__toString() : "No Name $id";
            $slug = (key_exists(0,  $category->xpath('wp:category_nicename'))) ?  $category->xpath('wp:category_nicename')[0]->__toString() :  helper()->slug($name);
            $parent = (key_exists(0,  $category->xpath('wp:category_parent'))) ? $category->xpath('wp:category_parent')[0]->__toString() : '';
            $cat[$slug] = [
                'cat_id' => $id,
                'cat_slug' => $slug,
                'created_at' => helper()->date(),
                'cat_parent_id' => (!empty($parent) && key_exists($parent, $cat)) ? $cat[$parent]['cat_id'] : '',
                'cat_name' => $name,
                'cat_content' => (key_exists(0,  $category->xpath('wp:category_description'))) ? $category->xpath('wp:category_description')[0]->__toString() : '',
            ];

            ## Don't give it space to suck the memory, try force clear gc here...
            helper()->garbageCollect(callback: function () use (&$postCategory, &$postData){
                $this->resetPostCategoryImportDependencies($postCategory, $postData);
            });
            $result = $postCategory->storeFromImport($cat[array_key_last($cat)]);
            if ($result){
                helper()->sendMsg($this->getCurrentState(), "Imported Category: '$name' ✔");
            } else{
                helper()->sendMsg($this->getCurrentState(), "Failed To Import Category: '$name', Moving On Regardless...", 'issue');
            }
            ++$lastHighestID;

            if ($result instanceof OnPostCategoryCreate){
                $cat_slug = $cat[array_key_last($cat)]['cat_slug'];
                $postCatParents = $postData->getPostCategoryParents($cat_slug);
                $lastCat = isset($postCatParents[array_key_last($postCatParents)]) ? $postCatParents[array_key_last($postCatParents)]->path. '/' : '';
                // 'old' url is the key, and the value is where to redirect to...
                $this->urlRedirections[ "/$lastCat"] = "/categories/{$result->getSlugID()}/$slug";
            }
        }

        $this->categoryData = $cat;
        return $this->switchState(self::PhaseThree_ImportPosts, self::NEXT);
    }

    /**
     * @throws \Exception
     */
    public function PhaseThree_ImportPosts(): string
    {
        helper()->sendMsg(self::getCurrentState(), 'Getting Set To Import Posts');
        $shortCode = new WordPressShortCode();
        $urls = new WordPressWPContentURL();
        $postData = container()->get(PostData::class);
        $userData = container()->get(UserData::class);
        $postController = new PostsController($postData, $userData);
        $attachment = [];
        $noNameID = 1;
        $currentUserID = UserData::getCurrentUserID() ??  1;
        db()->run("SET SQL_MODE='ALLOW_INVALID_DATES';");
        foreach ($this->xmlObject->channel->item as $post){
            if ($post->xpath('wp:post_type')[0]->__toString() === 'attachment'){
                $namespaces = $post->getNameSpaces(true);
                $attachment[$post->children($namespaces['wp'])->post_id->__toString()] =
                    $post->children($namespaces['wp'])->attachment_url->__toString();
            }

            if ($post->xpath('wp:post_type')[0]->__toString() === 'post'){
                $namespaces = $post->getNameSpaces(true);
                $xmlContent = $post->children($namespaces['content']);
                $imageID = '';
                foreach ($post->xpath('wp:postmeta') as $meta){
                    if ($meta->xpath('wp:meta_key')[0]->__toString() === '_thumbnail_id'){
                        $imageID = $meta->xpath('wp:meta_value')[0]->__toString();
                    }
                }

                $seoTitleSupport = [
                    '_genesis_title' => '_genesis_title',
                    '_yoast_wpseo_title' =>  '_yoast_wpseo_title'
                ];

                $seoDescriptionSupport = [
                    '_genesis_description' => '_genesis_description',
                    '_yoast_wpseo_metadesc' =>  '_yoast_wpseo_metadesc'
                ];
                $seoTitle = ''; $seoDescription = '';
                foreach ($post->xpath('wp:postmeta') as $meta) {
                    $metaKey = $meta->xpath('wp:meta_key')[0]->__toString();
                    if (key_exists($metaKey, $seoTitleSupport)) {
                        $seoTitle = $meta->xpath('wp:meta_value')[0]->__toString();
                    }
                    if (key_exists($metaKey, $seoDescriptionSupport)) {
                        $seoDescription = $meta->xpath('wp:meta_value')[0]->__toString();
                    }
                }

                $imageUrl = (key_exists($imageID, $attachment)) ? $attachment[$imageID] : '';
                if (!empty($imageUrl)){
                    $path = DriveConfig::getWordPressImportUploadsPath().parse_url($imageUrl, PHP_URL_PATH);
                    $path = str_replace("/wp-content/uploads", '', $path);
                    $fileObject = $this->localDriver->convertFilePathToFileObject($path);
                    $imageUrl = (isset($fileObject->urlPreview)) ? $fileObject->urlPreview : $imageUrl;
                }
                $postStatus = ($post->children($namespaces['wp'])->status->__toString() === 'publish') ? 1: 0;
                ## The tab (\t) is a marker should in case we wanna reconsume at the beginning,
                ## meaning it would avoid our of index array in the characters
                $content = "\t" . trim($xmlContent->encoded->__toString());

                if(!empty($content)){
                    ## For ShortCode...
                    $shortCode->getView()->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => $content]));
                    $content = $shortCode->getView()->render('template');

                    ## For URL
                    $urls->getView()->storeDataInModeStorage('url', ['siteURL' => trim($this->siteURL, '/') . '/']);
                    $urls->getView()->setTemplateLoader(new TonicsTemplateArrayLoader(['template' => $content]));

                    ## Don't give it space to suck the memory, try force clear gc here...
                    helper()->garbageCollect(callback: function () use (&$postController, &$postData, &$userData, &$url, &$shortCode){
                        $this->resetPostImportDependencies($postController, $postData, $userData, $url, $shortCode);
                    });
                    $content = $urls->getView()->render('template');
                }

                ## Don't give it space to suck the memory, try force clear gc here...
                helper()->garbageCollect(callback: function () use (&$postController, &$postData, &$userData, &$url, &$shortCode){
                    $this->resetPostImportDependencies($postController, $postData, $userData, $url, $shortCode);
                });

                $cat_slug = $post->category->attributes()->nicename->__toString();
                $postCatParents = $postData->getPostCategoryParents($cat_slug);
                $name = (!empty($post->title->__toString())) ? $post->title->__toString() : "No Name $noNameID";
                $postSlug = (empty($post->children($namespaces['wp'])->post_name->__toString())) ? helper()->slug($name): $post->children($namespaces['wp'])->post_name->__toString();
                $postToImport = [
                    'post_title' => $name,
                    'post_slug' => $postSlug,
                    'created_at' => date("Y-m-d H:i:s", strtotime($post->pubDate)),
                    'category' => $post->category->__toString(),
                    'category_slug' => $cat_slug,
                    'fk_cat_id' => (isset($postCatParents[0]->cat_id)) ? $postCatParents[0]->cat_id: '',
                    'post_status' =>  $postStatus,
                    'image_url' => $imageUrl,
                    'user_id' => $currentUserID,
                    'excerpt' =>  $post->children($namespaces['excerpt'])->encoded->__toString(),
                    'post_content' => $content,
                    'seo_title' => $seoTitle,
                    'seo_description' => $seoDescription,
                    'og_title' => $seoTitle,
                    'og_description' => $seoDescription,
                ];
                /**
                 * @var $result OnPostCreate|bool
                 */
                $result = $postController->storeFromImport($postToImport);
                if ($result){
                    helper()->sendMsg($this->getCurrentState(), "Imported Post: '$name' ✔");
                } else{
                    helper()->sendMsg($this->getCurrentState(), "Failed To Import Post: '$name', Moving On Regardless...", 'issue');
                }
                ++$noNameID;

                if ($result instanceof OnPostCreate){
                    if (!empty($postCatParents)){
                        $lastCat = isset($postCatParents[array_key_last($postCatParents)]) ? $postCatParents[array_key_last($postCatParents)]->path. '/' : '';
                        // 'old' url is the key, and the value is where to redirect to...
                        $this->urlRedirections[ "/{$lastCat}$postSlug"] = "/posts/{$result->getSlugID()}/$postSlug";
                    }
                }
            }
        }

        db()->insertOnDuplicate(
            Tables::getTable(Tables::GLOBAL),
            [
                'key' => 'url_redirections',
                'value' => json_encode($this->urlRedirections, JSON_UNESCAPED_SLASHES)
            ],
            ['value']
        );

        return $this->switchState(self::PhaseDone, self::NEXT);
    }

    public function resetPostImportDependencies(&$postController, $postData, $userData, &$url, &$shortCode)
    {
        unset($url, $shortCode, $postController);
        $url = new WordPressWPContentURL();
        $shortCode = new WordPressShortCode();
        $postController = new PostsController($postData, $userData);
    }

    public function resetPostCategoryImportDependencies(&$postCategory, $postData)
    {
        unset($postCategory);
        $postCategory = new PostCategoryController($postData);
    }

    public function PhaseDone(): string
    {
        return self::DONE;
    }
    /**
     * @return LocalDriver
     */
    public function getLocalDriver(): LocalDriver
    {
        return $this->localDriver;
    }

    /**
     * @return string
     */
    public function getUploadZip(): string
    {
        return $this->uploadZip;
    }

    /**
     * @return string
     */
    public function getUploadXML(): string
    {
        return $this->uploadXML;
    }

    /**
     * @return string
     */
    public function getSiteURL(): string
    {
        return $this->siteURL;
    }

    /**
     * @return false|SimpleXMLElement
     */
    public function getXmlObject(): SimpleXMLElement|bool
    {
        return $this->xmlObject;
    }

    /**
     * @return array
     */
    public function getCategoryData(): array
    {
        return $this->categoryData;
    }
}