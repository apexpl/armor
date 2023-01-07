<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Verify;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Updates\UpdatePhone;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Codes\NumberCode;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Armor\Enums\PhoneMessageType;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use redis;


/**
 * Verify phone number
 */
class VerifyPhone
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
    public function init(ArmorUserInterface $user, bool $is_update = false, string $new_phone = ''):string
    {

        // Get items from container
        $redis = Di::get(redis::class);
        $policy = $this->armor->getPolicy();

        // Generate hash
        $hash_phone = $is_update === false && $new_phone != '' ? $new_phone : $user->getPhone();
        list($code, $redis_key) = NumberCode::get($hash_phone, 'verify');

        // Generate verify hash
        while (true) { 

            // Generate hash
            $code = (string) random_int(100000, 999999);
            $redis_key = 'armor:verify:' . hash('sha512', $hash_phone . ':' . $code);

            // Check if exists
            if (!$redis->exists($redis_key)) { 
                break;
            }
        }

        // Debugger
        $this->debugger?->add(2, "Initializing phone verification for uuid " . $user->getUuid());

        // Set request
        $request = json_encode([
            'uuid' => $user->getUuid(), 
            'is_update' => $is_update === true ? 1 : 0, 
            'phone' => $user->getPhone(), 
            'new_phone' => $new_phone
        ]);

        // Add to redis
        $redis->set($redis_key, $request);
        $redis->expire($redis_key, $policy->getExpireVerifyPhoneSecs());

        // Send verification e-mail
        $adapter = Di::get(AdapterInterface::class);
        $adapter->sendSMS($user, PhoneMessageType::VERIFY, $code, $new_phone);

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
        $redis_key = 'armor:verify:' . hash('sha512', $user->getPhone() . ':' . $code);
        if (!$data = $redis->get($redis_key)) {
            return null;
        }
        $request = json_decode($data, true);

        // Debug
        $this->debugger?->add(2, "Verifying phone verification for uuid $request[uuid]");

        // Delete from redis
        $redis->del($redis_key);

        // Update e-mail address
        if ($request['is_update'] == 1) { 
            $updater = Di::make(UpdatePhone::class);
            $updater->update($user, $request['new_phone'], true);
            return $request['uuid'];
        }

        // Update database
        $db->update('armor_users', [
            'phone_verified' => 1
        ], 'uuid = %s', $request['uuid']);

        // Add log
        $logger = Di::make(UserLog::class);
        $logger->add($user, 'verified_phone', $user->getPhone());

        return $request['uuid'];
    }

}


