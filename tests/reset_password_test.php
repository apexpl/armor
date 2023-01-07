<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Verify\{VerifyEmail, InitialPassword, ResetPassword};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 ( Define password after registration test
 */
class reset_password_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor();
        $armor->purge();

        // Create profile
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Initialize password reset
        $reset = Di::make(ResetPassword::class);
        $count = $reset->byEmail('test@apexpl.io');
        $this->assertEquals(1, $count);

        // Get e-mail from redi
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('reset_password', $vars['type']);

        // Verify e-mail
        $ver  = Di::make(VerifyEmail::class);
        $ver->verify($vars['code']);

        // Check redis
        $uuid = $redis->get('armor:test:reset_password');
        $this->assertEquals('u:test', $uuid);

        // Define password
        $ver = Di::make(ResetPassword::class);
        $chk = $ver->finish('password56789');
        $this->assertEquals('u:test', $chk);

        // Load user
        $vuser = $armor->getUuid('u:test');
        $this->assertTrue($vuser->hasPassword());
        $this->assertTrue(Password::verify('password56789', $vuser->getPassword()));
    }

}



