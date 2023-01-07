<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Codes;

use Apex\Armor\Auth\Operations\RandomString;
use Apex\Container\Di;
use redis;


/**
 * String code
 */
class StringCode
{

    /**
     * Get
     */
    public static function get(string $type = '2fa', int $length = 48)
    {

        // Initialize
        $redis = Di::get(redis::class);

        // Generate verify hash
        while (true) { 

            // Generate hash
            $string = RandomString::get($length);
            $redis_key = 'armor:' . $type . ':' . hash('sha512', $string);

            // Check if exists
            if (!$redis->exists($redis_key)) { 
                break;
            }
        }

        // Return
        return [$string, $redis_key];
    }

}



