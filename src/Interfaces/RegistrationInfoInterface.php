<?php
declare(strict_types = 1);

namespace Apex\Armor\Interfaces;

/**
 * Registration info interface
 */
interface RegistrationInfoInterface
{

    /**
     * Prepare for user creation
     */
    public function prepare():void;


    /**
     * Get IP address
     */
    public function getRegIpAddress():string;


    /**
     * Get user agent
     */
    public function getRegUserAgent():string;


    /**
     * Get country
     */
    public function getRegCountry():string;


    /**
     * Get provice ISO code
     */
    public function getRegProvinceISOCode():string;


    /**
     * Get province name
     */
    public function getRegProvinceName():string;


    /**
     * get city
     */
    public function getRegCity():string;


    /**
     * Get latitude
     */
    public function getRegLatitude():float;


    /**
     * Get longitude
     */
    public function getRegLongitude():float;

}


