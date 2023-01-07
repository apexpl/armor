<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require 2FA frequency
 */
class TwoFactorFrequency
{

    public const DISABLED = 'none';
    public const OPTIONAL = 'optional';
    public const EVERY_LOGIN = 'always';
    public const NEW_DEVICE_ONLY = 'new_device';

}


