<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\{Password, Cookie};
use Apex\Armor\Auth\{SessionManager, AuthSession};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * Create with auth session
 */
class create_with_auth_session_test extends TestUtils
{

    /**
     * Test create
     */
    public function test_create()
    {

        // Get policy
        $policy = new ArmorPolicy(
            require_password: 'require', 
            require_email: 'require', 
            require_phone: 'optional'
        );

        // Initialize
        $armor = $this->initArmor($policy);
        $armor->purge();

        // Create profile
        $user = $armor->createUser('u:test', 'password12345', 'test', 'mdizak@apexpl.io', '14165551234', 'user', null, true);
        $this->assertEquals($user::class, ArmorUser::class);

        // Check cookie
        $session_id = Cookie::get('sid_user');
        $this->assertNotNull($session_id);
        $this->assertIsString($session_id);

        // Load session
        $manager = Di::make(SessionManager::class);
        $session = $manager->lookup();
        $this->assertEquals(AuthSession::class, $session::class);
        $this->assertEquals('u:test', $session->getUuid());
    }

}




