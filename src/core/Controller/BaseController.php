<?php

declare(strict_types=1);

namespace DocPHT\core\Controller;

use DocPHT\core\Http\Session;
use System\Request;

abstract class BaseController
{
    protected Session $session;
    protected Request $request;

    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
    }
}