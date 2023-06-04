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

use App\Apps\TonicsCloud\Library\LXD\Client;
use App\Apps\TonicsCloud\Library\LXD\URL;
use App\Apps\TonicsCloud\TonicsCloudActivator;
use App\Modules\Core\Boot\ModuleRegistrar\Interfaces\ExtensionConfig;
use App\Modules\Core\Configs\AppConfig;
use App\Modules\Core\Events\TonicsTemplateEngines;
use App\Modules\Core\Library\Authentication\Session;
use App\Modules\Core\Library\JobSystem\Job;
use App\Modules\Core\Library\SchedulerSystem\Scheduler;
use Devsrealm\TonicsContainer\Container;
use Devsrealm\TonicsDomParser\DomParser;
use Devsrealm\TonicsEventSystem\EventDispatcher;
use Devsrealm\TonicsQueryBuilder\TonicsQuery;
use Devsrealm\TonicsRouterSystem\Handler\Router;
use Devsrealm\TonicsRouterSystem\Route;
use Devsrealm\TonicsTemplateSystem\TonicsView;
use Exception;

/**
 * The initial loader of the app
 * Class InitLoader
 */
class InitLoader
{
    private Router $router;
    private TonicsView $tonicsView;
    private TonicsTemplateEngines $tonicsTemplateEngines;
    private EventDispatcher $eventDispatcher;
    private static ?Job $jobEventDispatcher = null;
    private static ?Scheduler $scheduler = null;

    private static bool $eventStreamAsHTML = false;

    /**
     * @return bool
     */
    public static function isEventStreamAsHTML(): bool
    {
        return self::$eventStreamAsHTML;
    }

    /**
     * If set to true, a br tag would be appended to every sent event stream message
     * @param bool $eventStreamAsHTML
     */
    public static function setEventStreamAsHTML(bool $eventStreamAsHTML): void
    {
        self::$eventStreamAsHTML = $eventStreamAsHTML;
    }

    /**
     * Yh, Boot up the application
     * @throws Exception
     */
    public function BootDaBoot()
    {
/*        $certificate = <<<CRT
def50200c47ef7d24bbabfcbf11b3fd1e7b923022bbbfa280263ddd642bbcb67d1d475a76a19f4eb43046003011602dba3b5ca599e9670ff88cd6d7b4137011390459536b903214d0fcf1ee40a72e73de8fd05af3d3ecc541b856e67cf5e0f2a98e0cde892b2ea43353154ac237ba262cc4f5726271b3ece51186db2e6124f6ecf8fe3e57b56e4ead1ff77e6ef0086d5a0c0eae5fda44eb0539c705ae5be569390da396f44ac4e9c643a64cdb1fd25f055fd35d824a7ea678fad319362d9d92a87460c45512b20cc8476f584bbe5c986ac276f49f1c8976cbbb02ae80c6c504c48daf0ad61494d40d6c9d871789b418af565cfcba66158136c3a8a986804a2f68d498c4d389e5608761a58ddeaecee56a4a0a324b4d4227030d4dad86ac7ef27de595ad8c8f6f166b2b812a4d164c65da05a38ab8f6ddb52898ad01d61779a28e940bd3c27bf5d323a08f0e048b0dc0147f1090f485cffe7bf8e65328eff29ff926da86f6192abe6e823e0b92533c59c27b2493e8c6e3b059b4d2ae2296d0db162707818a6aa04254a52b8c59c1b9bb688153d8d521ec58d44a9b30c270fbcce2532686621e46b88fd5366695fa604e677974f1bde874363dbba61efe4433a88b95ce4f099fd0674c098044cb019798de672dabde2791da16ad07fc1185bbe1f86b35d069197d85972f028f10f0efc158b2d56f92d835ebdbd4b1d04e3c4da0fe134f58a04439e8e4aea87e901f52ddf2e8a312b456891eca50e346d95a63a655aee3c172e0f286c2731df54384f0ebeb88504a465fd7a5ff346bb4a7ea6cffc0a9f53b1299cde7f8a22b285fa27846e3000e88f3ff4eaf81e4a95a4ffb051913d8e6f85e19051c91516a5c25d1ecb9cbd4fe994fbfd525a6af4ef6b728e1cb963975e8993a6b62078dceb7f33a76377a783604583383044925c5b323a1e1b8d382069e539b09353da0eb22c7ca5cad4011f13da8ce28b91d1651822de2398142466b533039f3551a398f54cd666e22f72e53c6c84caf5dcc86cecd3790c9407ed8a7e2e186e284dcff604fa80aeda0f7cb4ef372ec727a42017e4858204f2a9323640a6e3eaf662a3c1506d4201b2752e9082944c29c78c71de3e05232e789f08244d830c21bee62e6550488c727498e389358a045bb2850d7e1422e8c792609da3542141c1c5faed0acbc4db2b9d4d02b8a43d89969fe657707a63130c4238204898b65e77f0b0fb18a60f7df84b1def3fa603318acf77a3c7fc69d77ee8702d8b8372945e909ea7c5fbc1d6f9b12bc49b031762f6cd9be8b606359d344e128823f494d8d4ae6f4a9eefc9b7bfb6fb50d56ef4baf552de4f0fc90c3c6d8175b3387fd8a8dc79ec61d818cb29f8c87db71899cc061f0b1a8b6b06a43eb6002b1607ffd6fcead04bab205cf4b6deaf8f507a1b4a37e7e0e4391d4c717433ffe31a61d8ed5d2a62399fbd51846bb5d1ff7031a08bfa4bc57b26340974927bdd409cf392baf10ca46ca2ba3379d8a0e0efe03a04e172b70c65ba734b691526b7138d3d299e5e98cef558670ce91ded7c74c7160e741d411524c609a94ff90d4632f09009f03e5f786a785dd3432ca2e1e37880dca6b5d06384a8502fa6d49bf30092a121d1c056dbf4e9b7546bf3851b883781d29759708f0fe214dadab95dde72d0b254155e36758d93ef1fef6ccea80157f0c1d5fc8764ea1d97882c447b013c38a0fe180eb934e801696985962df1b9513d37dd93670f927deeabcafcdc9148277c9fdc72a0eb118acb66e5023e6b8e1b77ed805b009ce9ce947f63d2d414c7cce3f6f35595351cd528bbd9a377360d5d1ee024b0cd03e12c7b60701ff4b85713d822605a6c4953b905d5b29c76cc13aed95426ffcac35cfd8859cfb38751610c4dd05800
CRT;
        $key = <<<CRT
def50200c361409477b65eecade9f482f9191a4f31e0015aaf1c3d3080bb0a231a3421cf9d9fe2a47c475b7fabbbc5195e29d3ddf2689ee5519a9fada54c2880563f437a307060a53b40ecc3a4c6337a16dad3da7114b45ec7cad444c8ae662535b94c3e21e88dcdbf0c3137e6adb620bd2b4689222a80f7df70533488b34f1c9a609172ff266c5e56112681adc168db10beadd8649ee842c08dcdd1c36a93db9ac94b3a001f28a0a385410aa2444c92e7db6fce8f2aaab9bc98cbdc7001e32e6ced14527db45e0a2ee60a0ea6abad8a934c731daac9c27ca9ca1ee5cbde8782fbedc416a4dfa7147317f7bc98ebaf68096617b6cbbd997191e63e79e358bb6dea3ebd9bac41eb8c7ca683d80202ef582a10816ee8ef5f377993a343f17cad7bfd83d0306d249be854a2c92b81edaa84849df1f7c67c1c868d99df90e574fcb89b058d2e9504637b1e43fdde23882eda21efe2b427b9ec8dcfbd053dd819278028f635d98f174b0bb720328e103604c43bfe08a0dbf54b7d1e6447aecf51cc60e57c94acff482148304203b3a63b2cf1ca2bee14929ff77e8ca9fb201be89633ff144459e93c3134cb69df11c74b03370ed5cf9df266d5a03bebf5aa8c4ce07679d376a6b3b2443b7059559c7180c27c86d87126e61ad727272f2f812a88cffc9b1058efaaa67560b67b7d603602ff148254274d4f09cfdd479883d3216898ca978edbd93dbeb96fd3070d729b70feb37a1160868c478dda0dabcc52517df23e86877d82687f1cf7e563a57a78cde4b10410eb3cddce7b05a0d989fd1b1b2b2d183750b76c1cb2bd730ab36618035dcf836a7b04379c54e9fe309ecda4e1e8d41a4f2762358f255b6b99b3d6984b8e69c7b7a604196ac2714fcf42751fb7d07bae41e3a0f4da31bba52d24b542f2cb5fd4a67dc02cc10be4daaa9f614f96cc319cca5e24aa3aec2faa7316dba2dbe38119c50a42780112fb2ddf0f5f382d2f2a05c92dbec45b0cb6b7cc812dbc7f8575e42c2f2eeea1091e8fed9bb0c41da8f306691984338da0ee101a7b2ddd8226e5d69ca973fc3d5a01fe9077fc1f8a29e772206c8f6effc4472e2114d8dbbdbd339047c6713bedad6501b5f23e822eb8dd046ecad3acca0fa436026ce79365cf39529abe53332e8663d7353f77e34879f018076b8c317290a87ddc92223c7b1d16a649b43bdcb7495065c93e94d567025a2c89aad9c416648f49054fcd6eb6c8c74697065f15b7c8a2bf0b83712522ddc81d56d471865eb793e693ee001e9e3f931255ac7631e4ce32e3615a96b7d10552e8b3133ace7728b4dde7af13aa1c6aae38e2031342f22ea8c436c8dc187009d1053df81e02adf0bb3c7c0e6199b82c505d2f56f5ae0eb6f314faf440b32792de3db85e34bb4791144b058c59d13d2e8647ed630b6a71e453fd78b1d779493f5d6391c34484c6eb759453e1a97ca54e76123be44294ac4a235edd04a753ffe385cf856cdc713b729664a9ea7e0427135e30ceea08782efed62e465855977febdd295219e0a96ec34260a407c80fede661aab51941d31d114eba9f48e7487d591dc969730bc951019fcefe9aaf711f1be6459de968a34f4b78037473f70b7ba23a49335fd47a214c50c44182a07e82a4acd304965cdbe87337a31202978a20d6b3569e1a1ef40a49aff6d7dc2a8d5a4039157344aa1841ae1b73a83ae17fcd08ac89a8df47d0edac946e8b573ff1eadbb29ee5f1790fef40ca01df84482237edf10e81b4a259e1f9e7180d3e38fd6a5c777d46d9dc3bcfa50a1d27ed7d41013975dd84527b944372827797cb8c3050e6cdc2cc7d065d9ed82d8960ea0f68d59fe2a51de5ae64ec3db6129710ba39d5974888aac19476f12fc7ce1aa0ea95af581832276abd8d888d33772381050a1e02f7955104ddfb3f6c165854cad15b7d20d354f30ef99649300e9e92679a62c45f5d240af6c5e955c2f6fbfd0aa8a3ccb602816c0fe7073356ce6a9ba72d27f872378ad70110b7d4a75ba2a182186a6af1e5474847a4ffa101020a3b677600bcb6f8f71404af360e56b2e51332dec024e7503a6f9d0a0726b4c1dc4e4a5509faede078f40a677a21e8fa64d209103a0458bff849acb19bad9753c99df9d8f96abab012b96aae70f2c5d7780b717db2d1d32fbd4017f3c566685b3f64d41c079adb243ca63f633077ac9fd21d47484c9c0b5645139b40b6e6463ad51ae279f2a919db063e2add5d56dfe4b93622f2954eee908eb58937e57a56fbf48556182f6ddbdfff848f8affc90e774f80b07e7c337b9a129b7a9a5b87800f27d6bd32200efa5c68aff881d650906dfd6cc3abfa37cf203787abab62cd0e8cdbd3c3752d67fa9549f1d93f3fd5bfabca7208fb53bfc61554624c085353a8f86eb6c1c15118e1c3495c3e319869850aec731a639ded6efec84633793f9b3fe05bdd458a3094b3149f96d4acca1d44ee9d0c2b09f4d200c8fe49e8e4a4e857ebddaa71d9c2a82dad9b699c897efccde14ad8d783
CRT;
        $certAndKey = [
            'cert' => LXDHelper::decryptCertificate($certificate),
            'key' => LXDHelper::decryptCertificate($key),
        ];
        $client = new Client(new URL('https://50.116.34.235:7597'), $certAndKey);
        $create = $client->instances()->create([
            "name" => "web-proxy-test" ,
            "description" => "My test instance",
            "source" => [
                "protocol" => "simplestreams",
                "alias" => "debian/bullseye/amd64",
                "server" => "https://images.linuxcontainers.org",
                "type" => "image"
            ],
            "devices" => [
                // ensure all the value are string, else, you would get an error
                "proxyPort80" => [
                    "type" => "proxy",
                    "listen" => "tcp:0.0.0.0:80", // listen to port on the host
                    "connect" => "tcp:127.0.0.1:80", // proxy it to the container or instance
                    "proxy_protocol" => "true",
                ]
            ]
        ]);
        dd($create);
        dd($client->instances()->metrics());
        dd($client->certificates()->add([
            "certificate" => LXDHelper::decryptCertificate(LXDHelper::generateCertificateEncrypted()['cert']),
            "name" => "tonics_cloud",
            "password" => "epbBUvhO3laWWNfHOZMyPqa4Bt3ncOj92rOu7hCTOTiUXIqmIZGebhK8JJPRzk5iFvsVQAUTOuK6k0xlPFglol3mlVSrgxaJNWT9eXF6dDMVCGlV6Nk12Cc8S/UC+ZWY6aoFHw==",
            "type" => 'client',
        ]));
        dd($client->instances()->instanceMetrics('web37'));
        if($result){
            dd($client->operations()->wait($result->operation));
        }*/


        //dd($response, $newInstance);
        if (AppConfig::isMaintenanceMode()){
            die("Temporarily down for schedule maintenance, check back in few minutes");
        }

                #-----------------------------------
            # HEADERS SETTINGS TEST
        #-----------------------------------
        if (AppConfig::TonicsIsReady()){
            response()->headers([
                'Access-Control-Allow-Origin: ' . AppConfig::getAppUrl(),
                'Access-Control-Allow-Credentials: true',
                'Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers: Origin, Accept, X-Requested-With, Content-Type, Authorization',
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: SAMEORIGIN',
                'Referrer-Policy: strict-origin-when-cross-origin',
                'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
                'Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()',
            ]);
        }

                #----------------------------------------------------
            # GATHER ROUTES AND PREPARE FOR PROCESSING
        #---------------------------------------------------
        request()->reset();
        foreach ($this->Providers() as $provider) {
            $this->getContainer()->register($provider);
        }
    }

    /**
     * @throws Exception
     */
    public static function getAllApps(): array
    {
        return helper()->getModuleActivators([ExtensionConfig::class], helper()->getAllAppsDirectory());
    }

    /**
     * @return HttpMessageProvider[]
     * @throws Exception
     */
    protected function Providers(): array
    {
        return [
            new HttpMessageProvider(
                $this->router
            )
        ];
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * @param string $transporterName
     * @return Job
     * @throws Exception
     */
    public static function getJobEventDispatcher(string $transporterName): Job
    {
        if (!self::$jobEventDispatcher) {
            self::$jobEventDispatcher = new Job($transporterName);
        }
        self::$jobEventDispatcher->setTransporterName($transporterName);
        return self::$jobEventDispatcher;
    }

    /**
     * @param string $transporterName
     * @return Scheduler
     * @throws Exception
     */
    public static function getScheduler(string $transporterName): Scheduler
    {
        if (!self::$scheduler) {
            self::$scheduler = new Scheduler($transporterName);
        }
        self::$scheduler->setTransporterName($transporterName);
        return self::$scheduler;
    }

    /**
     * @return TonicsQuery
     * @throws Exception
     */
    public static function getDatabase(): TonicsQuery
    {
        return db();
    }

    /**
     * @return DomParser
     * @throws Exception
     */
    public function getDomParser(): DomParser
    {
        return dom();
    }

    /**
     * @return TonicsTemplateEngines
     */
    public function getTonicsTemplateEngines(): TonicsTemplateEngines
    {
        return $this->tonicsTemplateEngines;
    }

    /**
     * @param TonicsTemplateEngines $tonicsTemplateEngines
     * @return InitLoader
     */
    public function setTonicsTemplateEngines(TonicsTemplateEngines $tonicsTemplateEngines): InitLoader
    {
        $this->tonicsTemplateEngines = $tonicsTemplateEngines;
        return $this;
    }

    /**
     * @param Router $router
     * @return InitLoader
     */
    public function setRouter(Router $router): InitLoader
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param TonicsView $tonicsView
     * @return InitLoader
     */
    public function setTonicsView(TonicsView $tonicsView): InitLoader
    {
        $this->tonicsView = $tonicsView;
        return $this;
    }

    /**
     * @param EventDispatcher $eventDispatcher
     * @return InitLoader
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher): InitLoader
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Register the route for the module
     *
     * @param ExtensionConfig $module
     * @return Route
     */
    protected function registerRoutes(ExtensionConfig $module): Route
    {
        return $module->route($this->getRouter()->getRoute());
    }

    /**
     * @return Session
     * @throws Exception
     */
    public function getSession(): Session
    {
        return \session();
    }


    /**
     * @return Container
     * @throws Exception
     */
    public function getContainer(): Container
    {
        return container();
    }

    /**
     * @return TonicsView
     */
    public function getTonicsView(): TonicsView
    {
        return $this->tonicsView;
    }

}