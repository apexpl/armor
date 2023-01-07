<?php
declare(strict_types = 1);

namespace Apex\Armor\User;

use Apex\Armor\Armor;
use Apex\Armor\User\{RegistrationInfo, Validator};
use Apex\Armor\Auth\Operations\Password;
use Apex\Armor\User\Updates\{UpdateUsername, UpdatePassword, UpdateEmail, UpdatePhone, UpdateIsActive, UpdateIsDeleted, UpdateIsFrozen, UpdateTwoFactor};
use Apex\Armor\User\Extra\{Devices, IpAllow, UserLog, LoginHistory};
use Apex\Armor\Enums\UpdateStatus;
use Apex\Armor\Interfaces\ArmorUserInterface;
use Apex\Armor\Exceptions\ArmorNotInitializedException;
use Apex\Container\Di;
use Brick\PhoneNumber\PhoneNumber;
use DateTime;


/**
 * User model
 */
class ArmorUser extends RegistrationInfo implements ArmorUserInterface
{

    // Properties
    protected ?DateTime $created_at = null;
    protected ?DateTime $updated_at = null;
    protected ?DateTime $unfreeze_at = null;
    protected ?PhoneNumber $phone_obj = null;


    /**
     * Constructor
     */
    public function __construct(
        protected string $uuid, 
        protected string $password = '',  
        protected string $username = '', 
        protected string $email = '', 
        protected string $phone = '', 
        protected string $type = 'user', 
        protected bool $is_pending = false, 
        protected bool $is_active = true, 
        protected bool $is_frozen = false, 
        protected bool $is_deleted = false, 
        protected bool $email_verified = false, 
        protected bool $phone_verified = false, 
        protected string $two_factor_type = 'none', 
        protected string $two_factor_frequency = 'none', 
        protected ?RegistrationInfo $reginfo = null
    ) {

        // Ensure Amor is initialized
        if (!Di::has(Armor::class)) { 
            throw new ArmorNotInitializedException("The central Armor class has not yet been intalized, hence this class can not be loaded.  Please instantiate the Armor class first.");
        }

        // Import registration info, if needed
        if ($reginfo !== null) { 
            $this->reg_ip = $reginfo->getRegIpAddress();
            $this->reg_user_agent = $reginfo->getRegUserAgent();
            $this->reg_country = $reginfo->getRegCountry();
            $this->reg_province_iso_code = $reginfo->getRegProvinceISOCode();
            $this->reg_province_name = $reginfo->getRegProvinceName();
            $this->reg_city = $reginfo->getRegCity();
            $this->reg_latitude = $reginfo->getRegLatitude();
            $this->reg_longitude = $reginfo->getRegLongitude();
        }

    }

    /**
     * Get uuid
     */
    public function getUuid():string
    {
        return $this->uuid;
    }

    /**
     * Get type
     */
    public function getType():string
    {
        return $this->type;
    }

    /**
     * Get username
     */
    public function getUsername():string
    {
        return $this->username;
    }

    /**
     * Get password
     */
    public function getPassword():string
    {
        return $this->password;
    }

    /**
     * Get email
     */
    public function getEmail():string
    {
        return $this->email;
    }

    /**
     * Get phone
     */
    public function getPhone():string
    {
        return $this->phone;
    }

    /**
     * Get phone country code
     */
    public function getPhoneCountryCode():?string
    {

        // Load phone, if needed
        if ($this->phone == '') { 
            return '';
        } elseif ($this->phone_obj === null) { 
            $this->phone_obj = PhoneNumber::parse('+' . $this->phone);
        }

        // Return
        return $this->phone_obj->getCountryCode();
    }

    /**
     * Get phone country code
     */
    public function getPhoneNational():?string
    {

        // Load phone, if needed
        if ($this->phone == '') { 
            return '';
        } elseif ($this->phone_obj === null) { 
            $this->phone_obj = PhoneNumber::parse('+' . $this->phone);
        }

        // Return
        return $this->phone_obj->getNationalNumber();
    }

    /**
     * Get phone formatted
     */
    public function getPhoneFormatted():string
    {

        // Load phone, if needed
        if ($this->phone == '') { 
            return 'Not Defined';
        } elseif ($this->phone_obj === null) { 
            $this->phone_obj = PhoneNumber::parse('+' . $this->phone);
        }

        // Return
        return $this->phone_obj->formatForCallingFrom($this->phone);
    }

    /**
     * Get two factor type
     */
    public function getTwoFactorType():string
    {
        return $this->two_factor_type;
    }

    /**
     * Get two factor frequency
     */
    public function getTwoFactorFrequency():string
    {
        return $this->two_factor_frequency;
    }

    /**
     * Get date created
     */
    public function getCreatedAt():?DateTime
    {
        return $this->created_at;
    }

    /**
     * Get updated at
     */
    public function getUpdatedAt():?DateTime
    {
        return $this->updated_at;
    }

    /**
     * Get unfreeze at
     */
    public function getUnfreezeAt():?DateTime
    {
        return $this->unfreeze_at;
    }

    /**
     * Has username
     */
    public function hasUsername():bool
    {
        return $this->username == '' ? false : true;
    }

    /**
     * Has password
     */
    public function hasPassword():bool
    {
        return $this->password == '' ? false : true;
    }

    /**
     * Has e-mail
     */
    public function hasEmail():bool
    {
        return $this->email == '' ? false : true;
    }

    /**
     * Has phone
     */
    public function hasPhone():bool
    {
        return $this->phone == '' ? false : true;
    }

    /**
     * Is active
     */
    public function isActive():bool
    {
        return $this->is_active;
    }

    /**
     * Is pending
     */
    public function isPending():bool
    {
        return $this->is_pending;
    }

    /**
     * Is frozen
     */
    public function isFrozen():bool
    {
        return $this->is_frozen;
    }

    /**
     * Is deleted
     */
    public function isDeleted():bool
    {
        return $this->is_deleted;
    }

    /**
     * isEmailVerified
     */
    public function isEmailVerified():bool
    {
        return $this->email_verified;
    }

    /**
     * isPhoneVerified
     */
    public function isPhoneVerified():bool
    {
        return $this->phone_verified;
    }

    /**
     * To array
     */
    public function toArray():array
    {

        // Set variables
        $vars = [
            'uuid' => $this->uuid, 
            'is_active' => $this->is_active === true ? 1 : 0, 
            'is_deleted' => $this->is_deleted === true ? 1 : 0, 
            'is_pending' => $this->is_pending === true ? 1 : 0, 
            'is_frozen' => $this->is_frozen === true ? 1 : 0,
            'has_username' => ($this->username == '' ? 0 : 1), 
            'has_password' => ($this->password == '' ? 0 : 1), 
            'has_email' => ($this->email == '' ? 0 : 1), 
            'has_phone' => ($this->phone == '' ? 0 : 1),  
            'email_verified' => $this->email_verified === true ? 1 : 0, 
            'phone_verified' => $this->phone_verified === true ? 1 : 0, 
            'username' => $this->username, 
            'email' => $this->email, 
            'phone' => $this->phone,
            'phone_country' => $this->getPhoneCountryCode(), 
            'phone_national' => $this->getPhoneNational(), 
            'phone_formatted' => $this->getPhoneFormatted(), 
            'two_factor_type' => $this->two_factor_type, 
            'two_factor_frequency' => $this->two_factor_frequency, 
            'created_at' => $this->created_at, 
            'updated_at' => $this->updated_at
        ];

        // Return
        return $vars;
    }

    /**
     * Update username
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updateUsername(string $new_username):string
    {

        // Update
        $updater = Di::make(UpdateUsername::class);
        list($status, $updated_at) = $updated_at = $updater->update($this, $new_username);

        // Update properties
        if ($status == UpdateStatus::SUCCESS) { 
            $this->username = $new_username;
            $this->updated_at = $updated_at;
        }

        // Return
        return $status;
    }

    /**
     * Update password
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updatePassword(string $new_password, string $old_password = '', bool $delete_encrypted = false):string
    {

        // Update password
        $updater = Di::make(UpdatePassword::class);
        list($status, $updated_at) = $updater->update($this, $new_password, $old_password, $delete_encrypted);

        // Update properties
        if ($status == UpdateStatus::SUCCESS) { 
            $this->password = Password::hash($new_password);
            $this->updated_at = $updated_at;
        }

        // Return
        return $status;
    }

    /**
 * Update password with master
     */
    public function updatePasswordWithMaster(string $new_password, string $master_password):void
    {
        $updater = Di::make(UpdatePassword::class);
        $updater->updateWithMaster($this, $new_password, $master_password);
    }

    /**
     * Update e-mail
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updateEmail(string $new_email, bool $is_verified = false):string
    {

        // Update e-mail
        $updater = Di::make(UpdateEmail::class);
        $vars = $updater->update($this, $new_email, $is_verified);

        // Update properties
        if (isset($vars[0]) && $vars[0] !== true) { 
            $this->email = strtolower($new_email);
            $this->updated_at = $vars[2];
        }

        // Return
        return $vars[1];
    }

    /**
     * Update phone
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updatePhone(string $new_phone, bool $is_verified = false):string
    {

        // Update phone
        $updater = Di::make(UpdatePhone::class);
        list($status, $updated_at) = $updater->update($this, $new_phone, $is_verified);

        // Update properties
        if ($status == UpdateStatus::SUCCESS) { 
            $this->phone = preg_replace("/[\D\W\s]/", "", $new_phone);
            $this->updated_at = $updated_at;
        }

        // Return
        return $status;
    }

    /**
     * Update two factor info
     */
    public function updateTwoFactor(string $type = '', string $frequency = ''):void
    {
        $updater = Di::make(UpdateTwoFactor::class);
        $updater->update($this, $type, $frequency);

        // Update properties
        if ($type != '') { 
            $this->two_factor_type = $type;
        } 
        if ($frequency != '') { 
            $this->two_factor_frequency = $frequency;
        }

    }

    /**
     * Activate
     */
    public function activate():void
    {
        $updater = Di::make(UpdateIsActive::class);
        $updater->update($this, true);
        $this->is_active = true;
        $this->is_pending = false;
    }

    /**
     * Deactivate
     */
    public function deactivate():void
    {
        $updater = Di::make(UpdateIsActive::class);
        $updater->update($this, false);
        $this->is_active = false;
    }

    /**
     * Delete
     */
    public function delete():void
    {
        $updater = Di::make(UpdateIsDeleted::class);
        $updater->update($this, true);
        $this->is_deleted = true;
    }

    /**
     * Undelete
     */
    public function undelete():void
    {
        $updater = Di::make(UpdateIsDeleted::class);
        $updater->update($this, false);
        $this->is_deleted = false;
    }

    /**
     * Freeze
     */
    public function freeze(DateTime $unfreeze_at):void
    {
        $updater = Di::make(UpdateIsFrozen::class);
        $updater->update($this, true, $unfreeze_at);
        $this->is_frozen = true;
    }

    /**
     * Unfreeze
     */
    public function unfreeze():void
    {
        $updater = Di::make(UpdateIsFrozen::class);
        $updater->update($this, false);
        $this->is_frozen = false;
    }

    /**
     * List devices
     */
    public function listDevices(string $type = ''):array
    {
        $devices = Di::make(Devices::class);
        return $devices->getUuid($this->uuid, $type);
    }

    /**
     * List ip allow
     */
    public function listIpAllow():array
    {
        $ipallow = Di::make(IpAllow::class);
        return $ipallow->getUuid($this->uuid);
    }

    /**
     * Get activity log
     */
    public function listActivityLog(int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {
        $userlog = Di::make(UserLog::class);
        return $userlog->listUuid($this->uuid, $start, $limit, $sort_desc);
    }

    /**
     * Get activity log count
     */
    public function getActivityLogCount():int
    {
        $userlog = Di::make(UserLog::class);
        return $userlog->getCountUuid($this->uuid);
    }

    /**
     * Get login history
     */
    public function listLoginHistory(int $start = 0, int $limit = 0, bool $sort_desc = true):array
    {
        $history = Di::make(LoginHistory::class);
        return $history->listUuid($this->uuid, $start, $limit, $sort_desc);
    }

    /**
     * Get login history count
     */
    public function getLoginHistoryCount():int
    {
        $history = Di::make(LoginHistory::class);
        return $history->getCountUuid($this->uuid);
    }




}


