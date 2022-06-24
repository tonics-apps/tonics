<?php

namespace App\Modules\Core\Controllers;

use App\InitLoader;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Data\ThemeData;
use App\Modules\Core\Library\SimpleState;
use App\Modules\Core\Library\ThemeSystem;
use Devsrealm\TonicsFileManager\Utilities\FileHelper;
use JetBrains\PhpStorm\NoReturn;

class ThemeController
{
    use FileHelper;

    private ThemeData $themeData;

    /**
     * @param ThemeData $themeData
     */
    public function __construct(ThemeData $themeData)
    {
        $this->themeData = $themeData;
    }

    /**
     * @throws \Exception
     */
    public function index(): void
    {
        $themes = InitLoader::getAllThemes();
        $themeListing = $this->getThemeData()->adminThemeListing($themes);

        view('Modules::Core/Views/Theme/index', [
            'SiteURL' => AppConfig::getAppUrl(),
            'ThemeListing' => $themeListing,
        ]);

    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function install(string $themeName): void
    {
        $themeObject = $this->getThemeData()->getThemeObject($themeName);
        if ($themeObject !== null){
            $themeSystem = new ThemeSystem($themeObject);
            $themeSystem->setCurrentState(ThemeSystem::OnThemeActivateState);
            $themeSystem->runStates();
            if ($themeSystem->getStateResult() === SimpleState::DONE){
                redirect(route('themes.index'));
            }
        }

        session()->flash(['An Error Occurred Activating Theme'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function uninstall(string $themeName): void
    {
        $themeObject = $this->getThemeData()->getThemeObject($themeName);
        if ($themeObject !== null){
            $themeSystem = new ThemeSystem($themeObject);
            $themeSystem->setCurrentState(ThemeSystem::OnThemeDeActivateState);
            $themeSystem->runStates();
            if ($themeSystem->getStateResult() === SimpleState::DONE){
                redirect(route('themes.index'));
            }
        }

        session()->flash(['An Error Occurred DeActivating Theme'], []);
        redirect(route('themes.index'));
    }

    /**
     * @throws \Exception
     */
    #[NoReturn] public function serve(string $themeName): void
    {
        $path = AppConfig::getThemesPath() . "/$themeName/Assets/" . request()->getParam('path');
        $ext = helper()->extension($path);
        if (helper()->fileExists($path)){
            $aliasPath = "/themes/$themeName/Assets/" . request()->getParam('path');
            switch($ext){
                case 'css':
                    $mime = 'text/css';
                    break;
                case 'js':
                    $mime = 'application/javascript';
                    break;
                default:
                    $mime =  mime_content_type($path);;
            }
            $this->serveDownloadableFile($aliasPath, helper()->fileSize($path), false, $mime);
        }
        die("Theme Resource Doesn't Exist");
    }
    /**
     * @return ThemeData
     */
    public function getThemeData(): ThemeData
    {
        return $this->themeData;
    }
}