<?php

namespace Fluent\Authentication\Storages;

use CodeIgniter\Config\Services;
use CodeIgniter\Session\SessionInterface;
use Fluent\Authentication\Contracts\StorageInterface;

class SessionStorage implements StorageInterface
{
    /**
     * Default session namespace.
     */
    const NAMESPACE_DEFAULT = 'CodeIgniter4_Authentication';

    /**
     * Object to proxy session storage.
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Session namespace.
     *
     * @var mixed
     */
    protected $namespace = self::NAMESPACE_DEFAULT;

    /**
     * Set session storage option and initialiaze session namespace object.
     *
     * @param null|mixed $namespace
     */
    public function __construct($namespace = null)
    {
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }

        $this->session = Services::session();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return ! $this->session->has($this->namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->session->get($this->namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function write($content)
    {
        return $this->session->set($this->namespace, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->session->destroy($this->namespace);
    }
}
