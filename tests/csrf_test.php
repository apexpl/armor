<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Auth\{Login, AuthSession, AutoLogin};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Armor\Auth\Web\CSRF;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class csrf_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Init
        $armor = $this->initArmor();
        $this->resetRedis();
        $csrf = new CSRF($armor);

        // Init
        list($name, $value) = $csrf->init();
        $this->assertIsString($name);
        $this->assertIsString($value);
        $this->assertEquals(36, strlen($name));
        $this->assertEquals(48, strlen($value));

        // Verify
        $post = [$name => $value];
        $ok = $csrf->verify($post);
        $this->assertTrue($ok);
    }

}


