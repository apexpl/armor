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
class session_encrypt_test extends TestUtils
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
        $admin = $armor->createUser('a:1', 'password12345', 'admuser', 'admin@apexpl.io', '16045551234', 'admin');

        // Login
        $login = Di::make(Login::class);
        $session = $login->withPassword('test', 'password12345');
        $this->assertEquals(AuthSession::class, $session::class);
        $db = Di::get(DbInterface::class);

        // Encrypt
        $include_admin = true;
        for ($x=1; $x <= 50; $x++) { 
            $data = RandomString::get(rand(4, 256));
            $data_id = $session->encryptData($data, $include_admin);
            $chk = $session->decryptData($data_id);
            $this->assertEquals($chk, $data);

            // Decrypt to admin, if needed
            if ($include_admin === true) { 
                $admsess = $login->withPassword('admuser', 'password12345', 'admin');
                $this->assertEquals(AuthSession::class, $admsess::class);
                $admchk = $admsess->decryptData($data_id);
                $this->assertEquals($admchk, $data);
            } else { 
                $ok = $db->getField("SELECT id FROM armor_data_index WHERE data_id = %i AND uuid = 'a:1'", $data_id);
                $this->assertNull($ok);
            }
            $include_admin = $include_admin === true ? false : true;
        }

        // Large block of text
        $text = '';
        for ($x=1; $x <= 100; $x++) { 
            $text .= RandomString::get(rand(4,256)) . "\r\n";
        }

        // Encrypt
        $data_id = $session->encryptData($text);
        $chk = $session->decryptData($data_id);
        $this->assertEquals($chk, $text);
    }

}


