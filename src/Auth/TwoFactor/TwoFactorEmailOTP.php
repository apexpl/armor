<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\TwoFactor;

use Apex\Armor\Armor;
use Apex\Armor\Auth\AuthSession;
use Apex\Armor\Auth\TwoFactor\TwoFactor;
use Apex\Armor\Enums\EmailMessageType;
use Apex\Armor\Auth\Codes\NumberCode;
use Apex\Armor\Auth\Operations\Cookie;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Container\Di;
use redis;


/**
 * Two factor - email OTP
 */
class TwoFactorEmailOTP extends TwoFactor
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 

    }

    /**
     * Initialize
     */
    public function init(AuthSession $session, bool $is_login = false):void
    {

        // Generate hash
        list($code, $redis_key) = NumberCode::get($session->getUser()->getEmail());

        // Create session
        $this->createRequest($session, $redis_key, $is_login);

        // Send email
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendEmail($session->getUser(), EmailMessageType::TWO_FACTOR_OTP, $code);
    }

    /**
     * Verify
     */
    public function verify(ArmorUserInterface $user, string $code):bool
    {

        // Get request
        $redis_key = 'armor:2fa:' . hash('sha512', $user->getEmail() . ':' . $code);
        if (!list($session, $server_request, $is_login) = $this->getRequest($redis_key)) { 
            return false;
        }

        // Handle request
        $adapter = Di::get(AdapterInterface::class);
        $adapter->handleTwoFactorAuthorized($session, $server_request, $is_login);

        // Return
        return true;
    }

}


