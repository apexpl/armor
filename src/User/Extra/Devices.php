<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Extra;

use Apex\Armor\Armor;
use Apex\Armor\Auth\Operations\RandomString;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;

/**
 * User devices
 */
class Devices
{

    /**
     8 Constructor
     */
    public function __construct(
        private Armor $armor, 
        private ?DebuggerInterface $debugger = null
    ) { 

        // Get items from container
        $this->db = Di::get(DbInterface::class);

    }

    /**
     * Add device
     */
    public function add(string $uuid, string $device_id = '', string $type = 'pc'):string
    {

        // Get device id, if needed
        if ($device_id == '') { 
            $device_id = RandomString::get(64);
            $device_hash = hash('sha256', $device_id);
        } else { 
            $device_hash = $device_id;
        }

        // Add to database
        $this->db->insert('armor_users_devices', [
            'uuid' => $uuid, 
            'type' => $type, 
            'device_id' => $device_hash 
        ]);

        // Debug
        $this->debugger?->add(1, "Added device to uuid $uuid of type $type with id# $device_id");

        // Return
        return $device_id;
    }

    /**
     * Get a device
     */
    public function get(string $device_id):?array
    {

        // Get row
        if (!$row = $this->db->getRow("SELECT * FROM armor_users_devices WHERE device_id = %s", $device_id)) {             return null;
        }

        // Return
        return $row;
    }

    /**
     * Get all devices on a uuid
     */
    public function getUuid(string $uuid, string $type = ''):array
    {

        // Get rows
        if ($type != '') { 
            $rows = $this->db->query("SELECT * FROM armor_users_devices WHERE uuid = %s AND type = %s ORDER BY id", $uuid, $type);
        } else { 
            $rows = $this->db->query("SELECT * FROM armor_users_devices WHERE uuid = %s ORDER BY id", $uuid);
        }

        // Get device results
        $devices = [];
        foreach ($rows as $row) { 
            $devices[] = $row;
        }

        // Return
        return $devices;
    }

    /**
     * Delete device
     */
    public function delete(string $device_id):bool
    {
        $stmt = $this->db->query("DELETE FROM armor_users_devices WHERE device_id = %s", $device_id);
        $num = $this->db->numRows($stmt);

        // Debug
        $this->debugger?->add(1, "Deleted device id# $device_id");

        // Return
        return $num > 0 ? true : false;
    }

    /**
     * Delete uuid
     */
    public function deleteUuid(string $uuid):int
    {
        $stmt = $this->db->query("DELETE FROM armor_users_devices WHERE uuid = %s", $uuid);
        $this->debugger?->add(1, "Deleted all devices from uuid $uuid");
        return $this->db->numRows($stmt);
    }

    /**
     * Purge all devices
     */
    public function purge(string $type = ''):void
    {

        if ($type != '') { 
            $this->db->query("DELETE FROM armor_users_devices WHERE type = %s", $type);
        } else { 
            $this->db->query("DELETE FROM armor_users_devices");
        }

        // Debugger
        $this->debugger?->add(1, "Purged all user devices");

    }

}

