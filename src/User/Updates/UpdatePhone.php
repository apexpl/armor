<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\User\Verify\VerifyPhone;
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;



/**
 * Update phone
 */
class UpdatePhone
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private DbInterface $db, 
        private Validator $validator, 
        private UserLog $logger, 
        private ?DebuggerInterface $debugger = null
    ) {

    }

    /**
     * Update phone number
     */
    public function update(ArmorUserInterface $user, string $new_phone, bool $is_verified = false)
    {


        // Check for same phone
        $new_phone = preg_replace("/[\D\W\s]/", "", $new_phone);
        if ($new_phone == $user->getPhone()) { 
            return [UpdateStatus::FAIL, null];
        }

        // Validate phone
        $this->validator->checkPhone($new_phone, $user->getType());

        // Check if verification required
        if ($is_verified === false && !$this->checkVerification($user, $new_phone)) { 
            return [UpdateStatus::PENDING_OTP, null];
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Update database
        $this->db->update('armor_users', [
            'phone' => $new_phone, 
            'phone_verified' => ($is_verified === true ? 1 : 0), 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ], "uuid = %s", $user->getUuid());

        // Add user log
        $this->logger->add($user, 'change_phone', $user->getPhone(), $new_phone);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'phone', $new_phone);

        // Commit, and return
        $this->db->commit();
        return [UpdateStatus::SUCCESS, $updated_at];
    }

    /**
     * Check verification
     */
    private function checkVerification(ArmorUser $user, string $phone):bool
    {

        // Get policy
        $policy = $this->armor->getPolicy();
        $verify_phone = $policy->getVerifyPhone();

        // Process verification
        if (in_array($verify_phone, ['require', 'optional']) && $phone != '') { 
            $is_update = $verify_phone == 'require' ? true : false;
            $verifier = Di::make(VerifyPhone::class);
            $verifier->init($user, $is_update, $phone);
        }

        // Return
        return $verify_phone == 'require' ? false : true;
    }

}


