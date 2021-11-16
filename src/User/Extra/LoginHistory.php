<?php
declare(strict_types = 1);


namespace Apex\Armor\User\Extra;

use Apex\Armor\Armor;
use Apex\Armor\Auth\Operations\{IpAddress, UserAgent};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;


/**
 * Login history
 */
class LoginHistory
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
     * Add login
     */
    public function addLogin(string $uuid, bool $is_valid = true, bool $is_auto_login = false):int
    {

        // Add to db
        $this->db->insert('armor_history_logins', [
            'is_valid' => ($is_valid === true ? 1 : 0),
            'is_auto_login' => ($is_auto_login === true ? 1 : 0), 
            'uuid' => $uuid, 
            'ip_address' => IpAddress::get(), 
            'user_agent' => UserAgent::get()
        ]);
        $history_id = $this->db->insertId();

        // Return
        return $history_id;
    }

    /**
     * Add page request
     */
    public function addPageRequest(int $history_id):void
    {

        // Add to db
        $this->db->insert('armor_history_reqs', [
            'history_id' => $history_id, 
            'method' => ($_SERVER['REQUEST_METHOD'] ?? 'GET'), 
            'uri' => ($_SERVER['REQUEST_URI'] ?? ''), 
            'query_string' => ($_SERVER['QUERY_STRING'] ?? '')
        ]);

    }

    /**
     * List uuid
     */
    public function listUuid(string $uuid, int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM armor_history_logins WHERE uuid = %s ORDER BY created_at $order_dir";
        if ($limit > 0) { 
            $sql .= ' LIMIT ' . $start . ',' . $limit;
        }

        // Go through rows
        $logins = [];
        $rows = $this->db->query($sql, $uuid);
        foreach ($rows as $row) { 
            $logins[] = $row;
        }

        // Return
        return $logins;
    }

    /**
     * List all
     */
    public function listAll(int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM armor_history_logins ORDER BY created_at $order_dir";
        if ($limit > 0) { 
            $sql .= ' LIMIT ' . $start . ',' . $limit;
        }

        // Go through rows
        $logins = [];
        $rows = $this->db->query($sql);
        foreach ($rows as $row) {
            $row['user'] = $this->armor->getUuid($row['uuid']);
            $logins[] = $row;
        }

        // Return
        return $logins;
    }

    /**
     * List type
     */
    public function listType(string $type = 'user', bool $is_deleted = false, int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT armor_history_logins.* FROM armor_history_logins, armor_users WHERE armor_users.type = %s AND armor_history_logins.uuid = armor_users.uuid AND armor_users.is_deleted = %b ORDER BY armor_history_logins.created_at $order_dir";
        if ($limit > 0) { 
            $sql .= ' LIMIT ' . $start . ',' . $limit;
        }

        // Go through rows
        $logins = [];
        $rows = $this->db->query($sql, $type, $is_deleted);
        foreach ($rows as $row) {
            $row['user'] = $this->armor->getUuid($row['uuid']);
            $logins[] = $row;
        }

        // Return
        return $logins;
    }

    /**
     * Get count uuid
     */
    public function getCountUuid(string $uuid):int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_history_logins WHERE uuid = %s", $uuid)) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

    /**
     * Get count all
     */
    public function getCountAll():int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_history_logins")) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

    /**
     * Get count type
     */
    public function getCountType(string $type = 'user', bool $is_deleted = false):int
    {

        // Get count
        if (!$count = $this->db->getField("SELECT count(*) FROM armor_history_logins, armor_users WHERE armor_users.type = %s AND armor_history_logins.uuid = armor_users.uuid AND armor_users.uuid = %b", $type, $is_deleted)) { 
            $count = 0;
        }

        // Return
        return (int) $count;
    }

    /**
     * Get page requests of session
     */
    public function listPageRequests(int $history_id, int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {

        // Get SQL
        $order_dir = $sort_desc === true ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM armor_history_reqs WHERE history_id = %i ORDER BY created_at $order_dir";
        if ($limit > 0) { 
            $sql .= ' LIMIT ' . $start . ',' . $limit;
        }

        // Go through sql
        $log = [];
        $rows = $this->db->query($sql, $history_id);
        foreach ($rows as $row) { 
            $log[] = $row;
        }

        // Return
        return $log;
    }


}



