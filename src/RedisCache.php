<?php

namespace Soupmix\Cache;

use Soupmix\Cache\Exceptions\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use Redis;
use DateInterval;
use DateTime;

class RedisCache implements CacheInterface
{
    const PSR16_RESERVED_CHARACTERS = ['{','}','(',')','/','@',':'];

    private $handler;


    /**
     * Connect to Redis service
     *
     * @param Redis $handler Configuration values that has dbIndex name and host's IP address
     *
     */
    public function __construct(Redis $handler)
    {
        if (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) {
            $handler->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        }
        $this->handler = $handler;
    }

    public function getConnection()
    {
        return $this->handler;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $value = $this->handler->get($key);
        return $value ? $value : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $this->checkKeysValidity($key);
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }
        $setTtl = (int) $ttl;
        if ($setTtl === 0) {
            return $this->handler->set($key, $value);
        }
        return $this->handler->setex($key, $ttl, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return (bool) $this->handler->delete($key);
    }
    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->handler->flushDB();
    }
    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $defaults = array_fill(0, count($keys), $default);
        foreach ($keys as $key) {
            $this->checkKeysValidity($key);
        }
        return array_merge(array_combine($keys, $this->handler->mget($keys)), $defaults);
    }
    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->checkKeysValidity($key);
        }
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime('now'))->add($ttl)->getTimeStamp() - time();
        }
        $setTtl = (int) $ttl;
        if ($setTtl === 0) {
            return $this->handler->mset($values);
        }
        $return = true;
        foreach ($values as $key => $value) {
            $return = $return && $this->set($key, $value, $setTtl);

        }
        return $return;
    }
    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->checkKeysValidity($key);
        }
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




    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $this->checkKeysValidity($key);
        return $this->handler->exists($key);
    }

    private function checkKeysValidity($key)
    {
        if (!is_string($key)) {
            $message = sprintf('Key %s is not a string.', $key);
            throw new InvalidArgumentException($message);
        }
        foreach (self::PSR16_RESERVED_CHARACTERS as $needle) {
            if (strpos($key, $needle) !== false) {
                $message = sprintf('Key %s has not a legal value.', $key);
                throw new InvalidArgumentException($message);
            }
        }
    }
}
