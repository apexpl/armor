<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require password
 */
class RequirePassword
{
    /**
     * REQUIRE - Used for majority of operations.
     *
     * DISABLED - If set, you should have 'require_phone_verify' setting to REQUIRE.  Used if for example, running a 
     * mobile app and you only wish new users to input phone number and verify said number via one-time code.
     *
     * REQUIRE_AFTER_REGISTER - Upon registration, will create account without password and send e-mail to user with a link where they may define a password.
     * Used to help simplify first step of onboarding.
     */

    public const DISABLED = 'none';
    public const REQUIRE = 'require';
    public const REQUIRE_AFTER_REGISTER = 'after_register';

}

