<?php
namespace tests;

use Soupmix\Cache as c;
use Redis;
class RedisCachePersistentTest extends AbstractTestCases
{
    protected function setUp()
    {
        $handler = new Redis();
        $handler->pconnect(
            $this->redisConfig['host'],
            $this->redisConfig['port'],
            $this->redisConfig['timeout'],
            $this->redisConfig['persistentId']
        );
        $handler->select($this->redisConfig['dbIndex']);
        $this->client = new c\RedisCache($handler);
        $this->client->clear();
    }


}
