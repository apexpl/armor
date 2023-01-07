<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class brute_force_policy_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Get burte force policy
        $brute = new BruteForcePolicy();
        $brute->addRule(5, 30, 3600);
        $policy = new ArmorPolicy(
            username_column: 'username', 
            brute_force_policy: $brute
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Get invalid username error
        $login = new Login($armor);
        $ok = $login->withPassword('some_junk_user', 'junkpass');
        $this->assertNull($ok);
        $this->assertEquals('invalid_username', $login->getFailReason());

        // Get locked out
        for ($x=0; $x <= 5; $x++) { 
            $login = new Login($armor);
            $ok = $login->withPassword('test', 'junkpass');
            $this->assertNull($ok);

            if ($x < 4) { 
                $user = $armor->getUuid('u:test');
                $this->assertEquals(ArmorUser::class, $user::class);
                $this->assertFalse($user->isFrozen());
            }
        $this->assertEquals('invalid_password', $login->getFailReason());
        }

        // Check user frozen
        $user = $armor->getUuid('u:test');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->assertTrue($user->isFrozen());

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'is_frozen');
        $this->assertEquals('true', $vars['new_value']);

        // Check login again
        $ok = $login->withPassword('test', 'password12345');
        $this->assertNull($ok);
        $this->assertEquals('frozen', $login->getFailReason());

        // Test unfreeze
        $db = Di::get(DbInterface::class);
        $db->query("UPDATE armor_users SET unfreeze_at = %s WHERE uuid = 'u:test'", $db->subtractTime('minute', 1, date('Y-m-d H:i:s')));
        $s = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $s::class);

        // Check user
        $user = $armor->getUuid('u:test');
        $this->assertFalse($user->isFrozen());
    }


}



