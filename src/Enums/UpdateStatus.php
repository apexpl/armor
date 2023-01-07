<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Status returned upon updating username, password, e-mail address or phone number
 */
class UpdateStatus
{

    public const SUCCESS = 'success';
    public const FAIL = 'fail';
    public const PENDING_VERIFY = 'pending_verify';
    public const PENDING_OTP = 'pending_otp';
    public const PENDING_ADMIN = 'pending_admin';


}

