<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles, RegistrationInfo};
use Apex\Armor\User\Updates\{UpdateUsername, UpdatePassword, UpdateEmail, UpdatePhone};
use Apex\Armor\User\Extra\PendingPasswords;
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\AES\{KeyManager, EncryptAES, DecryptAES};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Registration info test
 */
class reginfo_test extends TestUtils
{

    /**
     * Setup
     */
    public function setUp():void
    {

        // Get policy
        $this->policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'unique', 
            require_phone: 'require', 
            verify_email: 'none', 
            verify_phone: 'none'
        );

    }

    /**
     * Test create
     */
    public function test_create()
    {

        // Initialize
        $armor = $this->initArmor($this->policy);
        $profiles = $armor;
        $profiles->purge();

        // Create reg info
        $reginfo = new RegistrationInfo(
            armor: $armor, 
            reg_country: 'US', 
            reg_province_iso_code: 'TX', 
            reg_province_name: 'Texas', 
            reg_city: 'Houston'
        );

        // Create profile
        $user = $armor->createUser('u:testreg', 'password12345', 'test', 'test@apexpl.io', '14165551234', 'user', $reginfo);
        $this->assertEquals($user::class, ArmorUser::class);
        $this->verifyUser($armor, 'u:testreg', 'password12345', 'test', 'test@apexpl.io', '14165551234');

        // Verify
        $vuser = $profiles->getUuid('u:testreg');
        $this->assertEquals('US', $vuser->getRegCountry());
        $this->assertEquals('Texas', $vuser->getRegProvinceName());
        $this->assertEquals('TX', $vuser->getRegProvinceISOCode());
        $this->assertEquals('Houston', $vuser->getRegCity());
    }

    /**
     * Test IP address
     */
    public function test_ip()
    {

        // Init
        $armor = $this->initArmor();
        $profiles = $armor;
        $profiles->purge();

        // Create reginfo
        $reginfo = new RegistrationInfo(
            armor: $armor, 
            reg_ip: '13.57.226.247'
        );

        // Create user
        $user = $armor->createUser('u:test', 'password12345', 'test', 'test@apexpl.io', '14165551234', 'user', $reginfo);

        // Verify
        $vuser = $profiles->getUuid('u:test');
        $this->assertEquals('US', $vuser->getRegCountry());
        $this->assertEquals('CA', $vuser->getRegProvinceISOCode());
        $this->assertEquals('California', $vuser->getRegProvinceName());
        $this->assertEquals('San Jose', $vuser->getRegCity());
    }

}


