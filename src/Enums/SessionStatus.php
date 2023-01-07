<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Auth session status
 */
class SessionStatus
{

    public const AUTH_OK = 'ok';
    public const EXPIRED = 'expired';
    public const DEFINE_PASSWORD = 'define_password';
    public const DEFINE_PHONE = 'define_phone';
    public const VERIFY_EMAIL = 'verify_email';
    public const VERIFY_EMAIL_OTP = 'verify_email_otp';
    public const VERIFY_PHONE = 'verify_phone';
    public const TWO_FACTOR_AUTHORIZED = 'authorized';
    public const TWO_FACTOR_EMAIL = 'email';
    public const TWO_FACTOR_EMAIL_OTP = 'email_otp';
    public const TWO_FACTOR_PHONE = 'phone';
    public const TWO_FACTOR_PGP = 'pgp';



}

