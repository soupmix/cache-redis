<?php

namespace Soupmix\Cache;

use Redis;

class RedisCache implements CacheInterface
{
    private static $defaults = [
        'persistent' => null,
        'bucket' => 'default',
        'dbIndex' => 0,
        'port' => 6379,
        'timeout' => 2.5,
        'persistentId' => 'main',
        'reconnectAttempt' => 100
    ];

    private $serializer = "PHP";

    public $handler = null;
    /**
     * Connect to Redis service
     *
     * @param array $config Configuration values that has dbIndex name and host's IP address
     *
     */
    public function __construct(array $config)
    {
        $this->handler = new Redis();
        $redisConfig= $this::$defaults;
        foreach ($config as $key=>$value) {
            $redisConfig[$key] = $value;
        }
        if (function_exists('igbinary_serialize')) {
            $this->serializer = "IGBINARY";
        }
        if ( isset($redisConfig['persistent']) && ($redisConfig['persistent'] === true)) {
            return $this->persistentConnect($redisConfig);
        }
        return $this->connect($redisConfig);
    }

    private function connect( array $redisConfig)
    {
        $this->handler->connect(
            $redisConfig['host'],
            $redisConfig['port'],
            $redisConfig['timeout'],
            null,
            $redisConfig['reconnectAttempt']
        );
        return $this->handler->select($redisConfig['dbIndex']);
    }

    private function persistentConnect( array $redisConfig)
    {
        $this->handler->pconnect(
            $redisConfig['host'],
            $redisConfig['port'],
            $redisConfig['timeout'],
            $redisConfig['persistentId']
        );
        return $this->handler->select($redisConfig['dbIndex']);
    }

    /**
     * Fetch a value from the cache.
     *
     * @param string $key The unique key of this item in the cache
     *
     * @return mixed The value of the item from the cache, or null in case of cache miss
     */
    public function get($key)
    {
        $value = $this->handler->get($key);
        return ($value) ? $this->unserialize($value) : null;
    }
    /**
     * Persist data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key The key of the item to store
     * @param mixed $value The value of the item to store
     * @param null|integer|DateInterval $ttl Optional. The TTL value of this item. If no value is sent and the driver supports TTL
     *                                       then the library may set a default value for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = intval($ttl);
        $value = $this->serialize($value);
        if($ttl ==0 ){
            return $this->handler->set($key, $value);
        }
        return $this->handler->set($key, $value, $ttl);
    }

    private function serialize($value)
    {
        return ($this->serializer === "IGBINARY") ? igbinary_serialize($value) : serialize($value);
    }

    private function unserialize($value)
    {
        return ($this->serializer === "IGBINARY") ? igbinary_unserialize($value) : unserialize($value);
    }
    /**
     * Delete an item from the cache by its unique key
     *
     * @param string $key The unique cache key of the item to delete
     *
     * @return bool True on success and false on failure
     */
    public function delete($key)
    {
        return (bool) $this->handler->delete($key);
    }
    /**
     * Wipe clean the entire cache's keys
     *
     * @return bool True on success and false on failure
     */
    public function clear()
    {
        return $this->handler->flushDb();
    }
    /**
     * Obtain multiple cache items by their unique keys
     *
     * @param array|Traversable $keys A list of keys that can obtained in a single operation.
     *
     * @return array An array of key => value pairs. Cache keys that do not exist or are stale will have a value of null.
     */
    public function getMultiple($keys)
    {
        return array_combine($keys, $this->handler->mGet($keys));
    }
    /**
     * Persisting a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param array|Traversable         $items An array of key => value pairs for a multiple-set operation.
     * @param null|integer|DateInterval $ttl   Optional. The amount of seconds from the current time that the item will exist in the cache for.
     *                                         If this is null then the cache backend will fall back to its own default behaviour.
     *
     * @return bool True on success and false on failure
     */
    public function setMultiple($items, $ttl = null)
    {
        if (($ttl === null) || ($ttl === 0)) {
            return $this->handler->mSet($items);
        }

        $return =[];
        foreach ($items as $key=>$value) {
            $return[$key] =  $this->set($key, $value, $ttl);
        }
        return $return;
    }
    /**
     * Delete multiple cache items in a single operation
     *
     * @param array|Traversable $keys The array of string-based keys to be deleted
     *
     * @return bool True on success and false on failure
     */
    public function deleteMultiple($keys)
    {
        $return =[];
        foreach ($keys as $key) {
            $return[$key] = (bool) $this->delete($key);
        }
        return $return;
    }
    /**
     * Increment a value atomically in the cache by its step value, which defaults to 1
     *
     * @param string  $key  The cache item key
     * @param integer $step The value to increment by, defaulting to 1
     *
     * @return int|bool The new value on success and false on failure
     */
    public function increment($key, $step = 1)
    {
        return $this->handler->incr($key, $step);
    }
    /**
     * Decrement a value atomically in the cache by its step value, which defaults to 1
     *
     * @param string  $key  The cache item key
     * @param integer $step The value to decrement by, defaulting to 1
     *
     * @return int|bool The new value on success and false on failure
     */
    public function decrement($key, $step = 1)
    {
        return $this->handler->decr($key, $step);
    }
}
