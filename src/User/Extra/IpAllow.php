<?php
declare(strict_types = 1);

namespace Apex\Armor\User\Extra;

use Apex\Armor\Armor;
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Operations\Cooki;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;

/**
 * IP allow
 */
class IpAllow
{


    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        private ?DebuggerInterface $debugger = null
    ) { 

        // Get items from container
        $this->db = Di::get(DbInterface::class);
        $this->logger = Di::get(UserLog::class);

    }

    /**
     * Add IP
     */
    public function add(string $uuid, string $ip):void
    {

        // Add to db
        $this->db->insert('armor_users_ipallow', [
            'uuid' => $uuid, 
            'ip_address' => $ip
        ]);

        // Debug
        $this->debugger?->add(1, "Added IP allow to uuid $uuid of IP: $ip", 'info');
    }

    /**
     * Check IP against uuid
     */
    public function check(string $uuid, string $ip = ''):bool
    {

        // Get IP address
        if ($ip == '') { 
            $ip = IpAddress::get();
        }

        // Get IP to check against
        if (!$chk = $this->getUuid($uuid)) { 
            return true;
        }

        // Debug
        $this->debugger?->add(2, "Checking IP authorization for uuid $uuid on IP address: $ip");

        // Go through
        $ok = false;
        foreach ($chk as $chk_ip) { 

            if (!str_starts_with($ip, $chk_ip)) { 
            continue;
        }
            $ok = true;
            break;
        }

        // Return
        return $ok;
    }

    /**
     * List
     */
    public function getUuid(string $uuid):?array
    {

        // Get IPs
        $ips = $this->db->getColumn("SELECT ip_address FROM armor_users_ipallow WHERE uuid = %s", $uuid);
        if (count($ips) == 0) { 
            return null;
        }

        // Return
        return $ips;
    }

    /**
     * Delete IP
     */
    public function delete(string $uuid, string $ip):bool
    {
        $stmt = $this->db->query("DELETE FROM armor_users_ipallow WHERE uuid = %s AND ip_address = %s", $uuid, $ip);
        $num = $this->db->numRows($stmt);

        // Debug
        $this->debugger?->add(1, "Deleted IP authorization from uuid $uuid for IP: $ip", 'info');

        // Return
        return $num > 0 ? true : false;
    }

    /**
     * Delete all on user
     */
    public function deleteUuid(string $uuid):int
    {
        $stmt = $this->db->query("DELETE FROM armor_users_ipallow WHERE uuid = %s", $uuid);
        $this->debugger?->add(1, "Deleted all IP authorization entries for uuid $uuid", 'info');
        return $this->db->numRows($stmt);
    }

    /**
     * Purge all
     */
    public function purge():int
    {
        $stmt = $this->db->query("DELETE FROM armor_users_ipallow");
        $this->debugger?->add(1, "Purged all IP authorizations", 'info');
        return $this->db->numRows($stmt);
    }

}




