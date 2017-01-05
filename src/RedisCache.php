<?php

namespace Soupmix\Cache;

use Soupmix\Cache\Exceptions\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use Redis;

class RedisCache implements CacheInterface
{
    const PSR16_RESERVED_CHARACTERS = ['{','}','(',')','/','@',':'];

    private $handler = null;

    private $serializer = 'PHP';

    /**
     * Connect to Redis service
     *
     * @param Redis $handler Configuration values that has dbIndex name and host's IP address
     *
     */
    public function __construct(Redis $handler)
    {
        $this->handler = $handler;
        if (function_exists('igbinary_serialize')) {
            $this->serializer = 'IGBINARY';
        }
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
        return $value ? $this->unserialize($value) : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = (int) $ttl;
        $value = $this->serialize($value);
        if ($ttl === 0) {
            return $this->handler->set($key, $value);
        }
        return $this->handler->set($key, $value, $ttl);
    }

    private function serialize($value)
    {
        return ($this->serializer === 'IGBINARY') ? igbinary_serialize($value) : serialize($value);
    }

    private function unserialize($value)
    {
        return ($this->serializer === 'IGBINARY') ? igbinary_unserialize($value) : unserialize($value);
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
        return $this->handler->flushDb();
    }
    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $defaults = array_fill(0, count($keys), $default);
        foreach ($keys as $key) {
            $this->checkReservedCharacters($key);
        }
        return array_merge(array_combine($keys, $this->handler->mGet($keys)), $defaults);
    }
    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->checkReservedCharacters($key);
        }
        if (($ttl === null) || ($ttl === 0)) {
            return $this->handler->mSet($values);
        }

        $return =[];
        foreach ($values as $key => $value) {
            $return[$key] =  $this->set($key, $value, $ttl);
        }
        return $return;
    }
    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->checkReservedCharacters($key);
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
        $this->checkReservedCharacters($key);
        return $this->handler->exists($key);
    }

    private function checkReservedCharacters($key)
    {

        if (!is_string($key)) {
            $message = sprintf('key %s is not a string.', $key);
            throw new InvalidArgumentException($message);
        }
        foreach (self::PSR16_RESERVED_CHARACTERS as $needle) {
            if (strpos($key, $needle) !== false) {
                $message = sprintf('%s string is not a legal value.', $key);
                throw new InvalidArgumentException($message);
            }
        }
    }
}
