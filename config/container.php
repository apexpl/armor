<?php

/**
 * Container definitions for Armor
 */

use Apex\Db\Interfaces\DbInterface;
use Apex\Debugger\Interfaces\DebuggerInterface;
use Apex\Armor\Enums\{UsernameColumn, RequirePassword, RequireEmail, RequirePhone, VerifyEmail, VerifyPhone, TwoFactorType, TwoFactorFrequency, MinPasswordStrength};
use Apex\Armor\Interfaces\AdapterInterface;
use Apex\Mercury\Email\Emailer;
use Apex\Mercury\SMS\NexmoConfig;


return [

    /**
     * Database.  Change with your credentials.
     */
    DbInterface::class => [\Apex\Db\Drivers\mySQL\mySQL::class, ['params' => [
        'dbname' => 'my_database',
        'user' => 'myuser', 
        'password' => 'secret_password']]
    ], 

    /**
     * redis connection
     */
    redis::class => function () {
        $redis = new redis();
        $redis->connect('127.0.0.1', 6379, 2);
        //$redis->auth('your_redis_password');
        return $redis;
    }, 

    /**
     * Cookie settings
     */
    'armor.cookie_prefix' => 'armor_', 
    'armor.cookie' => [
        'path' => '/', 
        'domain' => 'domain.com', 
        'secure' => true, 
        'httponly' => true, 
        'samesite' => 'strict'
    ], 

    /**
     * Default ArmorPolicy options.  If no ArmorPolicy object is passed during instantiation, 
     * one will be created using these defaults.  Please see the docs for details.
     */
    'armor.default_policy' => [
        'username_column' => UsernameColumn::USERNAME, 
        'create_as_pending' => false, 
        'require_password' => RequirePassword::REQUIRE,
        'require_email' => RequireEmail::REQUIRE, 
        'require_phone' => RequirePhone::OPTIONAL, 
        'verify_email' => VerifyEmail::OPTIONAL, 
        'verify_phone' => VerifyPhone::OPTIONAL, 
        'two_factor_type' => TwoFactorType::DISABLED, 
        'two_factor_frequency' => TwoFactorFrequency::DISABLED, 
        'default_two_factor_type' => 'none', 
        'default_two_factor_frequency' => 'none', 
        'min_password_strength' => MinPasswordStrength::MEDIUM, 
        'min_username_length' => 0, 
        'expire_verify_email_secs' => 600, 
        'expire_verify_phone_secs' => 600, 
        'expire_session_inactivity_secs' => 1800, 
        'expire_redis_session_secs' => 1800, 
        'lock_redis_expiration' => false, 
        'remember_device_days' => 90, 
        'remember_me_days' => 30, 
        'enable_ipcheck' => false
    ], 

    /**
     * Default brute force policy
     *    After 5 attempts within 60 seconds, suspends for 10 mins
     *    After 20 attempts within 6 hours, suspends for 6 hours.
     *    After 50 attempts within 12 hours, permanently suspends until manually reactivated.
     */
    'armor.default_brute_force_policy' => [
        ['attempts' => 5, 'seconds' => 60, 'suspend_seconds' => 600], 
        ['attempts' => 20, 'seconds' => 3600, 'suspend_seconds' => 21600], 
        ['attempts' => 50, 'seconds' => 43200, 'suspend_seconds' => 0]
    ],

    /**
     * Reserved username list.  Entries that begin with ~ signify username can not contain that item, others are can not match that item.
     */
    'armor.reserved_usernames' => ['~admin~'], 

    /**
     * Adapter interface
     */
    AdapterInterface::class => \Apex\Armor\Adapters\MercuryAdapter::class, 

    /**
     * Location of MaxMind GeoLite2-City.mmdb file
     */
    'armor.maxmind_dbfile' => __DIR__ . '/../GeoLite2-City.mmdb', 

    /**
     * SMTP server details, if using Apex Mercury package to send e-mails.
     */
    Emailer::class => [Emailer::class, ['smtp' => [
        'is_ssl' => 1, 
        'host' => 'smtp.gmail.com', 
        'port' => 465, 
        'user' => 'you@domain.com',
        'password' => 'secret_password'
    ], 

    /**
     * Nexmo API details, if using Apex Mercury package to send SMS messages.
     */
    NexmoConfig::class => [NexmoConfig::class, [
        'api_key' => 'api_key', 
        'api_secret' => 'api_secret', 
        'sender' => 'sender']
    ], 

    /**
     * Debugger.  Only applicable if using the APex Debugger.
     */
    DebuggerInterface::class => [\Apex\Debugger\Debugger::class, ['debug_level' => 3]], 

    /**
     * Ignore this item.
     */
    'migrations.yaml_file' => __DIR__ . '/migrations.yml'

];


