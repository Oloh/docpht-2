<?php

namespace System;

use Closure;
use System\Support\Str;

/**
 * A simple data object that extends ArrayObject to allow setting dynamic properties
 * without deprecation notices on PHP 8.2+.
 */
class RouteDataObject extends \ArrayObject
{
    public $app;
}

class Route
{
    private static $instance;
    /**
     * Named parameters list.
     */
    protected $pattern = [
        '/*' => '/(.*)',
        '/?' => '/([^\/]+)',
        'int' => '/([0-9]+)',
        'multiInt' => '/([0-9,]+)',
        'title' => '/([a-z_-]+)',
        'key' => '/([a-z0-9_]+)',
        'multiKey' => '/([a-z0-9_,]+)',
        'isoCode2' => '/([a-z]{2})',
        'isoCode3' => '/([a-z]{3})',
        'multiIsoCode2' => '/([a-z,]{2,})',
        'multiIsoCode3' => '/([a-z,]{3,})'
    ];
    private $routes = [];
    private $group = '';
    private $matchedPath = '';
    private $matched = false;
    private $pramsGroup = [];
    private $matchedArgs = [];
    private $pattGroup = [];
    private $fullArg = '';
    private $isGroup = false;
    private $groupAs = '';
    private $currentGroupAs = '';
    private $currentGroup = [];
    private $prams;
    private $currentUri;
    private $routeCallback = [];
    private $patt;
    public $Controller;
    public $Method;
    private $before = [];
    private $after = [];
    
    // --- START: Added property declaration to fix deprecation error ---
    public $req;
    // --- END: Added property ---

    public function __construct(Request $req)
    {
        $this->req = $req;
        defined('URL') || define('URL', $req->url);
    }

    public static function instance(Request $req)
    {
        if (null === static::$instance) {
            static::$instance = new static($req);
        }
        return static::$instance;
    }

    public function route(array $method, $uri, $callback, $options = [])
    {
        if (is_array($uri)) {
            foreach ($uri as $u) {
                $this->route($method, $u, $callback, $options);
            }
            return $this;
        }
        $options = array_merge(['ajaxOnly' => false, 'continue' => false], (array)$options);

        if ($uri != '/') {
            $uri = $this->removeDuplSlash($uri) . '/';
        }

        $pattern = $this->namedParameters($uri);
        $this->currentUri = $pattern;

        if ($options['ajaxOnly'] == false || $options['ajaxOnly'] && $this->req->ajax) {
            if ($this->matched === false) {
                $pattern = $this->prepare(
                    str_replace(['/?', '/*'], [$this->pattern['/?'], $this->pattern['/*']], $this->removeDuplSlash($this->group . $pattern))
                );

                $methodCheck = count($method) > 0 ? in_array($this->req->method, $method) : true;
                if ($methodCheck && $this->matched($pattern)) {
                    if ($this->isGroup) {
                        $this->prams = array_merge($this->pramsGroup, $this->prams);
                    }

                    $this->req->args = $this->bindArgs($this->prams, $this->matchedArgs);
                    $this->matchedPath = $this->currentUri;
                    $this->routeCallback[] = $callback;

                    if ($options['continue']) {
                        $this->matched = false;
                    }
                }
            }
        }
        $this->_as($this->removeParameters($this->trimSlash($uri)));
        return $this;
    }

    public function group($group, callable $callback, array $options = [])
    {
        $options = array_merge([
            'as' => $group,
            'namespace' => $group
        ], $options);

        if (is_array($group)) {
            foreach ($group as $k => $p) {
                $this->group($p, $callback, [
                    'as' => is_array($options['as']) ? ($options['as'][$k] ?? '') : $options['as'],
                    'namespace' => is_array($options['namespace']) ? ($options['namespace'][$k] ?? '') : $options['namespace']
                ]);
            }
            return $this;
        }
        $this->setGroupAs($options['as']);
        $group = $this->removeDuplSlash($group . '/');
        $group = $this->namedParameters($group, true);

        $this->matched($this->prepare($group, false), false);

        $this->currentGroup = $group;
        $this->group .= $group;
        
        $callback = Closure::bind($callback, $this, get_class());
        call_user_func_array($callback, $this->bindArgs($this->pramsGroup, $this->matchedArgs));

        $this->isGroup = false;
        $this->pramsGroup = $this->pattGroup = [];
        $this->group = substr($this->group, 0, -strlen($group));
        $this->setGroupAs(substr($this->getGroupAs(), 0, -(strlen(is_array($options['as']) ? '' : $options['as']) + 2)), true);

        return $this;
    }

    public function resource($uri, $controller, $options = [])
    {
        $options = array_merge([
            'ajaxOnly' => false,
            'idPattern' => ':int',
            'multiIdPattern' => ':multiInt'
        ], $options);

        if (class_exists($controller)) {
            $this->generated = false;
            $as = $this->trimc($uri);
            $as = ($this->getGroupAs() . '.') . $as;

            $withID = $uri.'/{id}'.$options['idPattern'];
            $deleteMulti = $uri.'/{id}'.$options['multiIdPattern'];

            $this->route(['GET'], $uri, [$controller, 'index'], $options)->_as($as);
            $this->route(['GET'], $uri. '/get', [$controller, 'get'], $options)->_as($as.'.get');
            $this->route(['GET'], $uri . '/create', [$controller, 'create'], $options)->_as($as.'.create');
            $this->route(['POST'], $uri, [$controller, 'store'], $options)->_as($as.'.store');
            $this->route(['GET'], $withID, [$controller, 'show'], $options)->_as($as.'.show');
            $this->route(['GET'], $withID . '/edit', [$controller, 'edit'], $options)->_as($as.'.edit');
            $this->route(['PUT', 'PATCH'], $withID, [$controller, 'update'], $options)->_as($as.'.update');
            $this->route(['DELETE'], $deleteMulti, [$controller, 'destroy'], $options)->_as($as.'.destroy');
            $this->route([], $uri . '/*', function (Request $req, Response $res) {
                http_response_code(404);
                $res->json(['error'=>'resource 404']);
            });
        } else {
            throw new \Exception("Not found Controller {$controller} try with namespace");
        }
    }

    public function controller($uri, $controller, $options = [])
    {
        if (class_exists($controller)) {
            $methods = get_class_methods($controller);
            foreach ($methods as $k => $v) {
                $split = Str::camelCase($v);
                $request = strtoupper(array_shift($split));
                $fullUri = $uri .'/'. implode('-', $split);

                if (isset($split[0]) && $split[0] == 'Index') {
                    $fullUri= $uri .'/';
                }

                $as = $this->trimc(strtolower($fullUri));
                $as = ($this->getGroupAs() . '.') . $as;
                $fullUri = [$fullUri.'/*', $fullUri];
                $call = [$controller, $v];

                if (isset($split[0]) && $split[0] == 'Index') {
                    $fullUri = $uri;
                }
                $this->route(explode('_', $request), $fullUri, $call, $options)->_as($as);
            }
        } else {
            throw new \Exception("Not found Controller {$controller} try with namespace");
        }
    }

    protected function bindArgs(array $pram, array $args)
    {
        if (count($pram) == count($args)) {
            $newArgs = array_combine($pram, $args);
        } else {
            $newArgs = [];
            foreach ($pram as $p) {
                $newArgs[$p] = array_shift($args);
            }

            if (isset($args[0]) && count($args) == 1) {
                foreach (explode('/', '/' . $args[0]) as $arg) {
                    $newArgs[] = $arg;
                }
                $this->fullArg = $newArgs[0] = $args[0];
            }
            if (count($args)) {
                $newArgs = array_merge($newArgs, $args);
            }
        }
        return $newArgs;
    }

    protected function namedParameters($uri, $isGroup = false)
    {
        $this->patt = [];
        $this->prams = [];
        return preg_replace_callback('/\/\{([a-z-0-9]+)\}\??(:\(?[^\/]+\)?)?/i', function ($m) use ($isGroup) {
            if (isset($m[2])) {
                $rep = substr($m[2], 1);
                $patt = isset($this->pattern[$rep]) ? $this->pattern[$rep] : '/' . $rep;
            } else {
                $patt = $this->pattern['/?'];
            }
            if (strpos($m[0], '?') !== false) {
                $patt = str_replace('/(', '(/', $patt) . '?';
            }

            if ($isGroup) {
                $this->isGroup = true;
                $this->pramsGroup[] = $m[1];
                $this->pattGroup[] = $patt;
            } else {
                $this->prams[] = $m[1];
                $this->patt[] = $patt;
            }
            return $patt;
        }, trim($uri));
    }

    protected function prepare($patt, $strict = true)
    {
        if (substr($patt, 0, 3) == '/(/') {
            $patt = substr($patt, 1);
        }
        return '~^' . $patt . ($strict ? '$' : '') . '~i';
    }

    protected function matched($patt, $call = true)
    {
        if (preg_match($patt, $this->req->path, $m)) {
            if ($call) {
                $this->matched = true;
            }
            array_shift($m);
            $this->matchedArgs = array_map([$this, 'trimSlash'], $m);
            return true;
        }
        return false;
    }

    protected function removeDuplSlash($uri)
    {
        return preg_replace('/\/+/', '/', '/' . $uri);
    }

    protected function trimSlash($uri)
    {
        return trim($uri, '/');
    }
    
    protected function trimc($str, $char = '.')
    {
        return trim($str, $char);
    }

    public function addPattern(array $patt)
    {
        $this->pattern = array_merge($this->pattern, $patt);
    }

    public function _as($name)
    {
        if (empty($name)) return $this;
        $name = rtrim($this->getGroupAs() . str_replace('/', '.', strtolower($name)), '.');
        $patt = $this->patt;
        $pram = $this->prams;
        if ($this->isGroup) {
            $patt = array_merge($this->pattGroup, $patt);
            if (count($patt) > count($pram)) {
                $pram = array_merge($this->pramsGroup, $pram);
            }
        }

        if (count($pram)) {
            foreach ($pram as $k => $v) {
                $pram[$k] = '/:' . $v;
            }
        }
        
        $replaced = $this->group . $this->currentUri;
        foreach ($patt as $k => $v) {
            $pos = strpos($replaced, $v);
            if ($pos !== false) {
                $replaced = substr_replace($replaced, $pram[$k], $pos, strlen($v));
            }
        }
        $this->routes[$name] = ltrim($this->removeDuplSlash(strtolower($replaced)), '/');
        return $this;
    }

    public function setGroupAs($as, $replace = false)
    {
        $as = str_replace('/', '.', $this->trimSlash(strtolower($as)));
        $as = $this->removeParameters($as);
        $this->currentGroupAs = $as;
        if ($this->groupAs == '' || empty($as) || $replace) {
            $this->groupAs = $as;
        } else {
            $this->groupAs .= '.' . $as;
        }
        return $this;
    }

    public function getGroupAs()
    {
        if ($this->groupAs == '')
            return $this->groupAs;
        else
            return $this->groupAs . '.';
    }

    protected function removeParameters($name)
    {
        if (preg_match('/[{}?:()*]+/', $name)) {
            $name = '';
        }
        return $name;
    }

    public function getRoute($name, array $args = [])
    {
        $name = strtolower($name);
        if (isset($this->routes[$name])) {
            $route = $this->routes[$name];
            foreach ($args as $k => $v) {
                $route = str_replace(':' . $k, $v, $route);
            }
            return $route;
        }
        return null;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function _use($callback, $event = 'before')
    {
        switch ($event) {
            case 'before':
                return $this->before('/*', $callback);
            default:
                return $this->after('/*', $callback);
        }
    }

    public function before($uri, $callback)
    {
        $this->before[] = [
            'uri' => $uri,
            'callback' => $callback
        ];
        return $this;
    }

    public function after($uri, $callback)
    {
        $this->after[] = [
            'uri' => $uri,
            'callback' => $callback
        ];
        return $this;
    }

    protected function emit(array $events) {
        $continue = true;
        foreach ($events as $cb) {
            if ($continue !== false) {
                $uri = $cb['uri'];
                $except = false;
                if (strpos($cb['uri'], '/*!') !== false){
                    $uri = substr($cb['uri'], 3);
                    $except = true;
                }

                $list = array_map('trim', explode('|', strtolower($uri)));
                foreach ($list as $item) {
                    $item = $this->removeDuplSlash($item);
                    if ($except) {
                        if ($this->matched($this->prepare($item, false), false) === false) {
                            $continue = $this->callback($cb['callback'], $this->req->args);
                            break;
                        }
                    } elseif ($list[0] == '/*' || $this->matched($this->prepare($item, false), false) !== false) {
                        $continue = $this->callback($cb['callback'], $this->req->args);
                        break;
                    }
                }
            }
        }
    }

    public function end() {
        ob_start();
        if ($this->matched && count($this->routeCallback)) {
            count($this->before) && $this->emit($this->before);
            foreach ($this->routeCallback as $call) {
                $this->callback($call, $this->req->args);
            }
            count($this->after) && $this->emit($this->after);
        } else if ($this->req->method != 'OPTIONS') {
            http_response_code(404);
            print('<h1>404 Not Found</h1>');
        }

        if (ob_get_length()) {
            ob_end_flush();
        }
        exit;
    }

    protected function callback($callback, array $args = [])
    {
        if (isset($callback)) {
            if (is_callable($callback) && $callback instanceof \Closure) {
                // --- START: Patched for PHP 8.2+ ---
                // We use our custom RouteDataObject instead of the standard ArrayObject
                // to prevent dynamic property deprecation notices.
                $o = new RouteDataObject($args);
                $o->app = App::instance();
                // --- END: Patch ---
                $callback = $callback->bindTo($o);
            } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                $fixcallback = explode('@', $callback, 2);
                $this->Controller = $fixcallback[0];

                if (is_callable(
                    $callback = [$fixcallback[0], (isset($fixcallback[1]) ? $fixcallback[1] : 'index')]
                )) {
                    $this->Method = $callback[1];
                } else {
                    throw new \Exception("Callable error on {$callback[0]} -> {$callback[1]} !");
                }
            }

            if (is_array($callback) && !is_object($callback[0])) {
                $callback[0] = new $callback[0];
            }

            if (isset($args[0]) && $args[0] == $this->fullArg) {
                array_shift($args);
            }
            
            return call_user_func_array($callback, $args);
        }
        return false;
    }

    public function __call($method, $args)
    {
        switch (strtoupper($method)) {
            case 'AS':
                return call_user_func_array([$this, '_as'], $args);
            case 'USE':
                return call_user_func_array([$this, '_use'], $args);
            case 'ANY':
                array_unshift($args, []);
                return call_user_func_array([$this, 'route'], $args);
        }
        
        $methods = explode('_', $method);
        $exists = [];
        foreach ($methods as $v) {
            if (in_array($v = strtoupper($v), ['POST', 'GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])) {
                $exists[] = $v;
            }
        }

        if (count($exists)) {
            array_unshift($args, $exists);
            return call_user_func_array([$this, 'route'], $args);
        }

        return is_string($method) && isset($this->{$method}) && is_callable($this->{$method})
            ? call_user_func_array($this->{$method}, $args) : null;
    }

    public function __set($k, $v)
    {
        $this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
    }
}