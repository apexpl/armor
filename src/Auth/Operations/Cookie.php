<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

use Apex\Container\Di;
use Apex\Armor\Exceptions\ArmorCookieNotSetException;
use redis;


/**
 * Cookie
 */
class Cookie
{

    // Properties
    private static array $cookie = [];


    /**
     * Set cookie
     */
    public static function set(string $name, string $value, int $expires = 0):void
    {

        // Get cookie, if needed
        if (count(self::$cookie) == 0) { 
            self::$cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING) ?? [];
        }

        // Get cookie name
        $prefix = Di::get('armor.cookie_prefix');
        $name = $prefix . $name;
        self::$cookie[$name] = $value;

        // Get options
        $options = Di::get('armor.cookie');
        if ($expires > 0) { 
            $options['expires'] = (time() + $expires);
        }

        // Check if testing via phpunit
        if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'phpunit')) { 
            $redis = Di::get(redis::class);
            $redis->hset('armor:test:cookie', $name, $value);
            return;
        }

        // Set cookie
        if (!setcookie($name, $value, $options)) { 
            throw new ArmorCookieNotSetException("Unable to set cookie with name '$name'");
        }

    }

    /**
     * Get
     */
    public static function get(string $name):?string
    {

        // Get cookie, if needed
        if (count(self::$cookie) == 0) { 
            self::$cookie = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_STRING) ?? [];
        }

        // Get cookie name
        $prefix = Di::get('armor.cookie_prefix');
        $name = $prefix . $name;

        // Check if testing via phpunit
        if (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], 'phpunit')) { 
            $redis = Di::get(redis::class);
            if (!$value = $redis->hget('armor:test:cookie', $name)) { 
                return null;
            }
            return $value;
        }

        // Return
        return self::$cookie[$name] ?? null;
    }

}


