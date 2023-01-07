<?php
declare(strict_types = 1);

namespace Apex\Armor\Auth\Operations;

/**
 * Password class
 */
class Password
{

    /**
     * Hash password
     */
    public static function hash(string $password):string
    {
        return password_hash($password, PASSWORD_BCRYPT, array('COST' => 12));
    }

    /**
     * Verify
     */
    public static function verify(string $password, string $hash):bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Get strength of password
     */
    public static function getStrength(string $password):int
    {

        // Get score
        $score = 0;
        if (strlen($password) >= 8) { $score++; }
        if (strlen($password) >= 12) { $score++; }
        if (preg_match("/\d/", $password)) { $score++; }
        if (preg_match("/[a-z]/", $password) && preg_match("/[A-Z]/", $password)) { $score++; }
        if (preg_match("/\W/", $password)) { $score++; }

        // Return
        return $score;
    }

}


