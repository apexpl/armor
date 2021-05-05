<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

/**
 * Random string
 */
class RandomString
{

    /**
     * Generate new random string
     */
    public static function get(int $length, bool $to_lower = false):string
    {

        // Get even length, if needed
        if (($length % 2) == 1) { 
            $length++;
        }

        // Generate string
        $string = unpack("H*", openssl_random_pseudo_bytes($length / 2))[1];
        if ($to_lower === true) { 
            $string = strtolower($string);
        }

        // Return
        return $string;
    }

}





