<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * User log action
 */
class UserLogAction
{

    public const PROFILE_CREATED = 'created';
    public const PROFILE_DELETED = 'deleted';
    public const PROFILE_UNDELETED = 'undeleted';
    public const PROFILE_ACTIVATED = 'activated';
    public const PROFILE_DEACTIVATED = 'deactivated';
    public const PROFILE_FROZEN = 'frozen';
    public const PROFILE_UNFROZEN = 'unfrozen';
    public const DEFINED_PASSWORD = 'defined_password';
    public const CHANGE_USERNAME = 'change_username';
    public const CHANGE_PASSWORD = 'change_password';
    public const CHANGE_EMAIL = 'change_email';
    public const CHANGE_PHONE = 'change_phone';
    public const CHANGE_TWO_FACTOR = 'change_two_factor';
    public const VERIFIED_EMAIL = 'verified_email';
    public const VERIFIED_PHONE = 'verified_phone';

    public const TWO_FACTOR = 'two_factor';
    public const RESET_PASSWORD = 'reset_password';

}

