<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth;

use Apex\Armor\Armor;
use Apex\Armor\Auth\AuthSession;
use Apex\Armor\Enums\SessionStatus;
use Apex\Armor\Auth\Operations\{RandomString, IpAddress, UserAgent, Cookie};
use Apex\Armor\User\Extra\LoginHistory;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Container\Di;
use redis;


/**
 * Session manager to create, delete, and lookup sessions
 */
class SessionManager
{

    /**
     * Construct
     */
    public function __construct(
        private Armor $armor
    ) { 
        $this->policy = $armor->getPolicy();
        $this->redis = Di::get(redis::class);
    }

    /**
     * Create session
     */
    public function create(
        ArmorUserInterface $user, 
        string $status = 'ok', 
        string $password = '', 
        bool $set_cookie = true, 
        bool $is_auto_login = false
    ):AuthSession { 

        // Generate session ID
        do { 
            $session_id = RandomString::get(64);
            $exists = $this->redis->exists('armor:s:' . hash('sha512', $session_id));
        } while ($exists > 0);

        // Add login to history
        $history = Di::make(LoginHistory::class);
        $history_id = $history->addLogin($user->getUuid(), true, $is_auto_login);

        // Set vars
        $vars = [
            'status' => $status,
            'history_id' => $history_id, 
            'expires_at' => (time() + $this->policy->getExpireSessionInactivitySecs()), 
            'uuid' => $user->getUuid(), 
            'last_seen' => time(), 
            'ip_address' => IpAddress::get(), 
            'user_agent' => UserAgent::get(), 
            'enchash' => hash('sha256', $password)
        ];

        // Save to redis
        $redis_key = 'armor:s:' . hash('sha512', $session_id);
        $this->redis->hmset($redis_key, $vars);
        $this->setExpiration($redis_key, true);

        // Create auth session
        $vars['id'] = $session_id;
        $vars['user'] = $user;
        $session = Di::makeset(AuthSession::class, $vars);

        // Set cookie, if needed
        if ($set_cookie === true) { 
            $name = 'sid_' . $user->getType();
            Cookie::set($name, $session_id);
        }

        // Return
        return $session;
    }

    /**
     * Lookup session
     */
    public function lookup(string $type = 'user'):?AuthSession
    {

        // Check for cookie
        if (!$session_id = Cookie::get('sid_' . $type)) { 
            return null;
        } elseif (!$session = $this->get($session_id)) {
            return null;
        }

        // Check for expired
        if (time() > $session->getExpiresAt()) { 
            $session->setStatus(SessionStatus::EXPIRED);
            $adapter = Di::get(AdapterInterface::class);
            $adapter->handleSessionStatus($session, SessionStatus::EXPIRED);
            return null;
        }

        // Add page request, if needed
        if ($session->getHistoryId() > 0) { 
            $history = Di::make(LoginHistory::class);
            $history->addPageRequest($session->getHistoryId());
        }
    Di::set(AuthSession::class, $session);

        // Return
        return $session;
    }

    /**
     * Get session
     */
    public function get(string $session_id, bool $check_client = true):?AuthSession
    {

        // Check redis
        $redis_key = 'armor:s:' . hash('sha512', $session_id);

        if (!$vars = $this->redis->hgetall($redis_key)) { 
            return null;
        } elseif ($check_client === true && ($vars['ip_address'] != IpAddress::get() || $vars['user_agent'] != UserAgent::get())) { 
            return null;
        }
        $vars['last_seen'] = time();

        // Update redis
        $this->redis->hset($redis_key, 'last_seen', $vars['last_seen']);
        $this->setExpiration($redis_key);

        // Ready vars for injection
        $vars['id'] = $session_id;
        $vars['history_id'] = (int) $vars['history_id'];
        $vars['expires_at'] = (int) $vars['expires_at'];

        // Create auth session
        $session = Di::make(AuthSession::class, $vars);

        // Return
        return $session;
    }

    /**
     * Destroy session
     */
    public function destroy(AuthSession $session):void
    {

        // Get user type
        $type = $session->getUser()->getType();
        $session_id = $session->getId();

        // Delete session
        $this->redis->del('armor:s:' . hash('sha512', $session_id));
        Cookie::set('sid.' . $type, '');
    }

    /**
     * Update expiration
     */
    public function setExpiration(string $redis_key, bool $is_new_session = false):void
    {

        // Return, if locked
        if ($this->policy->getLockRedisExpiration() === true && $is_new_session === false) { 
            return;
        }

        // Set expiration
        $redis = Di::get(redis::class);
        $redis->hset($redis_key, 'expires_at', (time() + $this->policy->getExpireSessionInactivitySecs()));

        // Set expiration on redis key
        if ($this->policy->getExpireRedisSessionSecs() > 0) { 
            $expire_secs = $this->policy->getExpireRedisSessionSecs();
        } else { 
            $expire_secs = $this->policy->getExpireSessionInactivitySecs();
        }
        $redis->expire($redis_key, $expire_secs);

    }


}

