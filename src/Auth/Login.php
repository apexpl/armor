<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth;

use Apex\Armor\Armor;
use Apex\Armor\Auth\{AuthSession, SessionManager};
use Apex\Armor\Auth\TwoFactor\TwoFactor;
use Apex\Armor\Auth\Operations\{Password, Cookie};
use Apex\Armor\User\Extra\{Devices, LoginHistory};
use Apex\Armor\Enums\{SessionStatus, LoginFailReason};
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Db\Mapper\ToInstance;


/**
 * Login class
 */
class Login
{

    // Properties
    private string $fail_reason = 'none';


    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) {

        // Get items from container
        $this->db = Di::get(DbInterface::class);
        $this->policy = $armor->getPolicy();

    }

    /**
     * Login with password
     */
    public function withPassword(
        string $username, 
        string $password, 
        string $type = 'user', 
        bool $set_cookie = true, 
        bool $remember_me = false
    ):?AuthSession { 

        // Check credentials
    if (!$user = $this->checkCredentials($username, $password, $type)) { 
            return null;
        }

        // Get login status
        $is_new_device = $this->checkIsNewDevice($user->getUuid());
        $status = $this->getLoginStatus($user, $is_new_device);

        // Create session
        $manager = Di::make(SessionManager::class);
        $session = $manager->create($user, $status, $password, $set_cookie);

        // Set remember me cookies, if needed
        if ($set_cookie === true && $remember_me === true) { 
            Cookie::set('username', $user->getUsername(), ($this->policy->getRememberMeDays() * 86400));

            // Add device, if needed
            if ($is_new_device === true) { 
                $devices = new Devices($this->armor);
                $device_id = $devices->add($user->getUuid());
                Cookie::set('hid', $device_id, ($this->policy->getRememberDeviceDays() * 86400));
            }
        }

        // Check two factor
        if (in_array($status, ['email', 'email_otp', 'phone', 'pgp'])) { 
            $two_factor = Di::make(TwoFactor::class);
            $two_factor->process($session, true);
        }

        // Handle status, if not ok
        if ($status != SessionStatus::AUTH_OK) { 
            $adapter = Di::get(AdapterInterface::class);
            $adapter->handleSessionStatus($session, $status);
        }

        // Return
        return $session;
    }

     /**
     * Check credentials
     */
    public function checkCredentials(string $username, string $password, string $type = 'user'):?ArmorUserInterface
    {

        // Check username and password
        if (!$user = $this->armor->getUser($username, $type, false, false)) { 
            $this->fail_reason = LoginFailReason::INVALID_USERNAME;
            return null;
        } elseif (!Password::verify($password, $user->getPassword())) { 
            return $this->invalidLogin($user->getUuid(), LoginFailReason::INVALID_PASSWORD);
        } elseif ($user->isFrozen() === true && time() > $user->getUnfreezeAt()->getTimestamp()) { 
            $user->unfreeze();
        } elseif ($user->isFrozen() === true) { 
            return $this->invalidLogin($user->getUuid(), LoginFailReason::USER_FROZEN);
        } elseif ($user->isPending() === true) { 
            return $this->invalidLogin($user->getUuid(), LoginFailReason::USER_PENDING);
        } elseif ($user->isActive() === false) { 
            return $this->invalidLogin($user->getUuid(), LoginFailReason::USER_INACTIVE);
        }

        // Check IP address, if needed
        if ($this->policy->getEnableIpcheck() === true) { 
            $ipallow = Di::make(IpAllow::class);
            if (!$ipallow->check($user->getUuid())) { 
                return $this->invalidLogin($user->getUuid(), LoginFailReason::IP_DENY);
            }
        }

        // Return
        return $user;
    }

    /**
     * Get fail reason
     */
    public function getFailReason():string
    {
        return $this->fail_reason;
    }

    /**
     * Get login status
     */
    public function getLoginStatus(ArmorUserInterface $user, bool $is_new_device = true):string
    {

        // Initialize
        $policy = $this->policy;
        $two_factor_type = $policy->getTwoFactorType() == 'optional' ? $user->getTwoFactorType() : $policy->getTwoFactorType();
        $two_factor_frequency = $policy->getTwoFactorFrequency() == 'optional' ? $user->getTwoFactorFrequency() : $policy->getTwoFactorFrequency();

        // Get status
        $status = match(true) { 
            ($policy->getVerifyPhone() == 'after_register' && $user->hasPhone() === false) => SessionStatus::DEFINE_PHONE, 
            ($policy->getVerifyEmail() == 'require' && $user->isEmailVerified() === false) => SessionStatus::VERIFY_EMAIL, 
            ($policy->getVerifyEmail() == 'require_otp' && $user->isEmailVerified() === false) => SessionStatus::VERIFY_EMAIL_OTP, 
            ($policy->getVerifyPhone() == 'require' && $user->isPhoneVerified() === false) => SessionStatus::VERIFY_PHONE, 
            ($two_factor_frequency == 'always' && in_array($two_factor_type, ['email','email_otp','phone','pgp'])) => $two_factor_type, 
            ($two_factor_frequency == 'always' && in_array($two_factor_type, ['phone_email','phone_email_otp']) && $user->isPhoneVerified() === true) => 'phone', 
            ($two_factor_frequency == 'always' && $two_factor_type == 'phone_email_otp' && $user->hasEmail() === true) => 'email_otp', 
            ($two_factor_frequency == 'always' && $two_factor_type == 'phone_email' && $user->hasEmail() === true) => 'email', 
            ($two_factor_frequency == 'new_device' && $is_new_device === true && in_array($two_factor_type, ['email','email_otp','phone','pgp'])) => $two_factor_type, 
            ($two_factor_frequency == 'new_device' && $is_new_device === true && in_array($two_factor_type, ['phone_email','phone_email_otp']) && $user->isPhoneVerified() === true) => 'phone', 
            ($two_factor_frequency == 'new_device' && $is_new_device === true && $two_factor_type == 'phone_email_otp' && $user->hasEmail() === true) => 'email_otp', 
            ($two_factor_frequency == 'new_device' && $is_new_device === true && $two_factor_type == 'phone_email' && $user->hasEmail() === true) => 'email', 
            default => SessionStatus::AUTH_OK
        };

        // Return
        return $status;
    }

    /**
     * Check if new device
     */
    private function checkIsNewDevice(string $uuid):bool
    {

        // Check cookie
        if (!$device_id = Cookie::get('hid')) { 
            return true;
        }

        // Check for device
        if ($id = $this->db->getField("SELECT id FROM armor_users_devices WHERE uuid = %s AND device_id = %s AND is_active = true", $uuid, hash('sha256', $device_id))) { 
            return false;
        } else { 
            return true;
        }
    }

    /**
     * Check brute force policy
     */
    public function invalidLogin(string $uuid, string $reason)
    {

        // Add invalid
        $history = Di::make(LoginHistory::class);
        $history->addLogin($uuid, false);

        // Set reason
        $this->fail_reason = $reason;
        if ($reason != LoginFailReason::INVALID_PASSWORD) { 
            return null;
        }

        // Get last successful login
        if (!$last_login = $this->db->getField("SELECT created_at FROM armor_history_logins WHERE uuid = %s AND is_valid = true ORDER BY created_at DESC LIMIT 1", $uuid)) { 
            $last_login = '2000-01-01 00:00:00';
        }

        // Get invalid login attempts
        $invalid = $this->db->getColumn("SELECT created_at FROM armor_history_logins WHERE uuid = %s AND is_valid = false AND created_at > %s ORDER BY created_at DESC", $uuid, $last_login);
        if (count($invalid) == 0) { 
            return null;
        }
        $invalid = array_map(function($d) { return new \DateTime($d); }, $invalid);
        $start = $invalid[0]->getTimestamp();

        // Get brute force rules
        $rules = $this->policy->getBruteForcePolicy()->getRules();
        $suspend_secs = 0;
        $deactivate = false;

        // Go through rules
        foreach ($rules as $rule) { 

            // Get number of attempts within seconds
            $count=0;
            foreach ($invalid as $dt) { 

                if (($dt->getTimestamp() - $start) > (int) $rule['seconds']) { 
                    break;
                }
                $count++;
            }

            // Check attemps
            if ($rule['attempts'] > $count) { 
                continue;
            }

            // Set variables
            if ($rule['suspend_seconds'] == 0) { 
                $deactivate = true;
            } else { 
                $suspend_secs = (int) $rule['suspend_seconds'];
            }
        }

        // Return if no action
        if ($suspend_secs == 0 && $deactivate === false) { 
            return null;
        }

        // Load user
        $user = $this->armor->getUuid($uuid);
        if ($deactivate === true) { 
            $user->deactivate();
        } else { 
            $unfreeze_at = new \DateTime();
            $unfreeze_at->add(new \DateInterval('PT' . $suspend_secs . 'S')); // adds 674165 secs
            $user->freeze($unfreeze_at);
        }

        // Return
        return null;
    }

}





