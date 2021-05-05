<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\Auth\{Login, AuthSession};
use Apex\Armor\User\Extra\LoginHistory;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class login_history_test extends TestUtils
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

        // Login
        $login = new Login($armor);
        $s = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $s::class);

        // Page view
        $_SERVER['REQUEST_URI'] = '/members/update';
        $s = $armor->checkAuth();
        $this->assertEquals(AuthSession::class, $s::class);
        $this->assertEquals('u:test', $s->getUuid());

        // Another page view
        sleep(1);
        $_SERVER['REQUEST_URI'] = '/members/transactions';
        $s = $armor->checkAuth();
        $this->assertEquals(AuthSession::class, $s::class);
        $this->assertEquals('u:test', $s->getUuid());

        // Get history
        $user = $armor->getUuid('u:test');
        $log = $user->listLoginHistory();
        $this->assertIsArray($log);
        $this->assertCount(1, $log);

        // Get page requests
        $history_id = (int) $log[0]['id'];
        $history = Di::make(LoginHistory::class);
        $reqs = $history->listPageRequests($history_id);
        $this->assertIsArray($reqs);
        $this->assertCount(2, $reqs);
        $this->assertEquals('/members/update', $reqs[1]['uri']);

    }

}


