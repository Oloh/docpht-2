<?php

declare(strict_types=1);

use DocPHT\Controller\AdminController;
use DocPHT\Controller\ErrorPageController;
use DocPHT\Controller\FormPageController;
use DocPHT\Controller\HomeController;
use DocPHT\Controller\LoginController;
use Instant\Core\Controller\BaseController;

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {

    // General Routes
    $r->addRoute('GET', '/', [HomeController::class, 'index']);
    $r->addRoute('GET', '/switch-theme', [BaseController::class, 'switchTheme']);
    $r->addRoute(['GET', 'POST'], '/lost-password', [LoginController::class, 'lostPassword']);
    $r->addRoute(['GET', 'POST'], '/recovery/{token:.+}', [LoginController::class, 'recoveryPassword']);
    $r->addRoute(['GET', 'POST'], '/login', [LoginController::class, 'login']);
    $r->addRoute('GET', '/logout', [LoginController::class, 'logout']);

    // Admin Routes
    $r->addGroup('/admin', function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '', [AdminController::class, 'settings']);
        $r->addRoute(['GET', 'POST'], '/update-password', [AdminController::class, 'updatePassword']);
        $r->addRoute(['GET', 'POST'], '/remove-user', [AdminController::class, 'removeUser']);
        $r->addRoute(['GET', 'POST'], '/add-user', [AdminController::class, 'addUser']);
        $r->addRoute(['GET', 'POST'], '/create-home', [AdminController::class, 'createHome']);
        $r->addRoute(['GET', 'POST'], '/backup', [AdminController::class, 'backup']);
        $r->addRoute(['GET', 'POST'], '/backup/save', [AdminController::class, 'saveBackup']);
        $r->addRoute(['GET', 'POST'], '/backup/export', [AdminController::class, 'exportBackup']);
        $r->addRoute(['GET', 'POST'], '/backup/delete', [AdminController::class, 'deleteBackup']);
        $r->addRoute(['GET', 'POST'], '/backup/import', [AdminController::class, 'importBackup']);
        $r->addRoute(['GET', 'POST'], '/backup/restore', [AdminController::class, 'restoreOptions']);
        $r->addRoute(['GET', 'POST'], '/upload-logo', [AdminController::class, 'uploadLogo']);
        $r->addRoute(['GET', 'POST'], '/remove-logo', [AdminController::class, 'removeLogo']);
        $r->addRoute(['GET', 'POST'], '/remove-fav', [AdminController::class, 'removeFav']);
        $r->addRoute(['GET', 'POST'], '/lastlogins', [AdminController::class, 'lastLogin']);
        $r->addRoute(['GET', 'POST'], '/update-email', [AdminController::class, 'updateEmail']);
        $r->addRoute(['GET', 'POST'], '/translations', [AdminController::class, 'translations']);
    });

    // Page Routes
    $r->addGroup('/page', function (FastRoute\RouteCollector $r) {
        $r->addRoute(['GET', 'POST'], '/search', [BaseController::class, 'search']);
        $r->addRoute(['GET', 'POST'], '/create', [FormPageController::class, 'getCreatePageForm']);
        $r->addRoute(['GET', 'POST'], '/add-section', [FormPageController::class, 'getAddSectionForm']);
        $r->addRoute(['GET', 'POST'], '/update', [FormPageController::class, 'getUpdatePageForm']);
        $r->addRoute(['GET', 'POST'], '/insert', [FormPageController::class, 'getInsertSectionForm']);
        $r->addRoute(['GET', 'POST'], '/modify', [FormPageController::class, 'getModifySectionForm']);
        $r->addRoute(['GET', 'POST'], '/remove', [FormPageController::class, 'getRemoveSectionForm']);
        $r->addRoute(['GET', 'POST'], '/sort', [FormPageController::class, 'getSortSectionForm']);
        $r->addRoute(['GET', 'POST'], '/delete', [FormPageController::class, 'getDeletePageForm']);
        $r->addRoute(['GET', 'POST'], '/import-version', [FormPageController::class, 'getImportVersionForm']);
        $r->addRoute(['GET', 'POST'], '/export-version', [FormPageController::class, 'getExportVersionForm']);
        $r->addRoute(['GET', 'POST'], '/restore-version', [FormPageController::class, 'getRestoreVersionForm']);
        $r->addRoute(['GET', 'POST'], '/delete-version', [FormPageController::class, 'getDeleteVersionForm']);
        $r->addRoute(['GET', 'POST'], '/save-version', [FormPageController::class, 'getSaveVersionForm']);
        $r->addRoute(['GET', 'POST'], '/publish', [FormPageController::class, 'getPublish']);
        $r->addRoute(['GET', 'POST'], '/home-set', [FormPageController::class, 'setHome']);
        $r->addRoute(['GET', 'POST'], '/{topic}/{filename}', [FormPageController::class, 'getPage']);
    });
    
    // Error Route
    $r->addRoute('GET', '/error', [ErrorPageController::class, 'getPage']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $controller = new ErrorPageController();
        $controller->getPage(); // This is the fix
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $controller = new ErrorPageController();
        $controller->getPage(); // This is the fix
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $class = $handler[0];
        $method = $handler[1];
        $controller = new $class;
        $controller->$method($vars);
        break;
}