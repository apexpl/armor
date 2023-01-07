<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Armor\Exceptions\ArmorProfileValidationException;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Container\Di;


/**
 * Update two factor
 */
class UpdateTwoFactor
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private DbInterface $db, 
        private UserLog $logger, 
        private ?DebuggerInterface $debugger = null
    ) {

    }

    /**
     * Update two factor
     */
    public function update(ArmorUserInterface $user, string $type = '', string $frequency = '')
    {

        // Initialize
        $policy = $this->armor->getPolicy();

        // Check
        if ($type == '' && $frequency == '') { 
            return;
        } elseif ($type == $user->getTwoFactorType() && $frequency == $user->getTwoFactorFrequency()) { 
            return;
        } elseif ($type != '' && $policy->getTwoFactorType() != 'optional') { 
            throw new ArmorProfileValidationException("Unable to update two factor type, as the ArmorPolicy::TwoFactorType is not set to 'optional'");
        } elseif ($frequency != '' && $policy->getTwoFactorFrequency() != 'optional') { 
            throw new ArmorProfileValidationException("Unable to update two factor frequency, as the ArmorPolicy::TwoFactorFrequency is not set to 'optional'");
        } elseif ($type != '' && !in_array($type, ['none','email','email_otp','phone','pgp'])) { 
            throw new ArmorProfileValidationException("Invalid two factor type specified, '$type'.  Please see the TwoFactorType enum for supported values.");
        } elseif ($frequency != '' && !in_array($frequency, ['none','always','new_device'])) { 
            throw new ArmorProfileValidationException("Invalid two factor type specified, '$type'.  Please see the TwoFactorType enum for supported values.");
        }

        // Set updates array
        $updates = [];
        if ($type != '') { 
            $updates['two_factor_type'] = $type;
        }
        if ($frequency != '') { 
            $updates['two_factor_frequency'] = $frequency;
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();
        $updates['updated_at'] = date('Y-m-d H:i:s', $updated_at->getTimestamp());

        // Update database
        $this->db->update('armor_users', $updates, "uuid = %s", $user->getUuid());

        // Add log
        $old_item = $user->getTwoFactorType() . ',' . $user->getTwoFactorFrequency();
        $this->logger->add($user, 'change_two_factor', $old_item, $type . ',' . $frequency);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'two_factor_type', $type);
        $adapter->onUpdate($user, 'two_factor_frequency', $frequency);

        // Commit
        $this->db->commit();
    }

}



