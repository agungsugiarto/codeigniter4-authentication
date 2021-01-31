<?php

namespace Fluent\Auth\Facades;

use Fluent\Auth\Config\Services;
use Fluent\Auth\Contracts\HasherInterface;

/**
 * @see \Illuminate\Hashing\HashManager
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
     * Static instance hash service.
     *
     * @param string $method
     * @param array $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        return Services::hash()->$method(...$arguments);
    }
}
