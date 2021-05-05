<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Create invalid test
 */
class create_invalid_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_invalid_email()
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

        // Get no e-mail exception
        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'password12345', 'test2', 'junk_email', '14165551234');
    }

    /**
     * No username
     */
    public function test_no_username()
    {
        $armor = $this->initArmor();
        $profiles = $armor;

        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'password12345', '', 'mdizak2@apexl.io', '14165551234');
    }

    /**
     * dupe uuid
     */
    public function testdupe_uuid()
    {
        $armor = $this->initArmor();
        $profiles = $armor;

        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test', 'password12345', 'test2', 'mdizak2@apexl.io', '14165551234');
    }

    /**
     * dupe username
     */
    public function test_dupe_username()
    {
        $armor = $this->initArmor();
        $profiles = $armor;

        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'password12345', 'test', 'mdizak2@apexl.io', '14165551234');
    }

    /**
     * weak password
     */
    public function test_weak_password()
    {
        $armor = $this->initArmor();
        $profiles = $armor;

        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'd', 'test2', 'mdizak2@apexl.io', '14165551234');
    }

    /**
     * min username length
     */
    public function test_min_username_length()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional', 
            min_username_length: 6
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $profiles = $armor;

        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'password12345', 't', 'mdizak2@apexl.io', '14165551234');
    }

    /**
     * Reserved usernames
     */
    public function test_reserved_username()
    {

        // Init
        $armor = $this->initArmor();
        $this->expectException(ArmorProfileValidationException::class);
    $armor->createUser('u:admin', 'password12345', 'admin123', 'admin@apexpl.io', '14165551234');
    }

}

