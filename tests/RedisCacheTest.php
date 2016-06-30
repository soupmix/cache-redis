<?php
namespace tests;

use Soupmix\Cache as c;
use Redis;
class RedisCacheTest extends AbstractTestCases
{
    /**
     * @var \Soupmix\Cache\RedisCache $client
     */
    protected $client = null;

    protected function setUp()
    {
        $handler = new Redis();
        $handler->connect(
            $this->redisConfig['host'],
            $this->redisConfig['port'],
            $this->redisConfig['timeout'],
            null,
            $this->redisConfig['reconnectAttempt']
        );
        $handler->select($this->redisConfig['dbIndex']);
        $this->client = new c\RedisCache($handler);
        $this->client->clear();
    }
}
