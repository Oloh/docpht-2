<?php

declare(strict_types=1);

use App\Core\Controller\ErrorPageController;
use App\Core\NewAppVersion;
use App\Core\translations\T;
use App\lib\DocPHT;
use App\Model\AccessLogModel;
use App\Model\AdminModel;
use App\Model\PageModel;
use App\lib\DocBuilder;
use Flasher\Prime\Flasher;
use Flasher\Prime\Storage\StorageManager;
use Flasher\Prime\Config\Config;
use Flasher\Prime\Response\Presenter\HtmlPresenter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Bootstrap\Configurator;
use Nette\Http\Session;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    header('Content-Type: text/plain');
    echo 'Missing vendor/autoload.php. Did you run "composer install"?';
    exit(1);
}

if (version_compare(phpversion(), '7.4.0', '<=')) {
    echo T::js('This app required PHP 7.4 or newer.');
    exit;
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

// Check if maintenance mode is defined and enabled
if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) {
    $errorController = new ErrorPageController();
    $errorController->serviceUnavailable();
    exit;
}
$configurator = new Configurator();

// Check if debug mode is defined and enabled
$configurator->setDebugMode(defined('DEBUG_MODE') && DEBUG_MODE === true);

$configurator->enableTracy(__DIR__ . '/log');
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->addConfig(__DIR__ . '/src/config/nette.neon');

$container = $configurator->createContainer();
$application = $container->getByType('Nette\Application\Application');

$router = new RouteList();

if (file_exists(__DIR__ . '/src/route.php') && is_readable(__DIR__ . '/src/route.php')) {
    require_once __DIR__ . '/src/route.php';
}
$application->getRouter(new RouteList());

$session = new Session($application->getHttpRequest(), $application->getHttpResponse());

$storageManager = new StorageManager($session);
$config = new Config(require __DIR__ . '/src/config/flasher.php');
$renderer = new HtmlPresenter($storageManager, $config->all());


$docpht = new DocPHT(
    VERSION,
    new AccessLogModel(),
    new DocBuilder($session),
    new PageModel($session),
    new AdminModel()
);

// Check if new version check is defined and enabled
if (defined('CHECK_NEW_VERSION') && CHECK_NEW_VERSION === true) {
    new NewAppVersion($docpht, $session);
}

$application->run();