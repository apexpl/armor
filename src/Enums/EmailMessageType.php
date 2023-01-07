<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * E-mail message type
 */
class EmailMessageType
{

    public const VERIFY = 'verify';
    public const VERIFY_OTP = 'verify_otp';
    public const TWO_FACTOR = 'two_factor';
    public const TWO_FACTOR_OTP = 'two_factor_otp';
    public const RESET_PASSWORD = 'reset_password';
    public const DEFINE_INITIAL_PASSWORD = 'initial_password';

}

