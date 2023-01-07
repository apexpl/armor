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

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class session_attributes_test extends TestUtils
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
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Login
        $login = DI::make(Login::class);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('u:test', $session->getUuid());

        // Set attribute
        $session->setAttribute('city', 'Vancouver');
        $session->setAttribute('country', 'Thailand');

        // Get attribute
        $this->assertEquals('Vancouver', $session->getAttribute('city'));
        $this->assertEquals('Thailand', $session->getAttribute('country'));

        // Get all attributes
        $attr = $session->getAttributes();
        $this->assertIsArray($attr);
        $this->assertCount(2, $attr);
        $this->assertEquals('Vancouver', $attr['city']);
        $this->assertEquals('Thailand', $attr['country']);

        // Delete
        $ok = $session->delAttribute('country');
        $this->assertTrue($ok);
        $this->assertNull($session->getAttribute('country'));
        $this->assertEquals('Vancouver', $session->getAttribute('city'));
    }

}


