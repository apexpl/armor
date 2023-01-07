<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Verify;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Operations\{RandomString, Cookie, Password};
use Apex\Armor\Auth\Codes\StringCode;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Armor\Exceptions\ArmorUuidNotExistsException;
use Apex\Armor\Enums\EmailMessageType;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use redis;


/**
 * Request initial password, only if 'require_password' of ArmorPolicy is set to RequirePassword::REQUIRE_AFTER_REGISTER
 */
class InitialPassword
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
        $this->debugger?->add(2, "Initializing request initial password for uuid " . $user->getUuid());

        // Set request
        $request = json_encode([
            'uuid' => $user->getUuid(), 
            'is_update' => 0, 
            'is_initial_password' => 1, 
            'is_reset_password' => 0, 
            'email' => $user->getEmail(), 
            'new_email' => ''
        ]);

        // Add to redis
        $redis->set($redis_key, $request);
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());

        // Send verification e-mail
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendEmail($user, EmailMessageType::DEFINE_INITIAL_PASSWORD, $string);

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
        Cookie::set('init_pass', $string);

        // Call adapter
        $adapter = Di::get(AdapterInterface::class);
        $adapter->requestInitialPassword($user);

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
        if (!$hash = Cookie::get('init_pass')) { 
            return null;
        }

        // Check for redis key
        $redis_key = 'armor:verify:' . hash('sha512', $hash);
        if (!$uuid = $redis->get($redis_key)) {
            return null;
        } elseif (!$user = $db->getIdObject(ArmorUser::class, 'armor_users', $uuid)) { 
            return null;
        } elseif ($user->getPassword() != '') { 
            return null;
        }

        // Validate password
        $validator = Di::make(Validator::class);
        $validator->checkPassword($new_password);

        // Debug
        $this->debugger?->add(2, "Defining initial password request for uuid $request[uuid]");

        // Delete from redis, and return
        $redis->del($redis_key);
        Cookie::set('init_pass', '');

        // Update database
        $db->update('armor_users', [
            'password' => Password::hash($new_password)
        ], 'uuid = %s', $user->getUuid());

        // Add log
        $logger = Di::make(UserLog::class);
        $logger->add($user, 'defined_password');

        return $user->getUuid();
    }

}


