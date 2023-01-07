<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Extra;

use Apex\Armor\Armor;
use Apex\Armor\User\{ArmorUser, Profiles};
use Apex\Armor\User\Updates\UpdatePassword;
use Apex\Armor\AES\{EncryptAES, DecryptAES};
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;


/**
 * Pending passwords (eg. master password must change user password)
 */
class PendingPasswords
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 
        $this->db = Di::get(DbInterface::class);
    }

    /**
     * Add
     */
    public function add(ArmorUserInterface $user, string $new_password):void
    {

        // Encrypt new password to 'master'
        $enc = Di::make(EncryptAES::class);
        $data_id = $enc->toUuids($new_password, ['master']);

        // Add to database
        $this->db->insert('armor_pending_updates', [
            'uuid' => $user->getUuid(), 
            'action' => 'password', 
            'data_id' => $data_id
        ]);

        // Inform adapter
        $adapter = Di::get(AdapterInterface::class);
        $adapter->pendingPasswordChange($user);
    }

    /**
     * Get count
     */
    public function getCount():int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_pending_updates WHERE action = 'password'")) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

    /**
     * List
     */
    public function list():array
    {

        // Initialize
        $results = [];
        $profiles = Di::make(Profiles::class);

        // Go through pending
        $rows = $this->db->query("SELECT * FROM armor_pending_updates WHERE action = 'password' ORDER BY id");
        foreach ($rows as $row) { 

            $results[] = [
                'id' => $row['id'], 
                'user' => $profiles->getUuid($row['uuid']), 
                'created_at' => new \DateTime($row['created_at'])
            ];
        }

        // Return
        return $results;
    }

    /**
     * Delete uuid
     */
    public function deleteUuid(string $uuid):bool
    {

        // Delete
        $stmt = $this->db->query("DELETE FROM armor_pending_updates WHERE action = 'password' AND uuid = %s", $uuid);
        $num = $this->db->numRows($stmt);

        // Return
        return $num > 0 ? true : false;
    }

    /**
     * Delete all
     */
    public function deleteAll():int
    {
        $stmt = $this->db->query("DELETE FROM armor_pending_updates WHERE action = 'password'");
        return $this->db->numRows($stmt);
    }

    /**
     * Process uuid
     */
    public function processUuid(string $uuid, string $master_password):bool
    {

        // Get row
        if (!$row = $this->db->getRow("SELECT * FROM armor_pending_updates WHERE action = 'password' AND uuid = %s", $uuid)) { 
            return false;
        }

        // Decrypt
        $dec = Di::make(DecryptAES::class);
        $password = $dec->fromUuid((int) $row['data_id'], 'master', $master_password);

        // Load user
        $profiles = Di::make(Profiles::class);
        $user = $profiles->getUuid($row['uuid']);

        // Update password
        $updater = Di::make(UpdatePassword::class);
        $updater->updateWithMaster($user, $password, $master_password);

        // Clean up from db
        $this->db->query("DELETE FROM armor_data WHERE id = %i", $row['data_id']);
        $this->db->query("DELETE FROM armor_pending_updates WHERE id = %i", $row['id']);

        // Return
        return true;
    }

    /**
     * Process all
     */
    public function processAll(string $master_password):void
    {

        // Go through all pending
        $rows = $this->db->query("SELECT * FROM armor_pending_updates WHERE action = 'password' ORDER BY id");
        foreach ($rows as $row) { 
            $this->processUuid($row['uuid'], $master_password);
        }

    }

}

