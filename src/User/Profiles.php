<?php
declare(strict_types = 1);

namespace Apex\Armor\User;

use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\User\{RegistrationInfo, ArmorUser, Validator};
use Apex\Armor\User\Extra\UserLog;
use Apex\Armor\Auth\Operations\{Password, Uuid};
use Apex\Armor\User\Verify\{VerifyEmail, VerifyPhone, VerifyEmailOTP, InitialPassword};
use Apex\Armor\Auth\SessionManager;
use Apex\Armor\Interfaces\{AdapterInterface, ArmorUserInterface};
use Apex\Container\Di;
use Apex\Armor\AES\KeyManager;
use Apex\Armor\Exceptions\{ArmorUuidNotExistsException, ArmorUsernameNotExistsException};
use Apex\Db\Interfaces\DbInterface;
use redis;


/**
 * Manage user profiles.
 */
class Profiles
{

    // Properties
    protected Armor $armor;
    protected ArmorPolicy $policy;


    /**
     * Create profile
     */
    public function createUser(
        string $uuid = '',  
        string $password = '',  
        string $username = '', 
        string $email = '', 
        string $phone = '', 
        string $type = 'user', 
        ?RegistrationInfo $reginfo = null, 
        bool $auto_login = false
    ):ArmorUser { 

        // Initialize
        $db = Di::get(DbInterface::class);

        // Validate profile
        $validator = new Validator($this);
        $validator->validate($uuid, $password, $username, $email, $phone, $type);

        // Get registration info, if needed
        if ($reginfo === null) { 
            $reginfo = new RegistrationInfo($this);
        }
        $reginfo->prepare();

        // Generate UUID, if needed
        if ($uuid == '') { 
            $uuid = Uuid::get($type);
        }

        // Create user model
        $user = new ArmorUser(
            uuid: $uuid, 
            password: $password == '' ? '' : Password::hash($password), 
            username: $username, 
            email: $email, 
            phone: $phone,
            type: $type, 
            is_pending: $this->policy->getCreateAsPending(), 
            two_factor_type: $this->policy->getDefaultTwoFactorType(), 
            two_factor_frequency: $this->policy->getDefaultTwoFactorFrequency(),  
            reginfo: $reginfo
        );

        // Add to db
        $db->beginTransaction();
        $db->insert('armor_users', $user);

        // Generate RSA key-pair
        if ($password != '') { 
            $rsa = Di::make(KeyManager::class);
            $rsa->generate($uuid, $password);
        }

        // Add user log
        $logger = Di::make(UserLog::class);
        $logger->add($user, 'created');

        // Commit db transaction
        $db->commit();

        // Verify e-mail, if needed
        $verify_email = $this->policy->getVerifyEmail();
        if (in_array($verify_email, ['require', 'optional']) && $email != '') { 
            $verifier = Di::make(VerifyEmail::class);
            $verifier->init($user);
        } elseif (in_array($verify_email, ['require_otp', 'optional_otp']) && $email != '') { 
            $verifier = Di::make(VerifyEmailOTP::class);
            $verifier->init($user);
        }

        // Check initial password
        if ($this->policy->getRequirePassword() == 'after_register' && $email != '') { 
            $verifier = Di::make(InitialPassword::class);
            $verifier->init($user);
        }

        // Verify phone, if needed
        $verify_phone = $this->policy->getVerifyPhone();
        if (in_array($verify_phone, ['require', 'optional']) && $phone != '') { 
            $verifier = Di::make(VerifyPhone::class);
            $verifier->init($user);
        }

        // Create auth session, if needed
        if ($auto_login === true) { 
            $manager = Di::make(SessionManager::class);
            $manager->create($user, 'ok', $password, true);
        }

        // Return
        return $user;
    }

    /**
     * Get count
     */
    public function getCount(string $type = '', bool $is_deleted = false):int
    {

        // Initialize
        $db = Di::get(DbInterface::class);
        $is_deleted = $is_deleted === true ? 1 : 0;

        // Get count
        if ($type != '') { 
            $count = $db->getField("SELECT count(*) FROM armor_users WHERE type = %s AND is_deleted = %i", $type, $is_deleted);
        } else { 
            $count = $db->getField("SELECT count(*) FROM armor_users WHERE is_deleted = %i", $is_deleted);
        }
        if ($count == '') { $count = 0; }

        // Return
        return (int) $count;
    }

    /**
     * Get user by uuid
     */
    public function getUuid(string $uuid, bool $is_deleted = false):ArmorUserInterface
    {

        // Initialize
        $adapter = Di::get(AdapterInterface::class);
        $db = Di::get(DbInterface::class);

        // Get user
        if (!$user = $adapter->getUuid($db, $uuid, $is_deleted)) { 
            throw new ArmorUuidNotExistsException("No user exists with the uuid $uuid");
        }

        // Return
        return $user;
    }

    /**
     * Get by username
     */
    public function getUser(string $username, string $type = 'user', bool $is_deleted = false, bool $throw_error = true):?ArmorUserInterface
    {

        // Initialize
        $db = Di::get(DbInterface::class);
        $adapter = Di::get(AdapterInterface::class);
        $username_col = $this->policy->getUsernameColumn();

        // Get UUID
        if (!$uuid = $db->getField("SELECT uuid FROM armor_users WHERE $username_col = %s AND type = %s AND is_deleted = %i", $username, $type, $is_deleted)) { 
            if ($throw_error === true) { 
                throw new ArmorUsernameNotExistsException("No user exists with the $username_col $username");
            }
            return null;
        }

        // Return
        return $adapter->getUuid($db, $uuid, $is_deleted);
    }

    /**
     * Remove uuid
     */
    public function removeUuid(string $uuid):bool
    {
        $db = Di::get(DbInterface::class);
        $stmt = $db->query("DELETE FROM armor_users WHERE uuid = %s AND is_deleted = false", $uuid);

        $num = $db->numRows($stmt);
        return $num > 0 ? true : false;
    }

    /**
     * Remove user
     */
    public function removeUser(string $username, string $type = 'user'):bool
    {

        // Initialize
        $db = Di::get(DbInterface::class);
        $username_col = $this->policy->getUsernameColumn();

        // Get user
        if (!$row = $db->getRow("SELECT * FROM armor_users WHERE $username_col = %s AND type = %s AND is_deleted = false", $username, $type)) { 
            return false;
        }

        // Delete
        $this->removeUuid($row['uuid']);
        return true;
    }

    /**
     * Find users
     */
    public function find(
        string $username = '', 
        string $email = '', 
        string $phone = '', 
        string $type = 'user',
        bool $is_deleted = false, 
        string $reg_country = '', 
        string $reg_province_code = '', 
        string $reg_province_name = '', 
        string $reg_city = '', 
        string $reg_ip = '', 
        ?DateTime $created_at_start = null, 
        ?DateTime $created_at_end = null
    ):array {

        // Start SQL
        $sql = "SELECT * FROM armor_users WHERE type = %s AND is_deleted = %i AND ";
        $args =[$type];
        $args[] = $is_deleted === true ? 1 : 0;

        // Add profile info
        if ($username != '') { $sql .= "username LIKE %ls AND "; $args[] = $username; }
        if ($email != '') { $sql .= "email LIKE %ls AND "; $args[] = $email; }
        if ($phone != '') { $sql .= "phone LIKE %ls AND "; $args[] = $phone; }
        if ($reg_country != '') { $sql .= "reg_country = %s AND "; $args[] = $reg_country; }
        if ($reg_province_code != '') { $sql .= "reg_province_iso_code = %s AND "; $args[] = $reg_province_code; }
        if ($reg_province_name != '') { $sql .= "reg_province_name LIKE %ls AND "; $args[] = $reg_province_name; }
        if ($reg_city != '') { $sql .= "reg_city LIKE %ls AND "; $args[] = $reg_city; } 
        if ($reg_ip != '') { $sql .= "reg_ip LIKE %ls AND "; $args[] = $reg_ip; }

        // Add start date
        if ($created_at_start !== null) { 
            $sql .= "created_at >= %s AND ";
            $args[] = date('Y-m-d H:i:s', $created_at_start->getTimestamp());
        }

        // Add end created at date
        if ($created_at_end !== null) { 
            $sql .= "created_at <= %s AND ";
            $args[] = date('Y-m-d H:i:s', $created_at_end->getTimestamp());
        }

        // Format SQL
        $sql = preg_replace("/ AND $/", " ORDER BY created_at", $sql);
        $db = Di::get(DbInterface::class);

        // Execute SQL
        $users = [];
        $rows = $db->query($sql, ...$args);
        foreach ($rows as $row) { 
            $users[] = $row;
        }

        // Return
        return $users;
    }

    /**
     * Purge all users
     */
    public function purge(string $type = ''):void
    {

        // Initialize
        $db = Di::get(DbInterface::class);
        $redis = Di::get(redis::class);
        $db->closeCursors();

        // Purge
        if ($type != '') { 
            $db->query("DELETE FROM armor_users WHERE type = %s", $type);
        $redis->del('armor:counter:' . $type);
        } else { 
            $db->query("DELETE FROM armor_users");

            // Delete redis counters
            $keys = $redis->keys('armor:counter:*');
            foreach ($keys as $key) { 
                $redis->del($key);
            }
        }

    }



}


