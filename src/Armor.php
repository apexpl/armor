<?php
declare(strict_types = 1);

namespace Apex\Armor;


use Apex\Armor\User\Profiles;
use Apex\Armor\Policy\{ArmorPolicy, PolicyManager};
use Apex\Armor\Auth\{SessionManager, AuthSession};
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Container\Di;
use Apex\Armor\Interfaces\AdapterInterface;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Mercury\Email\Emailer;
use Apex\Mercury\SMS\NexmoConfig;
use redis;


/**
 * Central boot loader class for Armor.
 */
class Armor extends Profiles
{


    /**
     * Constructor
     */
    public function __construct(
        private ?string $container_file = '', 
        ?redis $redis = null,
        ?DbInterface $db = null, 
        ?ArmorPolicy $policy = null, 
        string $policy_name = ''
    ) {

        // Load Armor
        if ($container_file !== null) { 
            $this->load($container_file, $db, $redis);
        }

        // Set policy
        if ($policy !== null) { 
            $this->policy = $policy;
        } elseif ($policy_name != '') { 
            $manager = Di::make(PolicyManager::class);
            $this->policy = $manager->loadPolicy($policy_name);
        }

    }

    /**
     * Get policy
     */
    public function getPolicy():ArmorPolicy
    {
        return $this->policy;
    }

    /**
     * Set policy
     */
    public function setPolicy(ArmorPolicy $policy):void
    {
        $this->policy = $policy;
    }

    /**
     * Load policy
     */
    public function loadPolicy(string $policy_name):void
    {
        $manager = Di::make(PolicyManager::class);
        $this->policy = $manager->loadPolicy($policy_name);
    }

    /**
     * Check for auth session
     */
    public function checkAuth(string $type = 'user'):?AuthSession
    {
        $manager = Di::make(SessionManager::class);
        return $manager->lookup($type);
    }

    /**
     * Get cookie username
     */
    public function getCookieUsername():string
    {
        return Cookie::get('username') ?? '';
    }

    /**
     * Load Armor
     */
    private function load(string $container_file, ?DbInterface $db = null, ?redis $redis = null):void
    {

        // Check for blank file
        if ($container_file == '') { 
            $container_file = __DIR__ . '/../config/container.php';
        }

        // Build container
        Di::buildContainer($container_file);

        // Set armor class
        Di::set(__CLASS__, $this);
        $this->armor = $this;

        // Ensure we have an ArmorPolicy available
        $this->setupPolicy();

        // Set db / redis instances in container if not null
        if ($db !== null) { 
            Di::set(DbInterface::class, $db);
        }
        if ($redis !== null) { 
            Di::set(redis::class, $redis);
        }

        // Mark necessary items as services
        Di::markItemAsService(DbInterface::class);
        Di::markItemAsService(AdapterInterface::class);
        Di::markItemAsService(redis::class);
        Di::markItemAsService(DebuggerInterface::class);
        Di::markItemAsService(Emailer::class);
        Di::markItemAsService(NexmoConfig::class);

    }

    /**
     * Ensure ArmorConfig is available
     */
    private function setupPolicy()
    {

        // Check if instantiated with policy
        if (isset($this->policy) && $this->policy !== null) { 
            Di::set(ArmorPolicy::class, $this->policy);
            return;
        }

        // Check container
        if (Di::has(ArmorPolicy::class)) { 
            $this->policy = Di::get(ArmorPolicy::class);
            return;
        }

        // Build new config
        $options = Di::get('armor.default_policy') ?? [];
        $this->policy = Di::makeset(ArmorPolicy::class, $options);
    }

}


