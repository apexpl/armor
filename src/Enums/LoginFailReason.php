<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Login failed reason
 */
class LoginFailReason
{

    public const INVALID_USERNAME = 'invalid_username';
    public const INVALID_PASSWORD = 'invalid_password';
    public const USER_INACTIVE = 'inactive';
    public const USER_PENDING = 'pending';
    public const USER_FROZEN = 'frozen';
    public const IP_DENY = 'ipdeny';

}

