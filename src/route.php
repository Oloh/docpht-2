<?php

declare(strict_types=1);

use App\Controller\AdminController;
use App\Controller\ErrorPageController;
use App\Controller\FormPageController;
use App\Controller\HomeController;
use App\Controller\LoginController;
use FastRoute\RouteCollector;

$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    // All routes are now simple and relative
    $r->addRoute('GET', '/admin', [AdminController::class, 'settings']);
    $r->addRoute('GET', '/login', [LoginController::class, 'login']);
    $r->addRoute('POST', '/login', [LoginController::class, 'login']);
    $r->addRoute('GET', '/logout', [LoginController::class, 'logout']);
    $r->addRoute('GET', '/admin/update-password', [AdminController::class, 'updatePassword']);
    $r->addRoute('POST', '/admin/update-password', [AdminController::class, 'updatePassword']);
    $r->addRoute('GET', '/admin/update-email', [AdminController::class, 'updateEmail']);
    $r->addRoute('POST', '/admin/update-email', [AdminController::class, 'updateEmail']);
    $r->addRoute('GET', '/admin/add-user', [AdminController::class, 'addUser']);
    $r->addRoute('POST', '/admin/add-user', [AdminController::class, 'addUser']);
    $r->addRoute('GET', '/admin/remove-user', [AdminController::class, 'removeUser']);
    $r->addRoute('POST', '/admin/remove-user', [AdminController::class, 'removeUser']);
    $r->addRoute('GET', '/admin/backups', [AdminController::class, 'backup']);
    $r->addRoute('POST', '/admin/save-backup', [AdminController::class, 'saveBackup']);
    $r->addRoute('POST', '/admin/restore-options', [AdminController::class, 'restoreOptions']);
    $r->addRoute('POST', '/admin/import-backup', [AdminController::class, 'importBackup']);
    $r->addRoute('POST', '/admin/export-backup', [AdminController::class, 'exportBackup']);
    $r->addRoute('POST', '/admin/delete-backup', [AdminController::class, 'deleteBackup']);
    $r->addRoute('GET', '/admin/translations', [AdminController::class, 'translations']);
    $r->addRoute('POST', '/admin/translations', [AdminController::class, 'translations']);
    $r->addRoute('GET', '/admin/upload-logo', [AdminController::class, 'uploadLogo']);
    $r->addRoute('POST', '/admin/upload-logo', [AdminController::class, 'uploadLogo']);
    $r->addRoute('GET', '/admin/remove-logo', [AdminController::class, 'removeLogo']);
    $r->addRoute('GET', '/admin/remove-favicon', [AdminController::class, 'removeFav']);
    $r->addRoute('GET', '/admin/last-logins', [AdminController::class, 'lastLogin']);
    $r->addRoute('GET', '/lost-password', [LoginController::class, 'lostPassword']);
    $r->addRoute('POST', '/lost-password', [LoginController::class, 'lostPassword']);
    $r->addRoute('GET', '/recovery-password/{id}', [LoginController::class, 'recoveryPassword']);
    $r->addRoute('POST', '/recovery-password/{id}', [LoginController::class, 'recoveryPassword']);
    $r->addRoute('GET', '/', [HomeController::class, 'index']); // Homepage route
    $r->addRoute('GET', '/search', [HomeController::class, 'search']);
    $r->addRoute('POST', '/search', [HomeController::class, 'search']);
    
    $r->addRoute('POST', '/p/create-page', [FormPageController::class, 'create']);
    $r->addRoute('GET', '/p/create-page', [FormPageController::class, 'create']);
    $r->addRoute('POST', '/p/edit/{name}', [FormPageController::class, 'edit']);
    $r->addRoute('GET', '/p/edit/{name}', [FormPageController::class, 'edit']);
    $r->addRoute('GET', '/p/delete/{name}', [FormPageController::class, 'delete']);
    $r->addRoute('POST', '/p/delete/{name}', [FormPageController::class, 'delete']);
    $r->addRoute('GET', '/p/add-section/{name:[^/]+}[/{order}]', [FormPageController::class, 'addSection']);
    $r->addRoute('POST', '/p/add-section/{name:[^/]+}[/{order}]', [FormPageController::class, 'addSection']);
    $r->addRoute('GET', '/p/insert-section/{name:[^/]+}/{order}', [FormPageController::class, 'insertSection']);
    $r->addRoute('POST', '/p/insert-section/{name:[^/]+}/{order}', [FormPageController::class, 'insertSection']);
    $r->addRoute('GET', '/p/modify-section/{name:[^/]+}/{order}', [FormPageController::class, 'modifySection']);
    $r->addRoute('POST', '/p/modify-section/{name:[^/]+}/{order}', [FormPageController::class, 'modifySection']);
    $r->addRoute('GET', '/p/remove-section/{name:[^/]+}/{order}', [FormPageController::class, 'removeSection']);
    $r->addRoute('POST', '/p/remove-section/{name:[^/]+}/{order}', [FormPageController::class, 'removeSection']);
    $r->addRoute('GET', '/p/sort-section/{name:[^/]+}/{order}', [FormPageController::class, 'sortSection']);
    $r->addRoute('POST', '/p/sort-section/{name:[^/]+}/{order}', [FormPageController::class, 'sortSection']);
    $r->addRoute('GET', '/p/publish-page/{name:[^/]+}/{version}', [FormPageController::class, 'publishPage']);
    $r->addRoute('POST', '/p/publish-page/{name:[^/]+}/{version}', [FormPageController::class, 'publishPage']);
    $r->addRoute('GET', '/p/{name}', [HomeController::class, 'page']);
});

// This block now correctly processes the URL
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// This removes the subdirectory path from the URI, making it relative
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if (strlen($basePath) > 1) {
    $uri = substr($uri, strlen($basePath));
}
if (!$uri) {
    $uri = '/';
}


$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $controller = new ErrorPageController();
        $controller->getPage();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $controller = new ErrorPageController();
        $controller->methodNotAllowed();
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $controller = new $handler[0]();
        $method = $handler[1];
        $controller->$method($vars);
        break;
}

exit;