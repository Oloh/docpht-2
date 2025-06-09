<?php

declare(strict_types=1);

namespace App\Core\Http;

use Laminas\Session\Container;
use Laminas\Session\SessionManager;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Validator\HttpUserAgent;
use Laminas\Session\Validator\RemoteAddr;

class Session
{
    protected $container;
    protected $manager;

    public function __construct()
    {
        $config = new SessionConfig();
        $config->setCookieLifetime(1800); // 30 minutes in seconds

        $this->manager = new SessionManager($config);

        // Add validators for session security
        $chain = $this->manager->getValidatorChain();
        $chain->attach('session.validator.http_user_agent', [new HttpUserAgent(), 'isValid']);
        $chain->attach('session.validator.remote_addr', [new RemoteAddr(), 'isValid']);

        $this->manager->start();
        $this->container = new Container('default', $this->manager);
    }

    public function set(string $name, $value)
    {
        $this->container->offsetSet($name, $value);
    }

    public function get(string $name)
    {
        return $this->container->offsetGet($name);
    }

    public function exists(string $name): bool
    {
        return $this->container->offsetExists($name);
    }

    public function delete(string $name)
    {
        $this->container->offsetUnset($name);
    }

    public function destroy()
    {
        $this->manager->destroy();
    }
}