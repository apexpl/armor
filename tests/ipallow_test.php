<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\Extra\IpAllow;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * IP allow test
 */
class ipallow_test extends TestUtils
{

    /**
     * Setup
     */
    public function setUp():void
    {

        $this->policy = new ArmorPolicy(
            enable_ipcheck: true
        );

    }

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor($this->policy);
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $ipallow = new IpAllow($armor);

        // Add IP
        $ipallow->add('u:test', '24.46');
        $ips = $ipallow->getUuid('u:test');
        $this->assertCount(1, $ips);
        $this->assertEquals('24.46', $ips[0]);

        // 
        $ipallow->add('u:test', '123.456');
        $ips = $ipallow->getUuid('u:test');
        $this->assertCount(2, $ips);

        // Check user
        $user = $armor->getUuid('u:test');
        $ips = $user->listIpAllow();
        $this->assertIsArray($ips);
        $this->assertCount(2, $ips);

        // Delete
        $ok = $ipallow->delete('u:test', '123.456');
        $this->assertTrue($ok);
        $ok = $ipallow->delete('u:test', '123.456');
        $this->assertFalse($ok);
        $ips = $ipallow->getUuid('u:test');
        $this->assertCount(1, $ips);

        // Delete uuid
        $ipallow->deleteUuid('u:test');
        $ips = $ipallow->getUuid('u:test');
        $this->assertNull($ips);
    }

    /**
     * Test login
     */
    public function test_login()
    {

        // Init
        $armor = $this->initArmor($this->policy);
        $ipallow = new IpAllow($armor);
        $ipallow->add('u:test', '24.68');

        // Check
        $ok = $ipallow->check('u:test', '24.68.125.62');
        $this->assertTrue($ok);
        $ok = $ipallow->check('u:test', '24.66.12.96');
        $this->assertFalse($ok);

        // Purge
        $ipallow->purge();
        $ips = $ipallow->getUuid('u:test');
        $this->assertNull($ips);
    }

}


