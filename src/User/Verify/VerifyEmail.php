<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Verify;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Updates\UpdateEmail;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\User\Verify\InitialPassword;
use Apex\Armor\Auth\Codes\StringCode;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Armor\Exceptions\ArmorUuidNotExistsException;
use Apex\Armor\Enums\EmailMessageType;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use redis;


/**
 * Verify e-mail address.
 */
class VerifyEmail
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
    public function init(ArmorUserInterface $user, bool $is_update = false, string $new_email = ''):string
    {

        // Get items from container
        $redis = Di::get(redis::class);
        $policy = $this->armor->getPolicy();

        // Get verify hash
        list($string, $redis_key) = StringCode::get('verify');

        // Debugger
        $this->debugger?->add(2, "Initializing e-mail verification for uuid " . $user->getUuid());

        // Set request
        $request = json_encode([
            'uuid' => $user->getUuid(), 
            'is_update' => $is_update === true ? 1 : 0, 
            'is_initial_password' => 0, 
            'is_reset_password' => 0, 
            'email' => $user->getEmail(), 
            'new_email' => $new_email
        ]);

        // Add to redis
        $redis->set($redis_key, $request);
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());

        // Send verification e-mail
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendEmail($user, EmailMessageType::VERIFY, $string, $new_email);

        // Return
        return $string;
    }

    /**
     * Verify
     */
    public function verify(string $hash):?string
    {

        // initialize
        $redis = Di::get(redis::class);
        $db = Di::get(DbInterface::class);

        // Check for redis key
        $redis_key = 'armor:verify:' . hash('sha512', $hash);
        if (!$data = $redis->get($redis_key)) {
            return null;
        }
        $request = json_decode($data, true);

        // Debug
        $this->debugger?->add(2, "Verifying e-mail verification for uuid $request[uuid]");

        // Load user
        if (!$user = $db->getIdObject(ArmorUser::class, 'armor_users', $request['uuid'])) { 
            throw new ArmorUuidNotExistsException("No user exists with uuid $request[uuid]");
        }

        // Delete from redis
        $redis->del($redis_key);

        // Define initial password
        if (isset($request['is_initial_password']) && $request['is_initial_password'] == 1) { 
            $pass_ver = Di::make(InitialPassword::class);
            $hash = $pass_ver->requestPassword($user);
            return $hash;

        // Reset password
        } elseif (isset($request['is_reset_password']) && $request['is_reset_password'] == 1) { 
            $pass_ver = Di::make(ResetPassword::class);
            $hash = $pass_ver->requestPassword($user);
            return $hash;

        // Update e-mail address
        } elseif ($request['is_update'] == 1) { 
            $updater = Di::make(UpdateEmail::class);
            $updater->update($user, $request['new_email'], true);
            return $request['uuid'];
        }

        // Update database
        $db->update('armor_users', [
            'email_verified' => 1
        ], 'uuid = %s', $request['uuid']);

        // Add log
        $logger = Di::make(UserLog::class);
        $logger->add($user, 'verified_email', $user->getEmail());

        return $request['uuid'];
    }

}


