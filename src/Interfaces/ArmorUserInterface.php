<?php
declare(strict_types = 1);

namespace Apex\Armor\Interfaces;

use DateTime;

/**
 * ArmorUser interface
 */
interface ArmorUserInterface
{

    /**
     * Get uuid
     */
    public function getUuid():string;


    /**
     * Get type
     */
    public function getType():string;


    /**
     * Get username
     */
    public function getUsername():string;


    /**
     * Get password
     */
    public function getPassword():string;


    /**
     * Get email
     */
    public function getEmail():string;


    /**
     * Get phone
     */
    public function getPhone():string;


    /**
     * Get phone country code
     */
    public function getPhoneCountryCode():?string;


    /**
     * Get phone country code
     */
    public function getPhoneNational():?string;


    /**
     * Get two factor type
     */
    public function getTwoFactorType():string;


    /**
     * Get two factor frequency
     */
    public function getTwoFactorFrequency():string;


    /**
     * Get date created
     */
    public function getCreatedAt():?DateTime;


    /**
     * Get updated at
     */
    public function getUpdatedAt():?DateTime;


    /**
     * Get unfreeze
     */
    public function getUnfreezeAt():?DateTime;


    /**
     * Has username
     */
    public function hasUsername():bool;


    /**
     * Has password
     */
    public function hasPassword():bool;


    /**
     * Has e-mail
     */
    public function hasEmail():bool;


    /**
     * Has phone
     */
    public function hasPhone():bool;


    /**
     * Is active
     */
    public function isActive():bool;


    /**
     * Is pending
     */
    public function isPending():bool;


    /**
     * Is frozen
     */
    public function isFrozen():bool;


    /**
     * Is deleted
     */
    public function isDeleted():bool;


    /**
     * isEmailVerified
     */
    public function isEmailVerified():bool;


    /**
     * isPhoneVerified
     */
    public function isPhoneVerified():bool;


    /**
     * To array
     */
    public function toArray():array;


    /**
     * Update username
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updateUsername(string $new_username):string;


    /**
     * Update password
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updatePassword(string $new_password, string $old_password = '', bool $delete_encrypted = false):string;


    /**
     * Update password with master
     */
    public function updatePasswordWithMaster(string $new_password, string $master_password):void;


    /**
     * Update e-mail
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updateEmail(string $new_email, bool $is_verified = false):string;


    /**
     * Update phone
     *
     * @return Apex\Armor\Enums\UpdateStatus constant
     */
    public function updatePhone(string $new_phone, bool $is_verified = false):string;


    /**
     * Update two factor info
     */
    public function updateTwoFactor(string $type = '', string $frequency = ''):void;


    /**
     * Activate
     */
    public function activate():void;


    /**
     * Deactivate
     */
    public function deactivate():void;


    /**
     * Delete
     */
    public function delete():void;


    /**
     * Undelete
     */
    public function undelete():void;


    /**
     * Freeze
     */
    public function freeze(DateTime $unfreeze_at):void;


    /**
     * Unfreeze
     */
    public function unfreeze():void;


    /**
     * List devices
     */
    public function listDevices(string $type = ''):array;


    /**
     * List ip allow
     */
    public function listIpAllow():array;


    /**
     * Get activity log
     */
    public function listActivityLog(int $start = 0, int $limit = 0, bool $sort_desc = true):array;


    /**
     * Get activity log count
     */
    public function getActivityLogCount():int;


    /**
     * Get login history
     */
    public function listLoginHistory(int $start = 0, int $limit = 0, bool $sort_desc = true):array;


    /**
     * Get login history count
     */
    public function getLoginHistoryCount():int;


}


