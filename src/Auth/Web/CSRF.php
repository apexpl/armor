<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Web;

use Apex\Armor\Armor;
use Apex\Armor\Auth\Operations\{Cookie, RandomString};
use Apex\Armor\Auth\Codes\StringCode;
use Apex\Container\Di;
use redis;

/**
 * CSRF
 */
class CSRF
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 

    }

    /**
     * Init request
     */
    public function init():array
    {

        // Initialize
        $redis = Di::get(redis::class);
        $policy = $this->armor->getPolicy();

        // Get key
        $key = RandomString::get(36);
        $value = RandomString::get(48);
        list($csrf_id, $redis_key) = StringCode::get('csrf');

        // Save to redis
        $redis->set($redis_key, $value);
        $redis->expire($redis_key, $policy->getExpireSessionInactivitySecs());

        // Set cookie
        Cookie::set('csrf_id', $key);
        Cookie::set('csrf_val', hash('sha256', $value));

        // Return
        return [$key, $csrf_id];
    }

    /**
     * Verify
     */
    public function verify(array $post = []):bool
    {

        // Get post data, if needed
        if (count($post) == 0) { 
            $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING) ?? [];
        }
        $redis = Di::get(redis::class);

        // Check
        if (!$key = Cookie::get('csrf_id')) { 
            return false;
        } elseif (!isset($post[$key])) {
            return false;
        } elseif (!$val = Cookie::get('csrf_val')) { 
            return false;
        } elseif (!$chk_val = $redis->get('armor:csrf:' . hash('sha512', $post[$key]))) { 
            return false;
        } elseif ($val != hash('sha256', $chk_val)) { 
            return false;
        }

        // Clean up
        $redis->del('armor:csrf:' . hash('sha512', $post[$key]));
        Cookie::set('csrf_id', '');
        Cookie::set('csrf_val', '');

        // Return
        return true;
    }

}


