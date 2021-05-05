<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession, AutoLogin};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class auto_login_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Init
        $armor = $this->initArmor();
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234', 'admin');

        // Login
        $login = Di::make(AutoLogin::class);
        $session = $login->loginUuid('u:test');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('u:test', $session->getUuid());

        // Check auth
        $s = $armor->checkAuth('admin');
        $this->assertNotNull($s);
        $this->assertEquals(AuthSession::class, $s::class);
        $this->assertEquals('u:test', $s->getUuid());
    }

}


