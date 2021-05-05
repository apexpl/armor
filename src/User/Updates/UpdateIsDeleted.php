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
 * Update is deleted
 */
class UpdateIsDeleted
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
     * Update is deleted
     */
    public function update(ArmorUserInterface $user, bool $is_deleted)
    {

        // Check if statis is already set
        if ($user->isDeleted() === $is_deleted) { 
            return [UpdateStatus::FAIL, null];
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Update database
        $this->db->update('armor_users', [
            'is_deleted' => ($is_deleted === true ? 1 : 0), 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ], "uuid = %s", $user->getUuid());

        // Add user log
        $action = $is_deleted === true ? 'deleted' : 'undeleted';
        $this->logger->add($user, $action);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'is_deleted', $is_deleted);

        // Commit and return
        $this->db->commit();
        return [UpdateStatus::SUCCESS, $updated_at];
    }

}



