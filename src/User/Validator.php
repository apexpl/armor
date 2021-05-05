<?php
declare(strict_types = 1);

namespace Apex\Armor\User;

use Apex\Armor\Armor;
use Apex\Container\Di;
use Apex\Armor\Auth\Operations\Password;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Apex\Db\Interfaces\DbInterface;
use Apex\Armor\Exceptions\ArmorProfileValidationException;


/**
 * Validate user profiles.
 */
class Validator
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor 
    ) {

        // Get items from container
        $this->db = Di::get(DbInterface::class);
        $this->policy = $armor->getPolicy();
    }

    /**
     * Validate
     */
    public function validate(
        string $uuid = '',  
        string $password = '',  
        string $username = '', 
        string $email = '', 
        string $phone = '', 
        string $type = 'user', 
        bool $throw_error = true
    ):bool {

        // Check uuid
        if ($uuid != '' && !$this->checkUuid($uuid, $throw_error)) { 
            return false;
        }

        // Check password
        if (!$this->checkPassword($password, $throw_error)) { 
            return false;
        }

        // Check username
        if (!$this->checkUsername($username, $type, $throw_error)) { 
            return false;
        }

        // Check e-mail
        if (!$this->checkEmail($email, $type, $throw_error)) { 
            return false;
        }

        // Check phone
        if (!$this->checkPhone($phone, $type, $throw_error)) { 
            return false;
        }

        // Return
        return true;
    }

    /**
     * Check uuid
     */
    public function checkUuid(string $uuid, bool $throw_error = false):bool
    {

        // Check
        if ($id = $this->db->getField("SELECT uuid FROM armor_users WHERE uuid = %s", $uuid)) { 
            return $this->error("The uuid '$uuid' already exists, and can not be created.", $throw_error);
        }

        // Return
        return true;
    }

    /**
     * Check username
     */
    public function checkUsername(string $username, string $type = 'user', bool $throw_error = false):bool
    {

        // Initialize
        $min_length = $this->policy->getMinUsernameLength();
        $username_col = $this->policy->getUsernameColumn();

        // Check for blank
        if ($username_col == 'username' && $username == '') {  
            return $this->error("Username was not specified, and is required.", $throw_error);

        // Check for valid username
        } elseif (preg_match("/[\s\W]/", $username)) { 
            return $this->error("Username can not contain spaces or special characters, $username", $throw_error);

        // Check min length
        } elseif ($min_length > 0 && strlen($username) < $min_length) {  
            return $this->error("Username must be a minimum of $min_length characters, $username", $throw_error);

        // Check for duplicate
        } elseif ($username != '' && $uuid = $this->db->getField("SELECT uuid FROM armor_users WHERE LOWER(username) = %s AND type = %s AND is_deleted = false", strtolower($username), $type)) {  
            return $this->error("The username already exists, $username", $throw_error);
        }

        // Check reserved usernames
        $reserved = Di::get('armor.reserved_usernames') ?? [];
        foreach ($reserved as $chk) { 

            if (preg_match("/^~(.+)$/", $chk, $match) && str_contains(strtolower($username), strtolower($match[1]))) { 
                return $this->error("This username is reserved and can not be created, $username", $throw_error);
            } elseif (strtolower($chk) == strtolower($username) && !str_starts_with($chk, '~')) { 
                return $this->error("This username is reserved and can not be created, $username", $throw_error);
            }
        }

        // Return
        return true;
    }

    /**
     * Check password
     */
    public function checkPassword(string $password, bool $throw_error = false):bool
    {

        // Get strength
        $score = Password::getStrength($password);
        $min_password_strength = $this->policy->getMinPasswordStrength();
        $require_password = $this->policy->getRequirePassword();

        // Check for blank password
        if ($require_password == 'require' && $password == '') {  
            return $this->error("No password was supplied, which is required.", $throw_error);

        // Check
        } elseif ($password != '' && $score < $min_password_strength) {  
            return $this->error("The password is not strong enough.  Please use a stronger password, and try again.", $throw_error);
        }

        // Return
        return true;
    }

    /**
     * Check e-mail
     */
    public function checkEmail(string $email, string $type = 'user', bool $throw_error = false):bool
    {

        // Get policy item
        $require_email = $this->policy->getRequireEmail();

        // Check for blank e-mail
        if (in_array($require_email, ['require','unique']) && $email == '') {
            return $this->error("No e-mail address was specified, which is required.", $throw_error);
        } elseif ($email == '') { 
            return true;
        }

        // Validate e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {  
            return $this->error("Invalid e-mail address, $email", $throw_error);

        // Check duplicate
        } elseif ($require_email == 'unique' && $id = $this->db->getField("SELECT uuid FROM armor_users WHERE email = %s AND type = %s", strtolower($email), $type)) {  
            return $this->error("A user already exists with the e-mail address, $email", $throw_error);
        }

        // Return
        return true;
    }

    /**
     * Check phone
     */
    public function checkPhone(string $phone, string $type = 'user', bool $throw_error = false):bool
    {

        // Get policy item
        $require_phone = $this->policy->getRequirePhone();

        // Check for blank phone
        if (in_array($require_phone, ['require','unique']) && $phone == '') {  
            return $this->error("No phone number was specified, which is required.", $throw_error);
        } elseif ($phone == '') { 
            return true;
        }

        // Validate phone
        $phone = preg_replace("/[\s\W]/", "", $phone);
        try {
            $number = PhoneNumber::parse('+' . $phone);
        } catch(PhoneNumberParseException $e) { 
            return $this->error("Invalid phone number, $phone.  " . $e->getMessage(), $throw_error);
        }

    // Check for duplicate
        if ($require_phone == 'unique' && $id = $this->db->getField("SELECT uuid FROM armor_users WHERE phone = %s AND type = %s", $phone, Type)) {  
            return $this->error("A user already exists with the phone number, $phone", $throw_error);
        }

        // Return
        return true;
    }

    /**
     * Error
     */
    private function error(string $message, bool $throw_error = false):bool
    {

        if ($throw_error === true) { 
            throw new ArmorProfileValidationException($message);
        }
        return false;
    }

}


