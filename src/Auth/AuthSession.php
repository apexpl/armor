<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth;

use Apex\Armor\Armor;
use Apex\Armor\Auth\TwoFactor\TwoFactor;
use Apex\Armor\AES\{EncryptAES, DecryptAES};
use Apex\Armor\Enums\SessionStatus;
use Apex\Armor\Interfaces\ArmorUserInterface;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use redis;


/**
 * Auth session
 */
class AuthSession
{

    // Properties
    private ?ArmorUserInterface $user = null;
    private string $redis_key = '';


    /**
     * Construct
     */
    public function __construct(
        private string $id,
        private string $status,
        private string $uuid, 
        private int $history_id, 
        private int $expires_at, 
        private string $ip_address, 
        private string $user_agent, 
        private string $enchash 
    ) { 

    }

    /**
     * Get id
     */
    public function getId():string
    {
        return $this->id;
    }

    /**
     * Get uuid
     */
    public function getUuid():string
    {
        return $this->uuid;
    }

    /**
     * Get history id
     */
    public function getHistoryId():int
    {
        return $this->history_id;
    }

    /**
     * Get expires at
     */
    public function getExpiresAt():int
    {
        return $this->expires_at;
    }

    /**
     * Get status
     */
    public function getStatus():string
    {
        return $this->status;
    }

    /**
     * Get ip address
     */
    public function getIpAddress():string
    {
        return $this->ip_address;
    }

    /**
     * Get user agent
     */
    public function getUserAgent():string
    {
        return $this->user_agent;
    }

    /**
     * Get enchash
     */
    public function getEncHash():?string
    {
        return $this->enchash;
    }

    /**
    * Get ArmorUser
     */
    public function getUser():ArmorUserInterface
    {

        // Return, if loaded
        if ($this->user !== null) { 
            return $this->user;
        }

        // Load user
        $armor = Di::get(Armor::class);
        $this->user = $armor->getUuid($this->uuid);

        // Return
        return $this->user;
    }

    /**
     * Set status
     */
    public function setStatus(string $status):void
    {
        $this->status = $status;
        if ($this->redis_key == '') { 
            $this->redis_key = 'armor:s:' . hash('sha512', $this->id);
        }

        // Update redis
        $redis = Di::get(redis::class);
        $redis->hset($this->redis_key, 'status', $status);
    }

    /**
     * Require 2FA
     */
    public function requireTwoFactor():bool
    {

        // Check current status
        if ($this->status == SessionStatus::TWO_FACTOR_AUTHORIZED) { 
            $this->setStatus(SessionStatus::AUTH_OK);
            return true;
        }

        // Process request
        $two_factor = Di::make(TwoFactor::class);
        if (!$two_factor->process($this)) { 
            return true;
        }

        // Return
        return false;
    }

    /**
     Encrypt data
     */
    public function encryptData(string $data, bool $include_admin = true):int
    {

        // Encrypt
        $aes = Di::make(EncryptAES::class);
        $data_id = $aes->toUuids($data, [$this->uuid], $include_admin);

        // Return
        return $data_id;
    }

    /**
     * Decrypt data
     */
    public function decryptData(int $data_id):?string
    {

        // Decrypt
        $aes = Di::make(DecryptAES::class);
        $data = $aes->fromUuid($data_id, $this->uuid, $this->enchash, false);

        // Return
        return $data;
    }

    /**
     * Logout
     */
    public function logout():void
    {
        $manager = Di::make(SessionManager::class);
        $manager->destroy($this);
    }

    /**
     * Set attribute
     */
    public function setAttribute(string $name, string $value):void
    {
        $redis = Di::get(redis::class);
        if ($this->redis_key == '') { 
            $this->redis_key = 'armor:s:' . hash('sha512', $this->id);
        }
        $redis->hset($this->redis_key, 'attr.' . $name, $value);
    }

    /**
     * Get attribute
     */
    public function getAttribute(string $name):?string
    {
        $redis = Di::get(redis::class);
        if ($this->redis_key == '') { 
            $this->redis_key = 'armor:s:' . hash('sha512', $this->id);
        }

        // Get and return 
        $value = $redis->hget($this->redis_key, 'attr.' . $name) ?? null;
        return $value === false ? null : $value;
    }

    /**
     * Get all attributes
     */
    public function getAttributes():array
    {
        $redis = Di::get(redis::class);
        if ($this->redis_key == '') { 
            $this->redis_key = 'armor:s:' . hash('sha512', $this->id);
        }

        // Get attributes
        $attr = [];
        $pairs = $redis->hgetall($this->redis_key);
        foreach ($pairs as $key => $value) { 
            if (!preg_match("/^attr\.(.+)$/", $key, $match)) { 
                continue;
            }
            $attr[$match[1]] = $value;
        }

        // return
        return $attr;
    }

    /**
     * Delete attribute
     */
    public function delAttribute(string $name):bool
    {
        $redis = Di::get(redis::class);
        if ($this->redis_key == '') { 
            $this->redis_key = 'armor:s:' . hash('sha512', $this->id);
        }

        // Delete and return
        $ok = $redis->hdel($this->redis_key, 'attr.' . $name);
        return $ok == 1 ? true : false;
    }

}


