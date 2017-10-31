<?php
namespace tests;

use Soupmix;
use Redis;

use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AbstractTestCases extends TestCase
{
    /**
     * @var \Soupmix\Cache\RedisCache $client
     */
    protected $client = null;

    protected $redisConfig = [
        'host'   => '127.0.0.1',
        'persistent' => null,
        'bucket' => 'default',
        'dbIndex' => 0,
        'port' => 6379,
        'timeout' => 2.5,
        'persistentId' => 'main',
        'reconnectAttempt' => 100
    ];

    public function testSetGetDeleteItem()
    {
        $ins1 = $this->client->set('test1', 'value1', new DateInterval('PT60S'));
        $this->assertTrue($ins1);
        $value1 = $this->client->get('test1');
        $this->assertEquals('value1',$value1);
        $delete = $this->client->delete('test1');
        $this->assertTrue($delete);
    }

    public function testMultiSetGetDeleteItems()
    {
        $cacheData = [
            'test1' => 'value1',
            'test2' => 'value2',
            'test3' => 'value3',
            'test4' => 'value4'
        ];
        $insMulti = $this->client->setMultiple($cacheData, new DateInterval('PT60S'));
        $this->assertTrue($insMulti);

        $getMulti = $this->client->getMultiple(array_keys($cacheData));

        foreach ($cacheData as $key => $value) {
            $this->assertArrayHasKey($key, $getMulti);
            $this->assertEquals($value, $getMulti[$key]);
        }
        $deleteMulti = $this->client->deleteMultiple(array_keys($cacheData));

        foreach ($cacheData as $key => $value) {
            $this->assertTrue($deleteMulti[$key]);
        }

        $cacheData = [
            'test1' => 'value1',
            'test2' => 'value2',
            'test3' => 'value3',
            'test4' => 'value4'
        ];
        $insMulti = $this->client->setMultiple($cacheData);
        $this->assertTrue($insMulti);
        $getMulti = $this->client->getMultiple(array_keys($cacheData));

        foreach ($cacheData as $key => $value) {
            $this->assertArrayHasKey($key, $getMulti);
            $this->assertEquals($value, $getMulti[$key]);
        }
        $deleteMulti = $this->client->deleteMultiple(array_keys($cacheData));

        foreach ($cacheData as $key => $value) {
            $this->assertTrue($deleteMulti[$key]);
        }
    }

    public function testIncrementDecrementItem()
    {
        $counter_i_1 = $this->client->increment('counter', 1);
        $this->assertEquals(1, $counter_i_1);
        $counter_i_3 = $this->client->increment('counter', 2);
        $this->assertEquals(3, $counter_i_3);
        $counter_i_4 = $this->client->increment('counter');
        $this->assertEquals(4, $counter_i_4);
        $counter_d_3 = $this->client->decrement('counter');
        $this->assertEquals(3, $counter_d_3);
        $counter_d_1 = $this->client->decrement('counter', 2);
        $this->assertEquals(1, $counter_d_1);
        $counter_d_0 = $this->client->decrement('counter', 1);
        $this->assertEquals(0, $counter_d_0);
    }



    public function testHasItem()
    {
        $has = $this->client->has('has');
        $this->assertFalse($has);
        $this->client->set('has', 'value');
        $has = $this->client->has('has');
        $this->assertTrue($has);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function failForReservedCharactersInKeyNames()
    {
        $result = $this->client->set('@key', 'value');
        var_dump($result);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function failForInvalidStringInKeyNames()
    {
        $this->client->set(1, 'value');

    }

    /**
     * @test
     */
    public function getConnectinReturnsRedisInstanceSuccessfully()
    {
        $redisInstance = $this->client->getConnection();

        $this->assertInstanceOf(Redis::class, $redisInstance);
    }

    public function testClear(){
        $clear = $this->client->clear();
        $this->assertTrue($clear);
    }
}
