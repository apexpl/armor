<?php
declare(strict_types = 1);

namespace Apex\Armor\Enums;

/**
 * Require e-mail column
 */
class RequireEmail
{

    public const DISABLED = 'none';
    public const OPTIONAL = 'optional';
    public const REQUIRE = 'require';
    public const REQUIRE_UNIQUE = 'unique';


}

