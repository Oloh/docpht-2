<?php

namespace DocPHT\Core\Http;

use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class Session
{
    private SymfonySession $session;

    public function __construct()
    {
        // This creates a new session object using Symfony's secure, native session handling.
        $storage = new NativeSessionStorage();
        $this->session = new SymfonySession($storage, new AttributeBag());
        $this->start();
    }

    /**
     * Starts the session.
     */
    public function start(): bool
    {
        if ($this->isStarted()) {
            return true;
        }
        return $this->session->start();
    }

    /**
     * Checks if the session is started.
     */
    public function isStarted(): bool
    {
        return $this->session->isStarted();
    }

    /**
     * Sets a session variable.
     */
    public function set(string $name, $value): void
    {
        $this->session->set($name, $value);
    }

    /**
     * Gets a session variable.
     */
    public function get(string $name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    /**
     * Checks if a session variable is set.
     */
    public function has(string $name): bool
    {
        return $this->session->has($name);
    }

    /**
     * Removes a session variable.
     */
    public function remove(string $name): void
    {
        $this->session->remove($name);
    }

    /**
     * Returns a "flash" message for the next request.
     */
    public function pull(string $name, $default = null)
    {
        return $this->session->getFlashBag()->get($name, $default);
    }

    /**
     * Regenerates the session ID.
     */
    public function regenerate(bool $destroy = false, ?int $lifetime = null): bool
    {
        return $this->session->migrate($destroy, $lifetime);
    }

    /**
     * Destroys the session.
     */
    public function destroy(): bool
    {
        $this->session->invalidate();
        return !$this->isStarted();
    }
}