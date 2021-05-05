<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\{Password, Cookie};
use Apex\Armor\Auth\{SessionManager, AuthSession, Login};
use Apex\Armor\Auth\TwoFactor\{TwoFactorEmail, TwoFactorEmailOTP, TwoFactorPhone};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Create with auth session
 */
class two_factor_test extends TestUtils
{

    /**
     * E-mail login
     */
    public function test_email_login()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            two_factor_type: 'email', 
            two_factor_frequency: 'always'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('email', $session->getStatus());

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('email', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorEmail::class);
        $ok = $ver->verify($vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }

    /**
     * E-mail OTP login
     */
    public function test_email_otp_login()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            two_factor_type: 'email_otp', 
            two_factor_frequency: 'always'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('email_otp', $session->getStatus());

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('email_otp', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor_otp', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorEmailOTP::class);
        $ok = $ver->verify($user, $vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }

    /**
     * Phone login
     */
    public function test_phone_login()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            two_factor_type: 'phone', 
            two_factor_frequency: 'always'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('phone', $session->getStatus());

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('phone', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:sms');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorPhone::class);
        $ok = $ver->verify($user, $vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }


    /**
     * E-mail session
     */
    public function test_email_session()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            two_factor_type: 'optional', 
            two_factor_frequency: 'optional'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $user->updateTwoFactor('email', 'always');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('email', $session->getStatus());

        // Require two factor
        $_POST['test_name'] = "Matt Dizak";
        $session->requireTwoFactor();

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('email', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorEmail::class);
        $ok = $ver->verify($vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }

    /**
     * E-mail OTP session
     */
    public function test_email_otp_session()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            two_factor_type: 'optional', 
            require_phone: 'optional', 
            two_factor_frequency: 'optional'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $user->updateTwoFactor('email_otp', 'none');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);

        // Require 2FA
        $_POST['test_name'] = 'Matt Dizak';
        $session->requireTwoFactor();

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('email_otp', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:email');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor_otp', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorEmailOTP::class);
        $ok = $ver->verify($user, $vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }

    /**
     * Phone session
     */
    public function test_phone_session()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            two_factor_type: 'optional', 
            two_factor_frequency: 'optional'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $user->updateTwoFactor('phone', 'none');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);

        // Require 2fa
        $_POST['test_name'] = 'Matt Dizak';
        $session->requireTwoFactor();

        // Check handle status
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:status');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals('phone', $vars['status']);

        // Check redis
        $vars = $redis->hgetall('armor:test:sms');
        $this->assertIsArray($vars);
        $this->assertEquals('two_factor', $vars['type']);

        // Verify
        $ver = Di::make(TwoFactorPhone::class);
        $ok = $ver->verify($user, $vars['code']);
        $this->assertTrue($ok);

        // Check redis
        $vars = $redis->hgetall('armor:test:2fa_auth');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
    }




}




