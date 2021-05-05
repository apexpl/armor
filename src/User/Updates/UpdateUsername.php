<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Container\Di;



/**
 * Update username
 */
class UpdateUsername
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
     * Update username
     */
    public function update(ArmorUserInterface $user, string $new_username)
    {

        // Check for different username
        if ($user->getUsername() == $new_username) { 
            $this->debugger?->add(2, "Skipping updating username of uuid '" . $user->getUuid() . "', username: " . $user->getUsername() . " as new username is the same.");
            return [UpdateStatus::FAIL, null];
        }

        // Validate
        $this->validator->checkUsername($new_username, $user->getType());

        // Debugger
        $this->debugger?->add(2, "Changing username of uuid '" . $user->getUuid() . "' from " . $user->getUsername() . " to $new_username");

        // Begin transaction
        $this->db->beginTransaction();
        $updated_at = new \DateTime();

        // Update database
        $this->db->update('armor_users', [
            'username' => $new_username, 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ], "uuid = %s", $user->getUuid());

        // Add user log
        $this->logger->add($user, 'change_username', $user->getUsername(), $new_username);

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'username', $new_username);

        // Return
        $this->db->commit();
        return [UpdateStatus::SUCCESS, $updated_at];
    }

}


