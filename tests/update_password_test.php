<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Updates\{UpdateUsername, UpdatePassword, UpdateEmail, UpdatePhone};
use Apex\Armor\User\Extra\PendingPasswords;
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\AES\{KeyManager, EncryptAES, DecryptAES};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Update profiles test
 */
class update_password_test extends TestUtils
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
        $profiles = $armor;
        $profiles->purge();

        // Generate master RSA key
        $key_manager = Di::make(KeyManager::class);
        $key_manager->generateMaster('master12345');

        // Create profile
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
    }

    /**
     * Update password - no encrypted data
     */
    public function test_update_password()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('password12345', $user->getPassword()));

        // Change password
        $user->updatePassword('password56789', 'password12345');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertfalse(Password::verify('password12345', $user->getPassword()));
        $this->assertTrue(Password::verify('password56789', $user->getPassword()));

        // Check onUpdate
        $redis = Di::get(redis::class);
        $vars = $redis->hgetall('armor:test:onupdate');
        $this->assertIsArray($vars);
        $this->assertEquals('u:test', $vars['uuid']);
        $this->assertEquals($vars['column'], 'password');
        $this->assertTrue(Password::verify('password56789', $vars['new_value']));

    }

    /**
     * Update password - no encrypted data, no old password
     */
    public function test_update_password_no_old_password()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('password56789', $user->getPassword()));

        // Change password
        $user->updatePassword('pass33321');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertfalse(Password::verify('password56789', $user->getPassword()));
        $this->assertTrue(Password::verify('pass33321', $user->getPassword()));
    }

    /**
     * Update password - with encrypted data
     */
    public function test_update_password_with_encrypted_with_old_password()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('pass33321', $user->getPassword()));

        // Change password
        // Encrypt data
        $enc = Di::make(EncryptAES::class);
        $data_id = $enc->toUuids("Testing 12345", ['u:test']);

        // Update pass
        $user->updatePassword('password12345', 'pass33321');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertfalse(Password::verify('pass33321', $user->getPassword()));
        $this->assertTrue(Password::verify('password12345', $user->getPassword()));

        // Decrypt
        $dec = Di::make(DecryptAES::class);
        $text = $dec->fromUuid($data_id, 'u:test', 'password12345');
        $this->assertEquals('Testing 12345', $text);
    }

    /**
     * Update password - with encrypted data, with master password
     */
    public function test_update_password_with_encrypted_without_old_password()
    {

        // Get user
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('password12345', $user->getPassword()));

        // Encrypt data
        $enc = Di::make(EncryptAES::class);
        $data_id = $enc->toUuids("Boxer was awesome", ['u:test']);

        // Update pass
        $status = $user->updatePassword('password56789');
        $this->assertEquals('pending_admin', $status);

        // Check redis
        $redis = Di::get(redis::class);
        $chk = $redis->get('armor:test:change_password');
        $this->assertEquals('u:test', $chk);

        // Check pending count
        $pending = Di::make(PendingPasswords::class);
        $count = $pending->getCount();
        $this->assertEquals(1, $count);

        // Get list
        $list = $pending->list();
        $this->assertCount(1, $list);
        $chk_user = $list[0]['user'];
        $this->assertEquals('u:test', $chk_user->getUuid());

        // Process
        $pending->processAll('master12345');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertfalse(Password::verify('pass33321', $user->getPassword()));
        $this->assertTrue(Password::verify('password56789', $vuser->getPassword()));

        // Decrypt
        $dec = Di::make(DecryptAES::class);
        $text = $dec->fromUuid($data_id, 'u:test', 'password56789');
        $this->assertEquals('Boxer was awesome', $text);
    }

    /**
     * Update with master password
     */
    public function test_update_master_password()
    {

        // Init
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $profiles->purge();

        // Generate master key
        $manager = Di::make(KeyManager::class);
        $manager->generateMaster('master12345');

        // Create user
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Change password
        $user = $profiles->getUuid('u:test');
        $user->updatePasswordWithMaster('password56789', 'master12345');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('password56789', $vuser->getPassword()));
    }

    /**
     * Update password, no master, no old, no enc data
     */
    public function test_update_password_nomaster_noold_noencdata()
    {

        // Init
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $profiles->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Change password
        $user = $profiles->getUuid('u:test');
        $user->updatePassword('password56789');

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertTrue(Password::verify('password56789', $vuser->getPassword()));
    }

    /**
     * Get exception, update with enc data
     */
    public function test_update_password_exception()
    {

        // Init, add enc data
        $armor = $this->initArmor($this->policy);
        $enc = Di::make(EncryptAES::class);
        $enc->toUuids('test 12345', ['u:test']);

        // Get user
        $profiles = $armor;
        $user = $profiles->getUuid('u:test');

        // Update password
        $this->expectException(ArmorProfileValidationException::class);
        $user->updatePassword('password98571725');
    }

}

