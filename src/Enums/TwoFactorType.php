<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require 2FA
 */
class TwoFactorType
{

    public const DISABLED = 'none';
    public const OPTIONAL = 'optional';
    public const EMAIL = 'email';
    public const EMAIL_OTP = 'email_otp';
    public const PHONE = 'phone';
    public const PHONE_OR_EMAIL = 'phone_email';
    public const PHONE_OR_EMAIL_OTP = 'phone_email_otp';
    public const PGP = 'pgp';

}

