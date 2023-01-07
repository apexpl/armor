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
use DateTime;


/**
 * Update is frozen
 */
class UpdateIsFrozen
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
    public function update(ArmorUserInterface $user, bool $is_frozen, ?DateTime $unfreeze_at = null)
    {

        // Check if statis is already set
        if ($user->isFrozen() === $is_frozen) { 
            return [UpdateStatus::FAIL, null];
        }

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Set updates
        $updates = [
            'is_frozen' => ($is_frozen === true ? 1 : 0), 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ];
        if ($is_frozen === true && $unfreeze_at !== null) { 
            $updates['unfreeze_at'] = date('Y-m-d H:i:s', $unfreeze_at->getTimestamp());
        }

        // Update database
        $this->db->update('armor_users', $updates, "uuid = %s", $user->getUuid());

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'is_frozen', $is_frozen);


        // Add user log
        $action = $is_frozen === true ? 'frozen' : 'unfrozen';
        $this->logger->add($user, $action);

        // Commit and return
        $this->db->commit();
        return [UpdateStatus::SUCCESS, $updated_at];
    }

}



