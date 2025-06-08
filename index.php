<?php

declare(strict_types=1);

// Use statements for all required classes
use DocPHT\Controller\AdminController;
use DocPHT\Controller\ErrorPageController;
use DocPHT\Controller\FormPageController;
use DocPHT\Controller\HomeController;
use DocPHT\Controller\LoginController;
use DocPHT\core\Http\Session;
use DocPHT\Core\NewAppVersion;
use DocPHT\Core\Translator\T;
use System\Route;
use System\Request;
use Tracy\Debugger;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PhpFileLoader;

// Load Composer autoloader
require_once 'vendor/autoload.php';

// Load main configuration file, which defines the constants
require_once 'src/config/config.php'; 

// Load the rest of the constants
require_once 'src/core/constants.php';

// Initialize the error debugger using the now-defined constants
Debugger::enable(DEVELOPMENT_MODE ? Debugger::DEVELOPMENT : Debugger::PRODUCTION, LOG_PATH);
Debugger::$errorTemplate = __DIR__ . '/src/views/error_page.php';

// Initialize Session and Request objects
$session = new Session();
$request = new Request();

// Initialize the modern translator
$translator = new Translator(LANGUAGE);
$translator->addLoader('php', new PhpFileLoader());
if (file_exists('src/translations/' . LANGUAGE . '.php')) {
    $translator->addResource('php', 'src/translations/' . LANGUAGE . '.php', LANGUAGE);
}
T::init($translator);

// ---- ROUTE DEFINITIONS ARE NOW CORRECT ----

Route::get('/', function () use ($session, $request) {
    (new HomeController($session, $request))->index();
});

Route::get('/search', function () use ($session, $request) {
    (new HomeController($session, $request))->search();
});

Route::any('/login', function () use ($session, $request) {
    (new LoginController($session, $request))->login();
});

Route::get('/logout', function () use ($session, $request) {
    (new LoginController($session, $request))->logout();
});

Route::any('/lost-password', function () use ($session, $request) {
    (new LoginController($session, $request))->lostPassword();
});

Route::any('/recovery/(:any)', function ($token) use ($session, $request) {
    (new LoginController($session, $request))->recoveryPassword($token);
});

Route::get('/page/add', function () use ($session, $request) {
    (new FormPageController($session, $request))->create();
});

Route::post('/page/add', function () use ($session, $request) {
    (new FormPageController($session, $request))->store();
});

Route::any('/admin/settings', function () use ($session, $request) {
    (new AdminController($session, $request))->settings();
});

// ... You must apply this correct syntax (Route::get, Route::post, Route::any) to ALL other routes in your file ...

Route::get('/(:any)/(:any)', function ($version, $page) use ($session, $request) {
    (new HomeController($session, $request))->page($version, $page);
});

Route::any('/404', function() use ($session, $request) {
    (new ErrorPageController($session, $request))->show();
});

Route::end();