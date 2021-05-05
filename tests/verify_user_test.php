<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Verify\{VerifyEmail, VerifyEmailOTP, VerifyPhone};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');


/**
 * Verify user tests
 */
class verify_user_test extends TestUtils
{

    /**
     * verify 
     */
    public function test_verify()
    {

        // Verify e-mail
        $this->verifyEmailtest('require');
        $this->verifyEmailtest('optional');

        // Verify e-mail with OTP
        $this->verifyEmailtest('require_otp');
        $this->verifyEmailtest('optional_otp');

        // Verify phone
        $this->verifyPhonetest('require');
        $this->verifyPhonetest('optional');
    }

    /**
     * Verify e-mail
     */
    private function verifyEmailTest(string $verify_email)
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            verify_email: $verify_email, 
        );
        $armor = $this->initArmor($policy);
        $this->resetRedis();

        // Delete previous test users, if exists
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users WHERE uuid LIKE 'u:test%'");

        // Create profile
        $profiles = $armor;
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $this->assertFalse($user->isEmailVerified());

        // Check e-mail
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);

        // Init class
        if (in_array($verify_email, ['require_otp', 'optional_otp'])) { 
            $this->assertEquals('verify_otp', $vars['type']);
            $ver = Di::make(VerifyEmailOTP::class);
            $uuid = $ver->verify($user, $vars['code']);
        } else { 
            $this->assertEquals('verify', $vars['type']);
            $ver = Di::make(VerifyEmail::class);
            $uuid = $ver->verify($vars['code']);
        }
        $this->assertEquals('u:test', $uuid);

        // Verify user
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue($vuser->isEmailVerified());
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
    }

    /**
     * Verify phone
     */
    private function verifyPhoneTest(string $verify_phone)
    {

        // Get policy
        $policy = new ArmorPolicy(
            username_column: 'phone', 
            require_password: 'require', 
            require_email: 'optional', 
            require_phone: 'require', 
            verify_email: 'none',
    verify_phone: $verify_phone 
        );
        $armor = $this->initArmor($policy);
        $this->resetRedis();

        // Delete previous test users, if exists
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users WHERE uuid LIKE 'u:test%'");

        // Create profile
        $profiles = $armor;
        $user = $armor->createUser('u:test', 'password12345', 'test', '', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', '', '14165551234');
        $this->assertFalse($user->isPhoneVerified());
        $this->assertFalse($user->hasEmail());

        // Check e-mail
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:sms');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('verify', $vars['type']);

        // Verify e-mail address
        $ver = Di::make(VerifyPhone::class);
        $uuid = $ver->verify($user, $vars['code']);
        $this->assertEquals('u:test', $uuid);

        // Verify user
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue($vuser->isPhoneVerified());
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', '', '14165551234');
    }




}



