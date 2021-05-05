<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\User\Verify\{VerifyEmail, VerifyEmailOTP};
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;


/**
 * Update e-mail address
 */
class UpdateEmail
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
        $this->policy = $armor->getPolicy();
    }

    /**
     * Update e-mail
     */
    public function update(ArmorUserInterface $user, string $new_email, bool $is_verified = false)
    {

        // Check for same e-mail
        if ($user->getEmail() == $new_email) { 
            return [UpdateStatus::FAIL, null];
        }

        // Validate e-mail
        $this->validator->checkEmail($new_email, $user->getType());

        // Check if verification required
        if ($is_verified === false) { 
            list($ver_required, $status) = $this->checkVerification($user, $new_email);
            if ($ver_required === true) { 
                return [true, $status, null];
            }
        } else { 
            $status = UpdateStatus::SUCCESS;
            $ver_required = false;
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Update database
        $this->db->update('armor_users', [
            'email' => strtolower($new_email), 
            'email_verified' => ($is_verified === true ? 1 : 0), 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ], "uuid = %s", $user->getUuid());

        // Add user log
        $this->logger->add($user, 'change_email', $user->getEmail(), $new_email);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'email', strtolower($new_email));


        // Commit, and return
        $this->db->commit();
        return [$ver_required, $status, $updated_at];
    }

    /**
     * Check verification
     */
    private function checkVerification(ArmorUserInterface $user, string $email)
    {

        // Get policy
        $policy = $this->armor->getPolicy();
        $verify_email = $policy->getVerifyEmail();
        $is_update = false;

        // Process verification
        if (in_array($verify_email, ['require', 'optional']) && $email != '') { 
        $is_update = $verify_email == 'require' ? true : false;
            $verifier = Di::make(VerifyEmail::class);
            $verifier->init($user, $is_update, $email);

        } elseif (in_array($verify_email, ['require_otp', 'optional_otp']) && $email != '') { 
            $is_update = $verify_email == 'require_otp' ? true : false;
            $verifier = Di::make(VerifyEmailOTP::class);
            $verifier->init($user, $is_update, $email);
        }

        // Get status
        $status = match($verify_email) { 
            'require' => UpdateStatus::PENDING_VERIFY,
            'optional' => UpdateStatus::PENDING_VERIFY, 
            'require_otp' => UpdateStatus::PENDING_OTP, 
            'optional_otp' => UpdateStatus::PENDING_OTP, 
        default => UpdateStatus::SUCCESS
        };

        // Return
        return [$is_update, $status];
    }

}



