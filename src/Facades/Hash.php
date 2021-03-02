<?php

namespace Fluent\Auth\Facades;

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\HasherInterface;
use Fluent\Auth\Passwords\Hash\AbstractManager;
use Fluent\Auth\Passwords\Hash\HashManager;

/**
 * @see \Fluent\Auth\Contracts\HasherInterface
 * @see \Fluent\Auth\Passwords\Hash\AbstractManager
 * @see \Fluent\Auth\Passwords\Hash\HashManager
 *
 * @method static HasherInterface driver($driver = null)
 * @method static $this extend($driver, Closure $callback)
 * @method static array getDrivers()
 * @method static string getDefaultDriver()
 * @method static array info(string $hashedValue)
 * @method static bool check(string $value, string $hashedValue, array $options = [])
 * @method static bool needsRehash(string $hashedValue, array $options = [])
 * @method static string make(string $value, array $options = [])
 */
class Hash
{
    /**
     * Facade hash instance service.
     *
     * @param string $method
     * @param array $arguments
     * @return AbstractManager|HashManager|HasherInterface
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::getSharedInstance('hash')->{$method}(...$arguments);
    }
}
