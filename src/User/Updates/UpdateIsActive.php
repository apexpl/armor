<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Container\Di;


/**
 * Update is active
 */
class UpdateIsActive
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
     * Update is active
     */
    public function update(ArmorUserInterface $user, bool $is_active)
    {

        // Check if statis is already set
        if ($is_active === false && $user->isActive() === false) { 
            return [UpdateStatus::FAIL, null];
        } elseif ($is_active === true && $user->isActive() === true && $user->isPending() === false) { 
            return [UpdateStatus::FAIL, null];
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Set updates
        $updates = [
            'is_active' => ($is_active === true ? 1 : 0), 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ];
        if ($is_active === true) { 
            $updates['is_pending'] = 0;
        }

        // Update database
        $this->db->update('armor_users', $updates, "uuid = %s", $user->getUuid());

        // Add user log
        $action = $is_active === true ? 'activated' : 'deactivated';
        $this->logger->add($user, $action);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'is_active', $is_active);

        // Commit and return
        $this->db->commit();
        return [UpdateStatus::SUCCESS, $updated_at];
    }

}



