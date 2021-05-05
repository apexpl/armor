<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Verify;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Updates\UpdateEmail;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Codes\NumberCode;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Armor\Enums\EmailMessageType;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use redis;


/**
 * Verify phone number
 */
class VerifyEmailOTP
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

        // Generate hash
        $hash_email = $is_update === false && $new_email != '' ? $new_email : $user->getEmail();
        list($code, $redis_key) = NumberCode::get($hash_email, 'verify');

        // Debugger
        $this->debugger?->add(2, "Initializing e-mail verification for uuid " . $user->getUuid());

        // Set request
        $request = json_encode([
            'uuid' => $user->getUuid(), 
            'is_update' => $is_update === true ? 1 : 0, 
            'email' => $user->getEmail(), 
            'new_email' => $new_email
        ]);

        // Add to redis
        $redis->set($redis_key, $request);
        $redis->expire($redis_key, $policy->getExpireVerifyEmailSecs());

        // Send verification e-mail
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendEmail($user, EmailMessageType::VERIFY_OTP, $code, $new_email);

        // Return
        return $code;
    }

    /**
     * Verify
     */
    public function verify(ArmorUserInterface $user, string $code):?string
    {

        // initialize
        $redis = Di::get(redis::class);
        $db = Di::get(DbInterface::class);

        // Check for redis key
        $redis_key = 'armor:verify:' . hash('sha512', $user->getEmail() . ':' . $code);
        if (!$data = $redis->get($redis_key)) {
            return null;
        }
        $request = json_decode($data, true);

        // Debug
        $this->debugger?->add(2, "Verifying e-mail verification for uuid $request[uuid]");

        // Delete from redis
        $redis->del($redis_key);

        // Update e-mail
        if ($request['is_update'] == 1) { 
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

        // Return
        return $request['uuid'];
    }

}


