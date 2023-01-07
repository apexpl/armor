<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require phone verify
 */
class VerifyPhone
{

    public const DISABLED = 'none';
    public const REQUIRE = 'require';
    public const OPTIONAL = 'optional';
    public const REQUIRE_AFTER_REGISTER = 'after_register';


}

