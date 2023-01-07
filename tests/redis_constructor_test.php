<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Auth\{Login, AuthSession, AutoLogin};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class redis_constructor_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Conenct to redis
        $redis = new redis();
        $redis->connect('127.0.0.1', 6379, 2);

        // Init
        $armor = new Armor(redis: $redis);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals(ArmorUser::class, $user::class);
        $user = $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

    }

}



