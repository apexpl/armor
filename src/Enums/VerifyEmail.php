<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require e-mail column
 */
class VerifyEmail
{

    public const DISABLED = 'none';
    public const REQUIRE = 'require';
    public const REQUIRE_OTP = 'require_otp';
    public const OPTIONAL = 'optional';
    public const OPTIONAL_OTP = 'optional_otp';



}

