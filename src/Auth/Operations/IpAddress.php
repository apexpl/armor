<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;


/**
 * Get IP address
 */
class IpAddress
{

    /**
     * Get IP address
     */
    public static function get():string
    {

        $ip_address = match(true) { 
            isset($_SERVER['HTTP_X_REAL_IP']) => $_SERVER['HTTP_X_REAL_IP'], 
            isset($_SERVER['HTTP_X_FORWARDED_FOR']) => $_SERVER['HTTP_X_FORWARDED_FOR'], 
            isset($_SERVER['REMOTE_ADDR']) => $_SERVER['REMOTE_ADDR'], 
            default => '127.0.0.1'
        };

        // Return
        return $ip_address;
    }

}


