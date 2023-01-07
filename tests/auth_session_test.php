<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Auth\Operations\{Password, Cookie};
use Apex\Armor\Auth\{SessionManager, AuthSession, Login};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Auth session test
 */
class auth_session_test extends TestUtils
{

    /**
     * Test login
     */
    public function test_login()
    {

        // Init
        $armor = $this->initArmor();
        $armor->purge();
        $db = Di::get(DbInterface::class);
        $db->query("DELETE FROM armor_keys");
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Login
        $login = new Login($armor);
        $session = $login->withPassword('test', 'password12345', 'user', true, true);
        $this->assertEquals(AuthSession::class, $session::class);

    }

}


