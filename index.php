<?php

declare(strict_types=1);

// Use statements for all required classes
use DocPHT\Controller\AdminController;
use DocPHT\Controller\ErrorPageController;
use DocPHT\Controller\FormPageController;
use DocPHT\Controller\HomeController;
use DocPHT\Controller\LoginController;
use DocPHT\Core\Http\Session;
use System\Request;
use System\Route;
use Tracy\Debugger;

// Load Composer autoloader
require_once 'vendor/autoload.php';

// Load main configuration file, which defines the constants
if (file_exists('src/config/config.php')) {
    require_once 'src/config/config.php';
} else {
    die('Configuration file not found. Please set up your config.php.');
}

// Load the rest of the constants
require_once 'src/core/constants.php';

// Initialize the error debugger using the now-defined constants
Debugger::enable(DEVELOPMENT_MODE ? Debugger::DEVELOPMENT : Debugger::PRODUCTION, LOG_PATH);
Debugger::$errorTemplate = __DIR__ . '/src/views/error_page.php';

// Initialize Session and Request objects
$session = new Session();
$request = new Request();
$route = new Route($request);

// ---- ROUTE DEFINITIONS ----

// Home, Page, and Search Routes
$route->get('/', fn() => (new HomeController($session, $request))->index());
$route->get('/page/{slug:.*}', fn($slug) => (new HomeController($session, $request))->page($slug));
$route->post('/search', fn() => (new HomeController($session, $request))->search());

// Login/Logout Routes
$route->any('/login', fn() => (new LoginController($session, $request))->login());
$route->get('/logout', fn() => (new LoginController($session, $request))->logout());
$route->any('/lost-password', fn() => (new LoginController($session, $request))->lostPassword());
$route->any('/recovery/{token}', fn($token) => (new LoginController($session, $request))->recoveryPassword($token));

// Page Creation/Editing Routes
$route->any('/page/add', fn() => (new FormPageController($session, $request))->create());
$route->any('/page/edit', fn() => (new FormPageController($session, $request))->update());
$route->any('/page/delete', fn() => (new FormPageController($session, $request))->delete());
$route->any('/page/publish', fn() => (new FormPageController($session, $request))->publish());
$route->any('/page/home', fn() => (new FormPageController($session, $request))->setHome());

// Section Management Routes
$route->any('/section/add', fn() => (new FormPageController($session, $request))->addSection());
$route->any('/section/insert', fn() => (new FormPageController($session, $request))->insertSection());
$route->any('/section/modify', fn() => (new FormPageController($session, $request))->modifySection());
$route->any('/section/remove', fn() => (new FormPageController($session, $request))->removeSection());
$route->any('/section/sort', fn() => (new FormPageController($session, $request))->sortSection());

// Versioning Routes
$route->any('/version/save', fn() => (new FormPageController($session, $request))->saveVersion());
$route->any('/version/import', fn() => (new FormPageController($session, $request))->importVersion());
$route->any('/version/export', fn() => (new FormPageController($session, $request))->exportVersion());
$route->any('/version/restore', fn() => (new FormPageController($session, $request))->restoreVersion());
$route->any('/version/delete', fn() => (new FormPageController($session, $request))->deleteVersion());

// Admin Routes
$route->any('/admin/settings', fn() => (new AdminController($session, $request))->settings());
$route->any('/admin/add-user', fn() => (new AdminController($session, $request))->addUser());
$route->any('/admin/remove-user', fn() => (new AdminController($session, $request))->removeUser());
$route->any('/admin/update-password', fn() => (new AdminController($session, $request))->updatePassword());
$route->any('/admin/update-email', fn() => (new AdminController($session, $request))->updateEmail());
$route->any('/admin/backups', fn() => (new AdminController($session, $request))->backup());
$route->any('/admin/backup/save', fn() => (new AdminController($session, $request))->saveBackup());
$route->any('/admin/backup/delete', fn() => (new AdminController($session, $request))->deleteBackup());
$route->any('/admin/backup/import', fn() => (new AdminController($session, $request))->importBackup());
$route->any('/admin/backup/export', fn() => (new AdminController($session, $request))->exportBackup());
$route->any('/admin/backup/restore', fn() => (new AdminController($session, $request))->restoreOptions());
$route->any('/admin/translations', fn() => (new AdminController($session, $request))->translations());
$route->any('/admin/logo', fn() => (new AdminController($session, $request))->uploadLogo());
$route->any('/admin/logo/remove', fn() => (new AdminController($session, $request))->removeLogo());
$route->any('/admin/favicon/remove', fn() => (new AdminController($session, $request))->removeFav());
$route->any('/admin/last-logins', fn() => (new AdminController($session, $request))->lastLogin());

// Error Page
$route->any('/404', fn() => (new ErrorPageController($session, $request))->show());

// Execute the router
$route->end();