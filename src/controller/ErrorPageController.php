<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller\BaseController;
use App\Core\Translations\T;

class ErrorPageController extends BaseController
{

    public function getPage()
    {
        // This now provides a title, a path to the view file, and the specific error code.
        $this->view->load(
            '404 Not Found', 
            'error_page.php', 
            ['errorCode' => '404', 'errorMessage' => T::trans('Page not found.')]
        );
    }

    public function getPageNotAllowed()
    {
        $this->view->load(
            '405 Method Not Allowed', 
            'error_page.php', 
            ['errorCode' => '405', 'errorMessage' => T::trans('Method not allowed.')]
        );
    }

    public function serviceUnavailable()
    {
        $this->view->load(
            '503 Service Unavailable', 
            'error_page.php', 
            ['errorCode' => '503', 'errorMessage' => T::trans('The server is currently unable to handle the request due to a temporary overloading or maintenance of the server.')]
        );
    }

    public function methodNotAllowed()
    {
        $this->view->load(
            '405 Method Not Allowed', 
            'error_page.php', 
            ['errorCode' => '405', 'errorMessage' => T::trans('Method not allowed.')]
        );
    }
}