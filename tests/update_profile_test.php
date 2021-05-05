<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Updates\{UpdateUsername, UpdatePassword, UpdateEmail, UpdatePhone};
use Apex\Armor\User\Verify\{VerifyEmail, VerifyEmailOTP, VerifyPhone}; 
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Update profiles test
 */
class update_profile_test extends TestUtils
{

    /**
     * Setup
     */
    public function setUp():void
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'require', 
            verify_phone: 'require'
        );

    }

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor($this->policy);

        // Delete previous test users, if exists
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users WHERE uuid LIKE 'u:test%'");

        // Create profile
        $profiles = $armor;
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
    }

    /**
     * Update username
     */
    public function test_update_username()
    {

        // Get profile
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update username
        $user->updateUsername('utest');
        $this->assertEquals('utest', $user->getUsername());
        $this->verifyUser($armor, 'u:test', 'password12345', 'utest', 'test@apexpl.io', '14165551234');

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'username');
        $this->assertEquals('utest', $vars['new_value']);

    }

    /**
     * Update e-mail
     */
    public function test_update_email()
    {

        // Get profile
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update e-mail
        $user->updateEmail('utest@apexpl.io');

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyEmail::class);
        $ver->verify($vars['code']);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest@apexpl.io', $vuser->getEmail());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_email'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'email');
        $this->assertEquals('utest@apexpl.io', $vars['new_value']);
    }

    /**
     * Update e-mail - optional policy
     */
    public function test_update_email_optional()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'optional', 
            verify_phone: 'require'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update e-mail
        $user->updateEmail('utest_optional@apexpl.io');

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest_optional@apexpl.io', $vuser->getEmail());
        $this->assertFalse($vuser->isEmailVerified());

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyEmail::class);
        $ver->verify($vars['code']);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest_optional@apexpl.io', $vuser->getEmail());
        $this->assertTrue($vuser->isEmailVerified());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_email'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'email');
        $this->assertEquals('utest_optional@apexpl.io', $vars['new_value']);

    }

    /**
     * Update e-mail - require OTP
     */
    public function test_update_email_require_otp()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'require_otp', 
            verify_phone: 'require'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update e-mail
        $user->updateEmail('utest_reqotp@apexpl.io');

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify_otp', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyEmailOTP::class);
        $ver->verify($user, $vars['code']);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest_reqotp@apexpl.io', $vuser->getEmail());
        $this->assertTrue($vuser->isEmailVerified());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_email'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'email');
        $this->assertEquals('utest_reqotp@apexpl.io', $vars['new_value']);

    }

    /**
     * Update e-mail - optional policy OTP 
     */
    public function test_update_email_optional_otp()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'optional_otp', 
            verify_phone: 'require'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update e-mail
        $user->updateEmail('utest_otp@apexpl.io');

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest_otp@apexpl.io', $vuser->getEmail());
        $this->assertFalse($vuser->isEmailVerified());

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify_otp', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyEmailOTP::class);
        $chk = $ver->verify($user, $vars['code']);
        $this->assertEquals('u:test', $chk);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('utest_otp@apexpl.io', $vuser->getEmail());
        $this->assertTrue($vuser->isEmailVerified());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_email'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'email');
        $this->assertEquals('utest_otp@apexpl.io', $vars['new_value']);

    }


    /**
     * Update phone
     */
    public function test_update_phone()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'require', 
            verify_phone: 'require'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertEquals('14165551234', $user->getPhone());
        $this->assertFalse($user->isPhoneVerified());

        // Update e-mail
        $user->updatePhone('16045551234');

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:sms');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyPhone::class);
        $chk = $ver->verify($user, $vars['code']);
        $this->assertEquals('u:test', $chk);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('16045551234', $vuser->getPhone());
        $this->assertTrue($vuser->isPhoneVerified());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_phone'");
        $this->assertIsArray($log);
        $this->assertEquals('14165551234', $log['old_item']);
        $this->assertEquals('16045551234', $log['new_item']);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'phone');
        $this->assertEquals('16045551234', $vars['new_value']);

    }

    /**
     * Update phone - optional
     */
    public function test_update_phone_optional()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'optional_otp', 
            verify_phone: 'optional'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertEquals('16045551234', $user->getPhone());

        // Update e-mail
        $user->updatePhone('14035551234');

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('14035551234', $vuser->getPhone());
        $this->assertFalse($vuser->isPhoneVerified());

        // Check redis
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:sms');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify', $vars['type']);

        // Verify e-mail message
        $ver = Di::make(VerifyPhone::class);
        $chk = $ver->verify($user, $vars['code']);
        $this->assertEquals('u:test', $chk);

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('14035551234', $vuser->getPhone());
        $this->assertTrue($vuser->isPhoneVerified());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_phone'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'phone');
        $this->assertEquals('14035551234', $vars['new_value']);

    }

    /**
     * Update phone - optional
     */
    public function test_update_two_factor()
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'optional_otp', 
            verify_phone: 'optional', 
            two_factor_type: 'optional', 
            two_factor_frequency: 'optional'
        );

        // Get profile
        $armor = $this->initArmor($this->policy);
        $this->resetRedis();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users_log");

        // Create user
        $profiles = $armor;
        $profiles->purge();
        $armor->createUser('u:test', 'password12345', 'test', 'tset@apexpl.io', '14165551234');
        $user = $profiles->getUuid('u:test');
        $this->assertEquals('none', $user->getTwoFactorType());
        $this->assertEquals('none', $user->getTwoFactorFrequency());

        // Update two factor
        $user->updateTwoFactor('email','new_device');
        $this->assertEquals('email', $user->getTwoFactorType());
        $this->assertEquals('new_device', $user->getTwoFactorFrequency());

        // Load user
        $vuser = $profiles->getuuid('u:test');
        $this->assertEquals('email', $vuser->getTwoFactorType());
        $this->assertEquals('new_device', $vuser->getTwoFactorFrequency());

        // Check log
        $db = Di::get(DbInterface::class);
        $log = $db->getRow("SELECT * FROM armor_users_log WHERE uuid = 'u:test' AND action = 'change_two_factor'");
        $this->assertIsArray($log);

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'two_factor_frequency');
        $this->assertEquals('new_device', $vars['new_value']);

    }

}



