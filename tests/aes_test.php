<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\Devices;
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Auth\{Login, AuthSession, AutoLogin};
use Apex\Armor\AES\{EncryptAES, DecryptAES};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class aes_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        $armor = $this->initArmor();
        $e = Di::make(EncryptAES::class);
        $d = Di::make(DecryptAES::class);

        for ($x=1; $x <= 100; $x++) { 
            $data = RandomString::get(rand(4, 1024));
            $password = RandomString::get(rand(4,24));
            $enc = $e->toPassword($data, $password);

            $chk = $d->fromPassword($enc, $password);
            $this->assertEquals($chk, $data);
        }

    }

}



