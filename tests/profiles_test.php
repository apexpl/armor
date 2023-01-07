<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Exceptions\{ArmorProfileValidationException, ArmorUsernameNotExistsException};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Profile tests
 */
class profiles_test extends TestUtils
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

        // Get no e-mail exception
        $this->expectException(ArmorProfileValidationException::class);
        $armor->createUser('u:test2', 'password12345', 'test2', 'junk_email', '14165551234');
    }

    /**
     * Find
     */
    public function test_find()
    {

        // Create a few users
        $armor = $this->initArmor();
        $armor->createUser('u:test5', 'password12345', 'jason', 'jason@gmail.com', '14165551233');
        $armor->createUser('u:test6', 'password12345', 'luke', 'luke@apexpl.io', '16045551234');

        // Find users
        $count = 0;
        $rows = $armor->find(email: 'apexpl.io');
        foreach ($rows as $row) { 
            $this->assertTrue(str_contains($row['email'], 'apexpl.io'));
            $count++;
        }
        $this->assertEquals(2, $count);

        // Get jason
        $rows = $armor->find(email: 'jason');
        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertIsArray($row);
        $this->assertEquals('jason', $row['username']);
    }

    /**
     * Remove user
     */
    public function test_remove()
    {

        // Get jason user
        $armor = $this->initArmor();
        $user = $armor->getUser('jason');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->assertEquals('jason@gmail.com', $user->getEmail());

        // Remove user
        $armor->removeUser('jason');

        // FInd user again
        $this->expectException(ArmorUsernameNotExistsException::class);
        $armor->getUser('jason');
    }

    /**
     * Test pruge
     */
    public function test_purge()
    {

        // Delete
        $armor = $this->initArmor();
        $armor->purge();

        // Ensure deleted
        $db = Di::get(DbInterface::class);
        if (!$count = $db->getField("SELECT count(*) FROM armor_users")) { 
            $count = 0;
        }
        $this->assertEquals(0, $count);
    }

}



