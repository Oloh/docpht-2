<?php

namespace System;

class Request
{
    // Declarations from previous fixes
    public $server;
    public $path;
    public $hostname;
    public $servername;
    public $secure;
    public $port;
    public $protocol;
    public $url;
    public $curl;
    public $extension;
    public $headers;
    public $method;
    public $query;
    public $args;
    public $body;
    public $files;
    public $cookies;
    public $ajax;

    private $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

    public function __construct()
    {
        $this->server = (object)$_SERVER;

        // --- START: Patched path calculation for subdirectory compatibility ---
        $requestUri = strtok($this->server->REQUEST_URI ?? '', '?');
        $basePath = dirname($this->server->SCRIPT_NAME ?? '');

        // Determine the path relative to the base path
        $path = substr(urldecode($requestUri), strlen($basePath));
        if ($path === false || $path === '') {
            $path = '/';
        }

        // Ensure the path starts with a slash
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        // Add a trailing slash to non-root paths to match the router's expectations
        if ($path !== '/') {
            $path = rtrim($path, '/') . '/';
        }
        $this->path = $path;
        // --- END: Patch ---
        
        $this->hostname = str_replace('www.', '', $this->server->HTTP_HOST ?? '');
        $this->servername = $this->server->SERVER_NAME;
        $this->secure = (isset($this->server->HTTPS) && $this->server->HTTPS != 'off');
        $this->port = $this->server->SERVER_PORT;
        $this->protocol = ($this->secure) ? 'https://' : 'http://';
        $this->url = $this->protocol . ($this->server->HTTP_HOST ?? '') . ($this->server->REQUEST_URI ?? '');
        $this->curl = 'curl -X ' . ($this->server->REQUEST_METHOD ?? '') . ' ' . $this->url;
        $this->extension = pathinfo($this->path, PATHINFO_EXTENSION);
        $this->headers = (object)$this->getHeaders();
        $this->method = $this->server->REQUEST_METHOD;
        $this->query = (object)$_GET;
        $this->args = (object)[];
        $this->body = (object)$_POST;
        $this->files = (object)$_FILES;
        $this->cookies = (object)$_COOKIE;

        $this->ajax = (isset($this->server->HTTP_X_REQUESTED_WITH) && (strtolower($this->server->HTTP_X_REQUESTED_WITH) == 'xmlhttprequest'));

        if ($this->is('put', 'patch', 'delete')) {
            parse_str(file_get_contents('php://input'), $body);
            $this->body = (object)$body;
        }

        foreach ($this->httpMethods as $method) {
            if ($this->is($method) && isset($this->body->{'_method'}) && in_array(strtoupper($this->body->{'_method'}), $this->httpMethods)) {
                $this->method = strtoupper($this->body->{'_method'});
            }
        }
        
        if (isset($this->server->HTTP_CONTENT_TYPE) && strpos($this->server->HTTP_CONTENT_TYPE, 'application/json') !== false) {
            $body = json_decode(file_get_contents('php://input'));
            if ($body) {
                foreach ($body as $k => $v) {
                    $this->body->{$k} = $v;
                }
            }
        }
    }

    protected function getHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, 5) == 'HTTP_') {
                $k = str_replace('_', ' ', substr($k, 5));
                $k = str_replace(' ', '-', ucwords(strtolower($k)));
                $headers[$k] = $v;
            }
        }
        return $headers;
    }

    public function is($methods)
    {
        $methods = is_array($methods) ? $methods : func_get_args();
        return in_array($this->method, array_map('strtoupper', $methods));
    }
}