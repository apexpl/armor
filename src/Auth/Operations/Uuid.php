<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use redis;


/**
 * Uuid generator
 */
class Uuid
{

    /**
     * Generate new UUID
     */
    public static function get(string $type = 'user'):string
    {

        // Initialize
        $redis = Di::get(redis::class);
        $prefix = strtolower(substr($type, 0, 1));

        // Check redis for counter
        if (!$redis->exists('armor:counter:' . $type)) { 

            // Get last user of type
            $db = Di::get(DbInterface::class);
            if ($uuid = $db->getField("SELECT uuid FROM armor_users WHERE type = %s ORDER BY created_at DESC LIMIT 1")) { 

                if (preg_match("/^(\s+?):(\d+)$/", $uuid, $match)) { 
                    $redis->set('armor:counter:' . $type, (int) $match[2]);
                }
            }

            // Get total users, fi uuid not found
            if (!$redis->exists('armor:counter:' . $type)) {  
                $total = $db->getField("SELECT count(*) FROM armor_users WHERE type = %s", $type);
                if ($total == '') { $total = 0; }

                $redis->set('armor:counter:' . $type, $total);
            }
        }

        // Generate uuid
        $num = $redis->incrby('armor:counter:' . $type, 1);
        $uuid = $prefix . ':' . $num;

        // Return
        return $uuid;
    }

}


