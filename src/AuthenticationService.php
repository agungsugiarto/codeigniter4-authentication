<?php

namespace Fluent\Authentication;

use Fluent\Authentication\Contracts\{AuthenticationInterface, StorageInterface, AdapterInterface};
use Fluent\Authentication\Exceptions\RuntimeException;
use Fluent\Authentication\Storages\SessionStorage;

use function is_null;

class AuthenticationService implements AuthenticationInterface
{
    /**
     * Persistent storage handler.
     *
     * @var Contracts\StorageInterface
     */
    protected $storage;

    /**
     * Authentication adapter.
     *
     * @var Contracts\AdapterInterface
     */
    protected $adapter;

    /**
     * The constructor Authentication Service.
     *
     * @param null|Contracts\StorageInterface $storage
     * @param null|Contracts\AdapterInterface $adapter
     */
    public function __construct(?StorageInterface $storage = null, ?AdapterInterface $adapter = null)
    {
        if ($storage !== null) {
            $this->setStorage($storage);
        }

        if ($adapter !== null) {
            $this->setAdapter($adapter);
        }
    }

    /**
     * Set the authentication $adapter
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @param Contracts\AdapterInterface $adapter
     * @return $this Provides a fluent interface
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the authentication adapter.
     *
     * The adapter does not have a default if the storage adapter has not been set.
     *
     * @return null|Contracts\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set the persistent sorage handler.
     *
     * @param Contracts\StorageInterface $storage
     * @return $this Provides a fluent interface
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Returns the persistent storage handler.
     *
     * @return Contracts\StorageInterface
     */
    public function getStorage()
    {
        if (is_null($this->storage)) {
            $this->setStorage(new SessionStorage());
        }

        return $this->storage;
    }
    
    /**
     * {@inheritdoc}
     */
    public function authenticate(?AdapterInterface $adapter = null)
    {
        if (! $adapter) {
            if (! $adapter = $this->getAdapter()) {
                throw new RuntimeException(
                    'An adapter must be set or passed prior to calling authenticate()'
                );
            }
        }

        $result = $adapter->attempt();

        if ($this->hasIdentity()) {
            $this->cleanIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasIdentity()
    {
        return ! $this->getStorage()->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return;
        }

        return $storage->read();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanIdentity()
    {
        return $this->getStorage()->clear();
    }
}
