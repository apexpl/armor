<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Extra;

use Apex\Armor\Armor;
use Apex\Armor\User\ArmorUser;
use Apex\Armor\Auth\Operations\{IpAddress, UserAgent};
use Apex\Armor\Interfaces\ArmorUserInterface;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;

/**
 * User log
 */
class UserLog
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
    public function add(ArmorUserInterface $user, string $action, string $old_item = '', string $new_item = ''):void
    {

        // Add to database
        $this->db->insert('armor_users_log', [
            'uuid' => $user->getUuid(), 
            'action' => $action,
            'ip_address' => IpAddress::get(), 
            'user_agent' => UserAgent::get(),  
            'old_item' => $old_item, 
            'new_item' => $new_item
        ]);

    }

    /**
     * List by uuid
     */
    public function listUuid(string $uuid, int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM armor_users_log WHERE uuid = %s ORDER BY created_at $order_dir";
        if ($limit > 0) { 
            $sql .= " LIMIT " . $start . ',' . $limit;
        }

        // Go through log
        $log = [];
        $rows = $this->db->query($sql, $uuid);
        foreach ($rows as $row) {
            unset($row['uuid']); 
            $row['created_at'] = new \DateTime($row['created_at']);
            $log[] = $row;
        }

        // Return
        return $log;
    }

    /**
     * List all actions
     */
    public function listAll(bool $is_deleted = false, int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT armor_users_log.* FROM armor_users_log,armor_users WHERE armor_users_log.uuid = armor_users.uuid AND armor_users.is_deleted = %b ORDER BY created_at $order_dir";
        if ($limit > 0) { 
            $sql .= ' LIMIT ' . $start . ',' . $limit;
        }

        // GO through log
        $log = [];
        $rows = $this->db->query($sql, $is_deleted);
        foreach ($rows as $row) { 
            $row['user'] = $this->armor->getUuid($row['uuid']);
            $row['created_at'] = new \DateTime($row['created_at']);
            $log[] = $row;
        }

        // Return
        return $log;
    }

    /**
     * Get count of uuid
     */
    public function getCountUuid(string $uuid):int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_users_log WHERE uuid = %s", $uuid)) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

    /**
     * Get count of all
     */
    public function getCountAll():int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_users_log")) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

}



