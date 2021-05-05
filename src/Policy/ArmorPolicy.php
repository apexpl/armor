<?php
declare(strict_types = 1);

namespace Apex\Armor\Policy;

use Apex\Armor\Policy\BruteForcePolicy;
use Apex\Armor\Exceptions\ArmorInvalidArgumentException;
use Apex\Container\Di;
use Symfony\Component\String\UnicodeString;


/**
 * Armor security policy
 */
class ArmorPolicy
{

    /**
     * Constructor
     */
    public function __construct(
        private string $username_column = 'username', 
        private bool $create_as_pending = false, 
        private string $require_password = 'require', 
        private string $require_email = 'require', 
        private string $require_phone = 'optional', 
        private string $verify_email = 'none', 
        private string $verify_phone = 'none', 
        private string $two_factor_type = 'none', 
        private string $two_factor_frequency = 'none', 
        private string $default_two_factor_type = 'none', 
        private string $default_two_factor_frequency = 'none', 
        private int $min_password_strength = 3,  
        private int $min_username_length = 0, 
        private int $expire_verify_email_secs = 600, 
        private int $expire_verify_phone_secs = 600, 
        private int $expire_session_inactivity_secs = 1800, 
        private int $expire_redis_session_secs = 1800, 
        private bool $lock_redis_expiration = false, 
        private int $remember_device_days = 90, 
        private int $remember_me_days = 30, 
        private bool $enable_ipcheck = false, 
        private ?BruteForcePolicy $brute_force_policy = null
    ) { 

        // Check brute force policy
        if ($this->brute_force_policy === null) { 
            if (!$default_rules = Di::get('armor.default_brute_force_policy')) { 
                $default_rules = [];
        }
            $this->brute_force_policy = new BruteForcePolicy($default_rules);
        }

        // Go through properties to ensure validation
        foreach (array_keys(get_class_vars(__CLASS__)) as $prop) { 
            if ($prop == 'brute_force_policy') { continue; }

            // Get method name
            $word = new UnicodeString('set_' . $prop);
            $method = (string) $word->camel();

            // Set property
            $this->$method($this->$prop);
        }

    }

    /**
     * Set username column
     */
    public function setUsernameColumn(string $col):void
    {

        // Check valid
        if (!in_array($col, ['username', 'email', 'phone'])) { 
            throw new ArmorInvalidArgumentException("Invalid username column specified, '$col'.  Supported values are: username, email, phone");
        }
        $this->username_column = $col;
    }

    /**
     * Set create as pending
     */
    public function setCreateAsPending(bool $val):void
    {
        $this->create_as_pending = $val;
    }

    /**
     * Set password required
     */
    public function setRequirePassword(string $value):void
    {

        // Check valid
        if (!in_array($value, ['none','require','after_register'])) { 
            throw new ArmorInvalidArgumentException("Invalid require password specified, '$value'.  Please see the RequirePassword enum of supported values.");
        }
        $this->require_password = $value;
    }

    /**
     * Set e-mail required
     */
    public function setRequireEmail(string $value):void
    {

        // Check valid
        if (!in_array($value, ['none','require','optional', 'unique'])) { 
            throw new ArmorInvalidArgumentException("Invalid require e-mail specified, '$value'.  Please see the RequireEmail enum of supported values.");
        }
        $this->require_email = $value;
    }

    /**
     * Set phone required
     */
    public function setRequirePhone(string $value):void
    {

        // Check valid
        if (!in_array($value, ['none','require','optional', 'unique'])) { 
            throw new ArmorInvalidArgumentException("Invalid require phone specified, '$value'.  Please see the RequirePhone enum of supported values.");
        }
        $this->require_phone = $value;
    }

    /**
     * Set require email verify
     */
    public function setVerifyEmail(string $val):void
    {

        // Validate
        if (!in_array($val, ['none', 'require', 'require_otp', 'optional','optional_otp'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of verify e-mail specified.  Please check the VerifyEmail constant for supported values.");
        }
        $this->verify_email = $val;

    }

    /**
     * Set require phone verify
     */
    public function setVerifyPhone(string $val):void
    {

        // Validate
        if (!in_array($val, ['none', 'require', 'optional'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of verify phone specified.  Please check the VerifyPhone constant for supported values.");
        }
        $this->verify_phone = $val;
    }

    /**
     * Set require two factor type
     */
    public function setTwoFactorType(string $val):void
    {

        // Validate
        if (!in_array($val, ['none','optional','email','email_otp','phone','phone_email_otp','phone_email','pgp'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of require two factor type specified.  Please see the RequireTwoFactorType constant for supported values.");
        }
        $this->two_factor_type = $val;
    }

    /**
     * Set require two factor frequency
     */
    public function setTwoFactorFrequency(string $val):void
    {

        // Validate
        if (!in_array($val, ['none','optional','always','new_device'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of require two factor frequency specified.  Please see the RequireTwoFactorFrequency constant for supported values.");
        }
        $this->two_factor_frequency = $val;
    }

    /**
     * Set default two factor type
     */
    public function setDefaultTwoFactorType(string $val):void
    {

        // Validate
        if (!in_array($val, ['none','email','email_otp','phone','pgp'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of default two factor type specified.  Please see the RequireTwoFactorType constant for supported values.");
        }
        $this->default_two_factor_type = $val;
    }

    /**
     * Set default two factor frequency
     */
    public function setDefaultTwoFactorFrequency(string $val):void
    {

        // Validate
        if (!in_array($val, ['none','always','new_device'])) { 
            throw new ArmorInvalidArgumentException("Invalid value of default two factor frequency specified.  Please see the RequireTwoFactorFrequency constant for supported values.");
        }
        $this->default_two_factor_frequency = $val;
    }

    /**
     * Set min password strength
     */
    public function setMinPasswordStrength(int $num):void
    {

        // Validate
        if ($num < 0 || $num > 5) { 
            throw new ArmorInvalidArgumentException("Invalid value of min password strength specified.  Please see the MinPasswordStrength constant for supported values.");
        }
        $this->min_password_strength = $num;
    }

    /**
     * Set min username length
     */
    public function setMinUsernameLength(int $length):void
    {
        $this->min_username_length = $length;
    }

    /**
     * Set expire verify email secs
     */
    public function setExpireVerifyEmailSecs(int $secs):void
    {
        $this->expire_verify_email_secs = $secs;
    }

    /**
     * Expire verify phone secs
     */
    public function setExpireVerifyPhoneSecs(int $secs):void
    {
        $this->expire_verify_phone_secs = $secs;
    }

    /**
     * Expire      session inactivity secs
     */
    public function setExpireSessionInactivitySecs(int $secs):void
    {
        $this->expire_session_inactivity_secs = $secs;
    }

    /**
     * Set redis expiration secs
     */
    public function setExpireRedisSessionSecs(int $secs):void
    {
        $this->expire_redis_session_secs = $secs;
    }

    /**
     * Set lock redis expiration
     */
    public function setLockRedisExpiration(bool $lock):void
    {
        $this->lock_redis_expiration = $lock;
    }

    /**
     * Set remember device days
     */
    public function setRememberDeviceDays(int $days):void
    {
        $this->remember_device_days = $days;
    }

    /**
     * Set remember me days
     */
    public function setRememberMeDays(int $days):void
    {
        $this->remember_me_days = $days;
    }

    /**
     * Set enable ipcheck
     */
    public function setEnableIpcheck(bool $val):void
    {
        $this->enable_ipcheck = $val;
    }

    /**
     * Set brute force policy
     */
    public function setBruteForcePolicy(BruteForcePolicy $policy):void
    {
        $this->brute_force_policy = $policy;
    }

    /**
     * Get username column
     */
    public function getUsernameColumn():string
    {
        return $this->username_column;
    }

    /**
     * Get create as pending
     */
    public function getCreateAsPending():bool
    {
        return $this->create_as_pending;
    }

    /**
     * Get require password
     */
    public function getRequirePassword():string
    {
        return $this->require_password;
    }

    /**
     * Get require e-mail
     */
    public function getRequireEmail():string
    {
        return $this->require_email;
    }

    /**
     * Get require phone
     */
    public function getRequirePhone():string
    {
        return $this->require_phone;
    }

    /**
     * Get verify e-mail
     */
    public function getVerifyEmail():string
    {
        return $this->verify_email;
    }

    /**
     * Get verify phone
     */
    public function getVerifyPhone():string
    {
        return $this->verify_phone;
    }

    /**
     * Get require 2fa type
     */
    public function getTwoFactorType():string
    {
        return $this->two_factor_type;
    }

    /**
     * Get require 2fa frequency
     */
    public function getTwoFactorFrequency():string
    {
        return $this->two_factor_frequency;
    }

    /**
     * Get default two factor type
     */
    public function getDefaultTwoFactorType():string
    {
        return $this->default_two_factor_type;
    }

    /**
     * Get default two factor frequency
     */
    public function getDefaultTwoFactorFrequency():string
    {
        return $this->default_two_factor_frequency;
    }

    /**
    /**
     * Get min password strength
     */
    public function getMinPasswordStrength():int
    {
        return $this->min_password_strength;
    }

    /**
     * Get min username length
     */
    public function getMinUsernameLength():int
    {
        return $this->min_username_length;
    }

    /**
     * Get expire verify email secs
     */
    public function getExpireVerifyEmailSecs():int
    {
        return $this->expire_verify_email_secs;
    }

    /**
     * Get expire verify phone secs
     */
    public function getExpireVerifyPhoneSecs():int
    {
        return $this->expire_verify_phone_secs;
    }

    /**
     * Get expire session inactivity secs
     */
    public function getExpireSessionInactivitySecs():int
    {
        return $this->expire_session_inactivity_secs;
    }

    /**
     * Get expire redis session secs
     */
    public function getExpireRedisSessionSecs():int
    {
        return $this->expire_redis_session_secs;
    }

    /**
     * Get lock redis expiration
     */
    public function getLockRedisExpiration():bool
    {
        return $this->lock_redis_expiration;
    }

    /**
     * Get remember device days
     */
    public function getRememberDeviceDays():int
    {
        return $this->remember_device_days;
    }

    /**
     * Get remember me days
     */
    public function getRememberMeDays():int
    {
        return $this->remember_me_days;
    }

    /**
     * Get enable IP check
     */
    public function getEnableIpcheck():bool
    {
        return $this->enable_ipcheck;
    }

    /**
     * Get brute forec policy
     */
    public function getBruteForcePolicy():BruteForcePolicy
    {
        return $this->brute_force_policy;
    }


}


