<?php

declare(strict_types=1);

use App\Core\Controller\ErrorPageController;
use App\Core\NewAppVersion;
use App\Core\Translations\T;
use App\Lib\DocPHT;
use App\Model\AccessLogModel;
use App\Model\AdminModel;
use App\Model\PageModel;
use App\Lib\DocBuilder;
use Flasher\Prime\Flasher;
use Flasher\Prime\Storage\StorageManager;
use Flasher\Prime\Config\Config;
use Flasher\Prime\Response\Presenter\HtmlPresenter;
use Flasher\Prime\Response\ResponseManager;
use Flasher\Prime\Response\Resource\ResourceManager;
use Flasher\Prime\EventDispatcher\EventDispatcher;
use Flasher\Prime\Factory\NotificationFactory;
use Nette\Application\Routers\RouteList;
use Nette\Bootstrap\Configurator;
use Nette\Http\Session;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    header('Content-Type: text/plain');
    echo 'Missing vendor/autoload.php. Did you run "composer install"?';
    exit(1);
}

if (file_exists(__DIR__ . '/src/config/config.php') && is_readable(__DIR__ . '/src/config/config.php')) {
    require __DIR__ . '/src/config/config.php';
} else {
    $installDir = str_replace(
        'index.php',
        'temp/install',
        (string)$_SERVER['SCRIPT_NAME']
    );
    header('Location: ' . $installDir);
    exit;
}

$configurator = new Configurator;
$configurator->setDebugMode(true); 
$logDir = __DIR__ . '/log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
@chmod($logDir, 0777); 
$configurator->enableTracy($logDir);
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->addConfig(__DIR__ . '/src/Config/nette.neon');

$container = $configurator->createContainer();
$application = $container->getByType('Nette\Application\Application');

$request = $container->getByType(IRequest::class);
$response = $container->getByType(IResponse::class);
$session = new Session($request, $response);
$session->start();

// --- FINAL CORRECT FLASHER INITIALIZATION ---
$flasherConfigData = require __DIR__ . '/src/config/flasher.php';
$config = new Config($flasherConfigData);
$storageManager = new StorageManager();
$notificationFactory = new NotificationFactory();
$eventDispatcher = new EventDispatcher();
$resourceManager = new ResourceManager($config);
$responseManager = new ResponseManager($resourceManager, $storageManager, $eventDispatcher);

$flasher = new Flasher('flasher', $responseManager, $storageManager);

$GLOBALS['flasher'] = $flasher;
$GLOBALS['flasherResponseManager'] = $responseManager; // This is the correct object for views
// --- End Flasher Init ---

require_once __DIR__ . '/src/route.php';