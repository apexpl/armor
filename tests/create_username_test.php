<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Login;
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Profile tests
 */
class create_username_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional'
        );

        // Initialize
        $armor = $this->initArmor($policy);

        // Delete previous test users, if exists
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_users WHERE uuid LIKE 'u:test%'");

        // Create profile
        $profiles = $armor;
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $this->assertEquals($user::class, ArmorUser::class);

        // Verify user
        $user = $this->verifyUser($armor, 'u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234');
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isDeleted());
        $this->assertFalse($user->isEmailVerified());
        $this->assertFalse($user->isPhoneVerified());
        $this->assertFalse($user->isPending());
        $this->assertEquals('1', $user->getPhoneCountryCode());
        $this->assertEquals('4165551234', $user->getPhoneNational());
    }

    /**
     * username column phone
     */
    public function test_username_column_phone()
    {

        // Get policy
        $policy = new ArmorPolicy(
            username_column: 'phone', 
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'optional' 
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $profiles = $armor;
        $user = $armor->createUser('u:test3', 'password12345', '', 'mdizak2@apexpl.io', '14165551235');
        $this->assertIsObject($user);
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->verifyUser($armor, 'u:test3', 'password12345', '', 'mdizak2@apexpl.io', '14165551235');
    }


    /**
     * username column email
     */
    public function test_username_column_email()
    {

        // Get policy
        $policy = new ArmorPolicy(
            username_column: 'email', 
            require_password: 'require', 
            require_email: 'optional', 
            require_phone: 'optional' 
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $profiles = $armor;
        $user = $armor->createUser('u:test2', 'password12345', '', '', '14165551234');
        $this->assertIsObject($user);
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->verifyUser($armor, 'u:test2', 'password12345', '', '', '14165551234');
    }

    /**
     * Create as pending
     */
    public function test_create_as_pending()
    {

        // Get policy
        $policy = new ArmorPolicy(
            create_as_pending: true, 
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'optional' 
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $profiles = $armor;
        $user = $armor->createUser('u:test4', 'password12345', 'test4', 'mdizak4@apexpl.io', '14165551235');
        $this->assertIsObject($user);
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->verifyUser($armor, 'u:test4', 'password12345', 'test4', 'mdizak4@apexpl.io', '14165551235');
        $this->assertTrue($user->isPending());

        // Login
        $login = new Login($armor);
        $ok = $login->withPassword('test4', 'password12345');
        $this->assertNull($ok);
        $this->assertEquals('pending', $login->getFailReason());
    }

    /**
     * Auto-generate UUids
     */
    public function test_generate_uuids()
    {

        // Init
        $armor = $this->initArmor();
        $armor->purge();
        $this->resetRedis();

        // Create some users
        $user = $armor->createUser('', 'password12345', 'test', 'test@apexpl.io');
        $user2 = $armor->createUser('', 'password12345', 'test2', 'test2@apexpl.io');
        $user3 = $armor->createUser('', 'password12345', 'test3', 'test3@apexpl.io');

        // Check
        $this->assertEquals('u:2', $user2->getUuid());
        $this->assertEquals('u:3', $user3->getUuid());

        // Check count
        $count = $armor->getCount();
        $this->assertEquals(3, $count);
    }

}



