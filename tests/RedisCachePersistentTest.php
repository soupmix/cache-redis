<?php
namespace tests;

use Soupmix;

class RedisCachePersistentTest extends AbstractTestCases
{
    protected function setUp()
    {
        $this->client = new Soupmix\Cache\RedisCache([
            'host'       => '127.0.0.1',
            'persistent' => true
        ]);
        $this->client->clear();
    }


}
