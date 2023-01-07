<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class devices_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $devices = new Devices($armor);

        // Add device
        $id = $devices->add('u:test', 'ios-test', 'ios');
        $this->assertEquals('ios-test', $id);

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345', 'user', true, true);
        $this->assertEquals(AuthSession::class, $session::class);

        // Get cookie
        $chk_id = Cookie::get('hid');
        $this->assertIsString($chk_id);

        // Check device exists
        $db = Di::get(DbInterface::class);
        $row = $db->getRow("SELECT * FROM armor_users_devices WHERE uuid = 'u:test' AND device_id = %s", hash('sha256', $chk_id));
        $this->assertIsArray($row);
        $this->assertEquals('u:test', $row['uuid']); 
    }

    /**
     * Get devices
     */
    public function test_get()
    {

        // Init
        $armor = $this->initArmor();
        $devices = new Devices($armor);

        // Get iOS
        $row = $devices->get('ios-test');
        $this->assertIsArray($row);
        $this->assertEquals('u:test', $row['uuid']);

        // Get all
        $rows = $devices->getUuid('u:test');
        $this->assertCount(2, $rows);

        // Test user
        $user = $armor->getUuid('u:test');
        $rows = $user->listDevices();
        $this->assertCount(2, $rows);

        // Get by type
        $rows = $devices->getUuid('u:test', 'pc');
        $this->assertCount(1, $rows);
        $this->assertEquals('u:test', $rows[0]['uuid']);
    }

    /**
     * Test delete
     */
    public function test_delete()
    {

        // Init
        $armor = $this->initArmor();
        $devices = new Devices($armor);

    // Delete
        $ok = $devices->delete('ios-test');
        $this->assertTrue($ok);
        $ok = $devices->delete('ios-test');
        $this->assertFalse($ok);

    // Delete by uuid
    $devices->deleteUuid('u:test');
        $rows = $devices->getUuid('u:test');
        $this->assertCount(0, $rows);

        // Purge
        $devices->purge();
    }

}



