<?php
namespace tests;

use Soupmix;

class RedisCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Soupmix\Cache\RedisCache $client
     */
    protected $client = null;

    protected function setUp()
    {
        $this->client = new Soupmix\Cache\RedisCache([

            'host'   => '127.0.0.1',
        ]);
        $this->client->clear();
    }

    public function testSetGetDeleteItem()
    {
        $ins1 = $this->client->set('test1','value1');
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

    public function testClear(){
        $clear = $this->client->clear();
        $this->assertTrue($clear);
    }

}
