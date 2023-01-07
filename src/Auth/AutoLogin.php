<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth;

use Apex\Armor\Armor;
use Apex\Armor\Auth\{SessionManager, AuthSession};
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Container\Di;

/**
 * Auto login
 */
class AutoLogin
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private ?DebuggerInterface $debugger = null
    ) {

    }

    /**
     * Login uuid
     */
    public function loginUuid(string $uuid):?AuthSession
    {

        // Get user
        $user = $this->armor->getUuid($uuid);

        // Create session
        $manager = Di::make(SessionManager::class);
        $session = $manager->create($user, 'ok', '', true, true);

        // Return
        return $session;
    }

    /**
     * Login username
     */
    public function loginUsername(string $username, string $type = 'user'):?AuthSession
    {

        // Get user
        $user = $this->armor->getUser($username, $type);

        // Create session
        $manager = Di::make(SessionManager::class);
        $session = $manager->create($user, 'ok', '', true, true);

        // Return
        return $session;
    }

}

