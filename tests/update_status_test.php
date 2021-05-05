<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Updates\{UpdateUsername, UpdatePassword, UpdateEmail, UpdatePhone};
use Apex\Armor\User\Extra\PendingPasswords;
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\AES\{KeyManager, EncryptAES, DecryptAES};
use Apex\Armor\Auth\Login;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Update profiles test
 */
class update_status_test extends TestUtils
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
            verify_email: 'none', 
            verify_phone: 'none'
        );

    }

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $profiles->purge();

        // Create profile
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
    }

    /**
     * Deactivate user
     */
    public function test_deactivate()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertTrue($user->isActive());

        // Deactivate
        $user->deactivate();
        $this->assertFalse($user->isActive());

        // Check
        $vuser = $profiles->getUuid('u:test');
        $this->assertFalse($vuser->isActive());

        // Get activity log
        $log = $user->listActivityLog();
        $this->assertIsArray($log);
        $this->assertCount(2, $log);
        $this->assertEquals(2, $user->getActivityLogCount());

        // Login
        $login = new Login($armor);
        $ok = $login->withPassword('test', 'password12345');
        $this->assertNull($ok);
        $this->assertEquals('inactive', $login->getFailReason());

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'is_active');
        $this->assertEquals('false', $vars['new_value']);
    }

    /**
     * Activate user
     */
    public function test_activate()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertFalse($user->isActive());

        // Deactivate
        $user->activate();
        $this->assertTrue($user->isActive());

        // Check
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue($vuser->isActive());

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'is_active');
        $this->assertEquals('true', $vars['new_value']);

    }


    /**
     * Delete user
     */
    public function test_delete()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertFalse($user->isDeleted());

        // Deactivate
        $user->delete();
        $this->assertTrue($user->isDeleted());

        // Check
        $vuser = $profiles->getUuid('u:test', true);
        $this->assertTrue($vuser->isDeleted());

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'is_deleted');
        $this->assertEquals('true', $vars['new_value']);

    }

    /**
     * Undelete user
     */
    public function test_undelete()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test', true);
        $this->assertTrue($user->isDeleted());

        // Deactivate
        $user->undelete();
        $this->assertFalse($user->isDeleted());

        // Check
        $vuser = $profiles->getUuid('u:test');
        $this->assertFalse($vuser->isDeleted());

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'is_deleted');
        $this->assertEquals('false', $vars['new_value']);

    }

    /**
     * Create as pending
     */
    public function test_create_as_pending()
    {

        // Init
        $this->policy->setCreateAsPending(true);
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;


        // Create user
        $user = $armor->createUser('u:test2', 'password12345', 'test2', 'test2@apexpl.io', '16045551234');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->assertTrue($user->isPending());

        // Activate
        $user = $profiles->getUuid('u:test2');
        $this->assertTrue($user->isPending());
        $user->activate();
        $this->assertFalse($user->isPending());

        // Verify
        $vuser = $profiles->getUuid('u:test2');
        $this->assertFalse($vuser->isPending());
    }




}

