<?php

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\Password;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Db\Drivers\mySQL\mySQL;
use Apex\Db\Drivers\PostgreSQL\PostgreSQL;
use Apex\Db\Drivers\SQLite\SQLite;
use Apex\Armor\Interfaces\AdapterInterface;
use Apex\Armor\Adapters\TestAdapter;
use PHPUnit\Framework\TestCase;

$GLOBALS['dbconn'] = null;

/**
 * Test utils class
 */
class TestUtils extends TestCase
{

    /**
     * Get test connection
     */
    protected function initArmor(?ArmorPolicy $policy = null, string $policy_name = '')
    {

        // Get armor
        //$redis = $this->getRedis();
        $armor = new Armor(
            policy: $policy, 
        policy_name: $policy_name
        );

        // Get database
        if ($GLOBALS['dbconn'] === null) { 
            //$GLOBALS['dbconn'] = $this->getDbConnection();
        }

        // Load adapter
        require_once(__DIR__ . '/TestAdapter.php');
        Di::set(AdapterInterface::class, Di::make(TestAdapter::class));

        // Return
        return $armor;
    }

    /**
     * Get db connection
     */
    protected function getDbConnection()
    {

        $params = $this->getTestParams();

        // Connect
        $driver = $_SERVER['test_sql_driver'];
        if ($driver == 'sqlite') { 
            $db = new SQLite($params);
        } elseif ($driver == 'postgresql') { 
            $db = new PostgreSQL($params);
        } else { 
            $db = new mySQL($params);
        }
        Di::set(DbInterface::class, $db);
        return $db;
    }

    /**
     * Get connection params
     */
    protected function getTestParams()
    {

        $driver = $_SERVER['test_sql_driver'];
        if ($driver == 'sqlite') { 
            return ['dbname' => __DIR__ . '/../../test.db'];
        }

        // Set params
        $params = [];
        $parts = explode(',', $_SERVER['test_connection_' . $driver]);
        foreach ($parts as $part) { 
            list($key, $value) = explode('=', $part);
            $params[trim($key)] = trim($value);
        }

        // Return
        return $params;
    }

    /**
     * Get redis connection
     */
    public function getRedis()
    {

        // Set params
        $params = [];
        $parts = explode(',', $_SERVER['test_redis_info']);
        foreach ($parts as $part) { 
            list($key, $value) = explode('=', $part);
            $params[trim($key)] = trim($value);
        }

        // Connect to redis
        $redis = new redis();
        $redis->connect($params['host'], (int) $params['port']);

            // Auth
        $pass = $params['password'] ?? '';
        if ($pass != '') { 
            $redis->auth($pass);
        }

        // Select
        $dbindex = (int) ($params['dbindex'] ?? 0);
        if ($dbindex > 0) { 
            $redis->select($dbindex);
        }

        Di::set(redis::class, $redis);
        $this->resetRedis();

        return $redis;
    }

    /**
     * Reset redis
     */
    public function resetRedis()
    {

        $redis = Di::get(redis::class);

        $keys = $redis->keys('armor:*');
        foreach ($keys as $key) { 
            $redis->del($key);
        }

    }

    /**
     * Verify user
     */
    protected function verifyUser(Armor $armor, string $uuid, string $password, string $username, string $email, string $phone)
    {

        // Get profile
        $profiles = $armor;
        $user = $profiles->getUuid($uuid);
        $this->assertIsObject($user);
        $this->assertEquals($user::class, ArmorUser::class);
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($phone, $user->getPhone());
        if ($user->hasPassword()) { 
            $this->assertTrue(Password::verify($password, $user->getPassword()));
        }

        // Get by username
        $user_col = $armor->getPolicy()->getUsernameColumn();
        $chk_username = match($user_col) {
            'email' => $email, 
            'phone' => $phone, 
            default => $username
        };
        $user = $profiles->getUser($chk_username);
        $this->assertIsObject($user);
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->assertEquals($uuid, $user->getUuid());

        // Return
        return $user;
    }


}


