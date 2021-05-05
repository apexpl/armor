<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\TwoFactor;

use Apex\Armor\Armor;
use Apex\Armor\Auth\{AuthSession, SessionManager};
use Apex\Armor\Auth\TwoFactor\{TwoFactorEmail, TwoFactorEmailOTP, TwoFactorPhone};
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Enums\SessionStatus;
use Apex\Armor\Interfaces\AdapterInterface;
use Apex\Armor\AES\{EncryptAES, DecryptAES};
use Apex\Armor\Exceptions\ArmorInvalidArgumentException;
use Apex\Container\Di;
use \Nyholm\Psr7\Factory\Psr17Factory;
use \Nyholm\Psr7Server\ServerRequestCreator;
use redis;


/**
 * Two factor auth requests
 */
class TwoFactor
{


    /**
     * Process 2FA request
     */
    public function process(AuthSession $session, $is_login = false)
    {

        // Get 2FA type
        $type = $session->getStatus();
        if (!in_array($type, ['email', 'email_otp', 'phone', 'pgp'])) { 

            // Get armor and user
            $armor = Di::get(Armor::class);
            $user = $session->getUser();

            // Get policy type
            $policy = $armor->getPolicy();
            $policy_type = $policy->getTwoFactorType();

            // Get 2fa type
            $type = match(true) {
                (in_array($policy_type, ['phone_email_otp','phone_email']) && $user->isPhoneVerified() === true) => 'phone', 
                ($policy_type == 'phone_email_otp' && $user->hasEmail() === true) => 'email_otp', 
                ($policy_type == 'phone_email' && $user->hasEmail() === true) => 'email', 
                ($policy_type == 'optional') => $user->getTwoFactorType(), 
                default => $policy_type
            };

            // Update status, if needed
            if (in_array($type, ['email','email_otp','phone','pgp'])) { 
                $session->setStatus($type);
            }
        }

        // Check for none
        if ($type == 'none') { 
            return false;
        }

        // Initialize request
        if ($type == 'email') { 
            $handler = Di::make(TwoFactorEmail::class);
            $handler->init($session, $is_login);
        } elseif ($type == 'email_otp') { 
            $handler = Di::make(TwoFactorEmailOTP::class);
            $handler->init($session, $is_login);
        } elseif ($type == 'phone') { 
            $handler = Di::make(TwoFactorPhone::class);
            $handler->init($session, $is_login);
        } else { 
            throw new ArmorInvalidArgumentException("Unable to process two factor request, as invalid type specified, $type");
        }

        // Handle session status
        $adapter = Di::get(AdapterInterface::class);
        $adapter->handleSessionStatus($session, $session->getStatus());

        // Return
        return true;
    }

    /**
     * Create 2fa request
     */
    protected function createRequest(AuthSession $session, string $redis_key, bool $is_login = false):void
    {

        // Initialize
            $factory = new Psr17Factory();
            $creator = new ServerRequestCreator($factory, $factory, $factory, $factory);

        // Start request
        $request = [
            'session_id' => $session->getId(), 
            'uuid' => $session->getUuid(), 
            'is_login' => $is_login, 
            'server_request' => $creator->fromGlobals()
        ];

        // Get encrypt password, and set cookie
        $password = RandomString::get(32);
        Cookie::set('2fa', $password);

        // Serialize and encrypt request
        $aes = Di::make(EncryptAES::class);
        $encrypted = $aes->toPassword(serialize($request), $password);

        // Get policy
        $armor = Di::get(Armor::class);
        $policy = $armor->getPolicy();

        // Add to redis
        $redis = Di::get(redis::class);
        $redis->set($redis_key, $encrypted);
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());
    }

    /**
     * Get request
     */
    protected function getRequest(string $redis_key):?array
    {

        // Initialize
        $redis = Di::get(redis::class);

        // Check redis
        if (!$data = $redis->get($redis_key)) { 
            return null;
        } elseif (!$password = Cookie::get('2fa')) { 
            return null;
        }

        // Decrypt
        $dec = Di::make(DecryptAES::class);
        if (!$data = $dec->fromPassword($data, $password)) { 
            return null;
        }
        $request = unserialize($data);

        // Get session
        $manager = Di::get(SessionManager::class);
        if (!$session = $manager->get($request['session_id'])) { 
            return null;
        }

        // Set session status, delete from redis
        $session->setStatus(SessionStatus::TWO_FACTOR_AUTHORIZED);
        Di::set(AuthSession::class, $session);
        $redis->del($redis_key);

        // Return
        return [$session, $request['server_request'], $request['is_login']];
    }

}



