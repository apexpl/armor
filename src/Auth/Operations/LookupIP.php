<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

use Apex\Container\Di;


/**
 * Lookup IP address
     */
class LookupIP
{

    /**
     * Lookup ip
     */
    public static function query(string $ip_address):array
    {

        // Init response
        $res = [
            'country' => '', 
            'province_iso_code' => '', 
            'province_name' => '', 
            'city' => '', 
            'latitude' => 0, 
            'longitude' => 0
        ];

        // Get dbfile
        if (!$dbfile = Di::get('armor.maxmind_dbfile')) { 
            $dbfile = __DIR__ . '/../../../GeoLite2-City.mmdb';
        }

        // Return if no db
        if (!file_exists($dbfile)) { 
            return $res;
        }

        // Lookup ip
        $reader = new \MaxMind\Db\Reader($dbfile);
        if (!$vars = $reader->get($ip_address)) { 
            return $res;
        }

        // Check for country
        if (isset($vars['country']) && isset($vars['country']['iso_code'])) { 
            $res['country'] = $vars['country']['iso_code'];
        }

        // Check for city
        if (isset($vars['city']) && isset($vars['city']['names']) && isset($vars['city']['names']['en'])) { 
            $res['city'] = $vars['city']['names']['en'];
        }

        // Check latitude
        if (isset($vars['location']) && isset($vars['location']['latitude'])) { 
            $res['latitude'] = $vars['location']['latitude'];
        }

        // Check longititude
        if (isset($vars['location']) && isset($vars['location']['longitude'])) { 
            $res['longitude'] = $vars['location']['longitude'];
        }

        // Check subdivision
        if (isset($vars['subdivisions']) && isset($vars['subdivisions'][0])) { 
            $sub = $vars['subdivisions'][0];
            $res['province_iso_code'] = $sub['iso_code'] ?? '';
            if (isset($sub['names']) && isset($sub['names']['en'])) { 
                $res['province_name'] = $sub['names']['en'];
            }
        }

        // Return
        return $res;
    }

}


