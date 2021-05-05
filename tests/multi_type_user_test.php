<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class multi_type_user_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor();
        $this->resetRedis();
        $armor->purge();
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');
        $admin = $armor->createUser('a:1', 'adminpass12345', 'admuser', 'admin@apexpl.io', '16045551234', 'admin');

        // Search by username
        $users = $armor->find(username: 'adm');
        $this->assertCount(0, $users);
        $users = $armor->find(username: 'adm', type: 'admin');
        $this->assertCount(1, $users);

        // Load user
        $user = $armor->getUuid('a:1');
        $this->assertEquals(ArmorUser::class, $user::class);
        $this->assertEquals('admuser', $user->getUsername());
        $this->assertEquals('admin', $user->getType());

        // Login with user
        $login = Di::make(Login::class);
        $session = $login->withPassword('admuser', 'adminpass12345');
        $this->assertNull($session);

        // Login again, with type admin
        $session = $login->withPassword('admuser', 'adminpass12345', 'admin');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('a:1', $session->getUuid());

        // Check login, get null
        $session = $armor->checkAuth();
        $this->assertNull($session);

        // Get session with type admin
        $session = $armor->checkAuth('admin');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('a:1', $session->getUuid());
    }

}


