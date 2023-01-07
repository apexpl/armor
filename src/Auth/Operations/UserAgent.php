<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

/**
 * User agent
 */
class UserAgent
{

    /**
     * Get user agent
     */
    public static function get():string
    {

        // Get user agent
        $string = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($string == '' || !$ua = parse_user_agent($string)) { 
            return 'unknown v0.0 (unknown)';
        }

        // Return
        $ua_string = $ua['browser'] . ' v' . $ua['version'] . ' (' . $ua['platform'] . ')';
        return $ua_string;
    }

}


