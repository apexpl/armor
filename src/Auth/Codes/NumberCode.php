<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Codes;

use Apex\Container\Di;
use redis;


/**
 * Six digit confirmation code
 */
class NumberCode
{

    /**
     * Get
     */
    public static function get(string $user_string = '', string $type = '2fa', int $length = 6)
    {

        // Initialize
        $redis = Di::get(redis::class);

        // Get start / end numbers
        $start = 1;
        for ($x=1; $x < $length; $x++) { 
            $start *= 10;
        }
        $end = ($start * 10) - 1;

        // Generate hash
        while (true) { 

            // Generate hash
            $code = (string) random_int($start, $end);
            $redis_key = 'armor:' . $type . ':' . hash('sha512', $user_string . ':' . $code);

            // Check if exists
            if (!$redis->exists($redis_key)) { 
                break;
            }
        }

        // Return
        return [$code, $redis_key];
    }

}



