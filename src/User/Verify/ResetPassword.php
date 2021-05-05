<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Verify;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\Auth\Codes\StringCode;
use Apex\Armor\Enums\EmailMessageType;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Container\Di;
use redis;


/**
 * Reset password
 */
class ResetPassword
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private ?DebuggerInterface $debugger = null
    ) { 
        $this->db = Di::get(DbInterface::class);
    }

    /**
     * Reset by e-mail
     */
    public function byEmail(string $email, string $type = 'user'):?int
    {

        // Get users
        $count = 0;
        $rows = $this->db->query("SELECT * FROM armor_users WHERE email = %s AND type = %s AND is_deleted = false", $email, $type);
        foreach ($rows as $row) { 

            // Load user, and init
            $user = $this->armor->getUuid($row['uuid']);
            $this->init($user);
            $count++;
        }

        // Return
        return $count == 0 ? null : $count;
    }

    /**
     * By username
     */
    public function byUsername(string $username, string $type = 'user'):?int
    {

        // Get user
        if (!$user = $this->armor->getUser($username, $type, false, false)) { 
            return null;
        }

        // Init user, and return
        $this->init($user);
        return 1;
    }

    /**
     * Initialize
     */
    public function init(ArmorUserInterface $user):string
    {

        // Get items from container
        $redis = Di::get(redis::class);
        $policy = $this->armor->getPolicy();

        // Get verify hash
        list($string, $redis_key) = StringCode::get('verify');

        // Debugger
        $this->debugger?->add(2, "Initializing reset password for uuid " . $user->getUuid());

        // Set request
        $request = json_encode([
            'uuid' => $user->getUuid(), 
            'is_update' => 0, 
            'is_initial_password' => 0, 
            'is_reset_password' => 1, 
            'email' => $user->getEmail(), 
            'new_email' => ''
        ]);

        // Add to redis
        $redis->set($redis_key, $request);
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());

        // Send verification e-mail
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendEmail($user, EmailMessageType::RESET_PASSWORD, $string);

        // Return
        return $string;
    }

    /**
     * Request password
     */
    public function requestPassword(ArmorUserInterface $user):string
    {

        // Get items from container
        $redis = Di::get(redis::class);
        $policy = $this->armor->getPolicy();

        // Generate verify hash
        while (true) { 

            // Generate hash
            $string = RandomString::get(48);
            $redis_key = 'armor:verify:' . hash('sha512', $string);

            // Check if exists
            if (!$redis->exists($redis_key)) { 
                break;
            }
        }

        // Add to redis
        $redis->set($redis_key, $user->getUuid());
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());

        // Set cookie
        Cookie::set('reset_pass', $string);

        // Call adapter
        $adapter = Di::get(AdapterInterface::class);
        $adapter->requestResetPassword($user);

        // return
        return $string;
    }

    /**
     * Finish
     */
    public function finish(string $new_password):?string
    {

        // initialize
        $redis = Di::get(redis::class);
        $db = Di::get(DbInterface::class);

        // Get cookie
        if (!$hash = Cookie::get('reset_pass')) { 
            return null;
        }

        // Check for redis key
        $redis_key = 'armor:verify:' . hash('sha512', $hash);
        if (!$uuid = $redis->get($redis_key)) {
            return null;
        } elseif (!$user = $db->getIdObject(ArmorUser::class, 'armor_users', $uuid)) { 
            return null;
        }

        // Validate password
        $validator = Di::make(Validator::class);
        $validator->checkPassword($new_password);

        // Debug
        $this->debugger?->add(2, "Resetting password request for uuid $request[uuid]");

        // Delete from redis, and return
        $redis->del($redis_key);
        Cookie::set('reset_pass', '');

        // Update database
        $user->updatePassword($new_password);

        // Add log
        $logger = Di::make(UserLog::class);
        $logger->add($user, 'change_password');

        return $user->getUuid();
    }

}


