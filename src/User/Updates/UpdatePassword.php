<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Updates;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Validator};
use Apex\Armor\User\Extra\{UserLog, PendingPasswords};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\Interfaces\{ArmorUserInterface, AdapterInterface};
use Apex\Armor\AES\KeyManager;
use Apex\Armor\Enums\UpdateStatus;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Armor\Exceptions\ArmorProfileValidationException;


/**
 * Update password
 */
class UpdatePassword
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private DbInterface $db, 
        private Validator $validator, 
        private UserLog $logger, 
        private KeyManager $key_manager, 
        private ?DebuggerInterface $debugger = null
    ) {

    }


    /**
     * Update password
     */
    public function update(ArmorUserInterface $user, string $new_password, string $old_password, bool $delete_encrypted = false)
    {

        // Initialize
        $db = Di::get(DbInterface::class);

        // Validate password
        $this->validator->checkPassword($new_password);

        // Check old password
        if ($old_password != '' && !Password::verify($old_password, $user->getPassword())) { 
            throw new ArmorProfileValidationException("Unable to update password, as the old password specified is incorrect.");
        }

        // Check if we can update without old password
        $count = 0;
        if ($old_password == '' && $delete_encrypted === false) { 
            $count = $db->getField("SELECT count(*) FROM armor_data_index WHERE uuid = %s", $user->getUuid());

            // Add pending update, if 'master' RSA key exists
            if ($count > 0 && $id = $this->db->getField("SELECT id FROM armor_keys WHERE uuid = 'master' AND algo = 'rsa'")) {
                $this->debugger?->add(2, "Adding pending password update on uuid '" . $user->getUuid() . "', username: " . $user->getUsername() . " as old password not supplied, and master RSA key exists.");
                $pending = Di::make(PendingPasswords::class);
                $pending->add($user, $new_password);
                return [UpdateStatus::PENDING_ADMIN, null];

            // Give error if encrypted data exists
            } elseif ($count > 0) { 
                throw new ArmorProfileValidationException("Unable to update password as no current password was provided, and one or more encrypted items exist on user account.  You must either confirm delete of encrypted data, or user the master password to update this password.");
            }
        }

        // Debug
        $this->debugger?->add(2, "Changing password of uuid '" . $user->getUuid() . "', username: " . $user->getUsername());

        // Begin transaction
        $this->db->beginTransaction();

        // Delete and re-generate RSA key, if needed
        if ($old_password == '') { 
            $this->key_manager->delete($user->getUuid());
            $this->key_manager->generate($user->getUuid(), $new_password);
        } else { 
            $this->key_manager->changePassword($user->getUuid(), $old_password, $new_password);
        }

        // Update database, and commit
        $updated_at = $this->updateDatabase($user, $new_password);
        $this->db->commit();

        // Return
        return [UpdateStatus::SUCCESS, $updated_at];
    }

    /**
     * Update password with master password
     */
    public function updateWithMaster(ArmorUser $user, string $new_password, string $master_password)
    {

        // Validate password
        $this->validator->checkPassword($new_password);

        // Begin transaction
        $this->db->beginTransaction();

        // Change RSA key
        $key_manager = Di::make(KeyManager::class);
        $key_manager->changePasswordWithMaster($user->getUuid(), $new_password, $master_password);

        // Update database, and commit
        $updated_at = $this->updateDatabase($user, $new_password);
        $this->db->commit();

        // Return
        return [UpdateStatus::SUCCESS, $updated_at];
    }

    /**
     * Update database
     */
    private function updateDatabase(ArmorUser $user, string $new_password):\DateTime
    {

        // Initialize
        $updated_at = new \DateTime();

        // Update password
        $password_hash = Password::hash($new_password);
        $this->db->update('armor_users', [
            'password' => $password_hash, 
            'updated_at' => date('Y-m-d H:i:s', $updated_at->getTimestamp())
        ], "uuid = %s", $user->getUuid());

        // Add user log
        $this->logger->add($user, 'change_password');

        // onUpdate
        $adapter = Di::get(AdapterInterface::class);
        $adapter->onUpdate($user, 'password', $password_hash);

    // Return
        return $updated_at;
    }

}


