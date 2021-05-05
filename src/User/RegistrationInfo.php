<?php
declare(strict_types = 1);

namespace Apex\Armor\User;

use Apex\Armor\Armor;
use Apex\Container\Di;
use Apex\Armor\Auth\Operations\{IpAddress, LookupIP, UserAgent};
use Apex\Armor\Interfaces\RegistrationInfoInterface;


/**
 * Registration info
 */
class RegistrationInfo implements RegistrationInfoInterface
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor, 
        protected string $reg_ip = '', 
        protected string $reg_user_agent = '', 
        protected string $reg_country = '', 
        protected string $reg_province_iso_code = '', 
        protected string $reg_province_name = '', 
        protected string $reg_city = '', 
        protected float $reg_latitude = 0, 
        protected float $reg_longitude = 0
    ) {

    }

    /**
     * Prepare for user creation
     */
    public function prepare():void
    {

        // Get IP address
        if ($this->reg_ip == '') { 
            $this->reg_ip = IpAddress::get();
        }

        // Get user-agent
        if ($this->reg_user_agent == '') { 
            $this->reg_user_agent = UserAgent::get();
        }

        // Lookup ip address
        if ($this->reg_country == '' && $this->reg_ip != '') { 
            $res = LookupIP::query($this->reg_ip);
            foreach ($res as $key => $value) {
                $key = 'reg_' . $key; 
                $this->$key = $value;
            }
        }

    }

    /**
     * Get IP address
     */
    public function getRegIpAddress():string
    {
        return $this->reg_ip;
    }

    /**
     * Get user agent
     */
    public function getRegUserAgent():string
    {
        return $this->reg_user_agent;
    }

    /**
     * Get country
     */
    public function getRegCountry():string
    {
        return $this->reg_country;
    }

    /**
     * Get provice ISO code
     */
    public function getRegProvinceISOCode():string
    {
        return $this->reg_province_iso_code;
    }

    /**
     * Get province name
     */
    public function getRegProvinceName():string
    {
        return $this->reg_province_name;
    }

    /**
     * get city
     */
    public function getRegCity():string
    {
        return $this->reg_city;
    }

    /**
     * Get latitude
     */
    public function getRegLatitude():float
    {
        return $this->reg_latitude;
    }

    /**
     * Get longitude
     */
    public function getRegLongitude():float
    {
        return $this->reg_longitude;
    }

}

