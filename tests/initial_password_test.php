<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Verify\{VerifyEmail, InitialPassword};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 ( Define password after registration test
 */
class initial_password_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Get policy
        $policy = new ArmorPolicy(
        username_column: 'email', 
            require_password: 'after_register', 
            require_email: 'unique', 
            require_phone: 'none'
        );

        // Initialize
        $armor = $this->initArmor($policy);

        // Delete previous test users, if exists
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users WHERE uuid LIKE 'u:test%'");

        // Create profile
        $profiles = $armor;
        $user = $armor->createUser('u:test', '', '', 'mdizak@apexpl.io', '');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', '', '', 'mdizak@apexpl.io', '');
        $this->assertFalse($user->hasPassword());
        $this->assertEquals('', $user->getPassword());

        // Get e-mail from redi
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('initial_password', $vars['type']);

        // Verify e-mail
        $ver  = Di::make(VerifyEmail::class);
        $ver->verify($vars['code']);

        // Check redis
        $uuid = $redis->get('armor:test:initial_password');
        $this->assertEquals('u:test', $uuid);

        // Define password
        $ver = Di::make(InitialPassword::class);
        $chk = $ver->finish('password12345');
        $this->assertEquals('u:test', $chk);

        // Load user
        $profiles = $armor;
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue($vuser->hasPassword());
        $this->assertTrue(Password::verify('password12345', $vuser->getPassword()));
    }

}



