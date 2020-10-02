<?php

namespace Fluent\Authentication\Contracts;

interface StorageInterface
{
    /**
     * Return true if and only if storage is empty.
     *
     * @return bool
     * @throws ExceptionInterface If it is impossible to determine whether storage is empty
     */
    public function isEmpty();

    /**
     * Return the content storage.
     *
     * @return mixed
     * @throws ExceptionInterface If reading contents from storage is impossible
     */
    public function read();

    /**
     * Write content to the storage.
     *
     * @param mixed $contents
     * @throws ExceptionInterface If writing $contents to storage is impossible
     */
    public function write($contents);

    /**
     * Clear content from storage.
     *
     * @return void
     * @throws ExceptionInterface If clearing contents from storage is impossible
     */
    public function clear();
}
