<?php
declare(strict_types = 1);

use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy, PolicyManager};
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\Auth\Operations\{Password, Cookie};
use Apex\Armor\Auth\{SessionManager, AuthSession, Login};
use Apex\Armor\Auth\TwoFactor\{TwoFactorEmail, TwoFactorEmailOTP, TwoFactorPhone};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

require_once(__DIR__ . '/files/TestUtils.php');

/**
 * ArmorPolicy tests
 */
class armorpolicy_test extends TestUtils
{

    /**
     * Brute force policy
     */
    public function test_bruteforcepolicy()
    {

        // Get brute force
        $brute = new BruteForcePolicy();
        $brute->addRule(5, 30, 3600);
        $brute->addRule(100, 6000, 0);

        // Get policy
        $policy = new ArmorPolicy(
            username_column: 'email', 
            verify_email: 'optional', 
            brute_force_policy: $brute
        );

        // Init armor
        $armor = $this->initArmor($policy);
        $p = $armor->getPolicy();
        $this->assertEquals((string) ArmorPolicy::class, (string) $p::class);
        $this->assertEquals('email', $p->getUsernameColumn());
        $this->assertEquals('optional', $p->getVerifyEmail());

        // Check brute force policy
        $b = $p->getBruteForcePolicy();
        $this->assertEquals(BruteForcePolicy::class, $b::class);
        $rules = $b->getRules();
        $this->assertIsArray($rules);
        $this->assertCount(2, $rules);

        // Check first rule
        $r = $rules[0];
        $this->assertEquals(5, $r['attempts']);
        $this->assertEquals(30, $r['seconds']);
        $this->assertEquals(3600, $r['suspend_seconds']);

    }

    /**
     * Default brute force
     */
    public function test_bruteforce_default()
    {

        // Init Armor
        $armor = $this->initArmor();
        $policy = $armor->getPolicy();
        $brute = $policy->getBruteForcePolicy();
        $this->assertEquals(BruteForcePolicy::class, $brute::class);

        // Get rules
        $rules = $brute->getRules();
        $this->assertIsArray($rules);
        $this->assertCount(3, $rules);

        // Check second rule
        $r = $rules[1];
        $this->assertEquals(20, $r['attempts']);
        $this->assertEquals(3600, $r['seconds']);
        $this->assertEquals(21600, $r['suspend_seconds']);
    }

    /**
     * Save policy
     */
    public function test_save_policy()
    {

        // Init
        $armor = $this->initArmor();
        $manager = new PolicyManager($armor);
        $manager->purgePolicies();

        // Define policy
        $policy = new ArmorPolicy(
            username_column: 'phone', 
            default_two_factor_type: 'phone', 
            default_two_factor_frequency: 'always'
        );

        // Save
        $manager->savePolicy('test_policy', $policy);

        // Load and check
        $p = $manager->loadPolicy('test_policy');
        $this->assertEquals('phone', $p->getUsernameColumn());
        $this->assertEquals('phone', $p->getDefaultTwoFactorType());
        $this->assertEquals('always', $p->getDefaultTwoFactorFrequency());
    }

    /**
     * Instantiate with policy
     */
    public function test_instantiate_with_policy()
    {

        // Init
        $armor = $this->initArmor(null, 'test_policy');
        $p = $armor->getPolicy();
        $this->assertEquals('phone', $p->getUsernameColumn());
        $this->assertEquals('phone', $p->getDefaultTwoFactorType());
        $this->assertEquals('always', $p->getDefaultTwoFactorFrequency());

        // Update policy
        $manager = new PolicyManager($armor);
        $p->setRequirePassword('after_register');
        $manager->savePolicy('test_policy', $p);
        $manager->savePolicy('test2', $p);
    }

    /**
     * Load policy via method
     */
    public function test_load_policy_method()
    {

        // Init
        $armor = $this->initArmor();
        $armor->loadPolicy('test_policy');

        // Check
        $p = $armor->getPolicy();
        $this->assertEquals('phone', $p->getUsernameColumn());
        $this->assertEquals('phone', $p->getDefaultTwoFactorType());
        $this->assertEquals('always', $p->getDefaultTwoFactorFrequency());
        $this->assertEquals('after_register', $p->getRequirePassword());

        // List
        $manager = new PolicyManager($armor);
        $list = $manager->listPolicies();
        $this->assertCount(2, $list);
        $p = $list['test_policy'];
        $this->assertEquals(ArmorPolicy::class, $p::class); 
    }

    /**
     * Delete and purge polices
     */
    public function test_delete_policy()
    {

        // Init
        $armor = $this->initArmor();
        $manager = new PolicyManager($armor);

        // Delete
        $ok = $manager->deletePolicy('test2');
        $this->assertTrue($ok);
        $list = $manager->listPolicies();
        $this->assertCount(1, $list);

        // Purge
        $manager->purgePolicies();
        $list = $manager->listPolicies();
        $this->assertCount(0, $list);
    }

}


