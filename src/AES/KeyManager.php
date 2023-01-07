<?php
declare(strict_types = 1);

namespace Apex\Armor\AES;

use Apex\Armor\Armor;
use Apex\Armor\AES\EncryptAES;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Armor\Exceptions\{ArmorDuplicateKeyException, ArmorInvalidKeyPasswordException, ArmorUuidNotExistsException};


/**
 * RSA key management
 */
class KeyManager
{

    /**
     * Constructor
     */
    public function __construct(
        private Armor $armor
    ) { 
        $this->db = Di::get(DbInterface::class);
    }

    /**
     * Generate master key
     */
    public function generateMaster(string $master_password):void
    {

        // Check if master uuid exists
        if ($row = $this->db->getIdRow('armor_users', 'master')) { 
            throw new ArmorProfileValidationException("Unable to generate master RSA key pair as a user with the uuid 'master' already exists.");
        }

        // Begin transaction
        $this->db->beginTransaction();

        // Add user
        $this->db->insert('armor_users', [
            'uuid' => 'master', 
            'type' => 'master'
        ]);

        // Generate RSA key
        $this->generate('master', $master_password);
        $this->db->commit();
    }

    /**
     * Generate key-pair
     */
    public function generate(string $uuid, string $password):string
    {

        // Check uuid for duplicate
        if ($id = $this->db->getField("SELECT id FROM armor_keys WHERE uuid = %s AND algo = 'rsa'", $uuid)) { 
            throw new ArmorDuplicateKeyException("RSA key-pair already exists for the UUID '$uuid', and must first be deleted via the delete() method before generating a new key-pair.");
        }
        // Set config args
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA 
        );

        // Generate private key
        $res = openssl_pkey_new($config);

        // Export key-pair
        openssl_pkey_export($res, $privkey);
        $pubkey = openssl_pkey_get_details($res);

        // Encrypt to master, if needed
        $master_id = 0;
        if ($id = $this->db->getField("SELECT id FROM armor_keys WHERE uuid = 'master' AND algo = 'rsa'")) { 
            $enc = Di::make(EncryptAES::class);
            $master_id = $enc->toUuids($privkey, ['master']);
        }

        // Encrypt private key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $privkey = openssl_encrypt($privkey, 'aes-256-cbc', hash('sha256', $password), 0, $iv);

        // Add to database
        $this->db->insert('armor_keys', [
            'uuid' => $uuid, 
            'algo' => 'rsa', 
            'master_id' => $master_id,
            'iv' => base64_encode($iv),  
            'public_key' => $pubkey['key'], 
            'private_key' => $privkey]
        );

        // Return
        return $pubkey['key'];
    }

    /**
     * Get public key
     */
    public function getPublic(string $uuid):?string
    {

        // Check for row
        if (!$pubkey = $this->db->getField("SELECT public_key FROM armor_keys WHERE uuid = %s AND algo = 'rsa'", $uuid)) { 
            return null;
        }

        // Return
        return $pubkey;
    }

    /**
     * Get private key
     */
    public function getPrivate(string $uuid, string $password, bool $is_ascii = true):string
    {

        // Check database
        if (!$row = $this->db->getRow("SELECT * FROM armor_keys WHERE uuid = %s AND algo = 'rsa'", $uuid)) { 
            return null;
        }

        // Hash password, if needed
        if ($is_ascii === true) { 
            $password = hash('sha256', $password);
        }

        // Decrypt private key
        if (!$privkey = openssl_decrypt($row['private_key'], 'aes-256-cbc', $password, 0, base64_decode($row['iv']))) { 
            throw new ArmorInvalidKeyPasswordException("Unable to retrive private RSA key for uuid '$uuid' as the password provided is incorrect.");
        }

        // Return
        return $privkey;
    }

    /**
     * Delete key
     */
    public function delete(string $uuid):bool
    {

        // Delete
        $stmt = $this->db->query("DELETE FROM armor_keys WHERE uuid = %s AND algo = 'rsa'", $uuid);
        $this->db->query("DELETE FROM armor_data_index WHERE uuid = %s", $uuid);
        $num = $this->db->numRows($stmt);

        // Return
        return $num > 0 ? true : false;
    }

    /**
     * Change password
     */
    public function changePassword(string $uuid, string $old_password, string $new_password, bool $is_ascii = true):void
    {

        // Get private key
        $privkey = $this->getPrivate($uuid, $old_password, $is_ascii);

        // Hash password, if needed
        if ($is_ascii === true) { 
            $new_password = hash('sha256', $new_password);
        }

        // Encrypt private key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $privkey = openssl_encrypt($privkey, 'aes-256-cbc', $new_password, 0, $iv);

        // Update database
        $this->db->update('armor_keys', [
            'iv' => base64_encode($iv), 
            'private_key' => $privkey
        ], "uuid = %s AND algo = 'rsa'", $uuid);
    }

    /**
     * Change password with master
     */
    public function changePasswordWithMaster(string $uuid, string $new_password, string $master_password, bool $is_ascii = true):void
    {

        // Get master id
        if (!$master_id = $this->db->getField("SELECT master_id FROM armor_keys WHERE uuid = %s AND algo = 'rsa'", $uuid)) { 
            throw new ArmorUuidNotExistsException("The uuid '$uuid' does not have a RSA key-pair registered on their account.");
        }

        // Decrypt
        $dclient = Di::make(DecryptAES::class);
        $privkey = $dclient->fromUuid((int) $master_id, 'master', $master_password);

        // Hash password, if needed
        if ($is_ascii === true) { 
            $new_password = hash('sha256', $new_password);
        }

        // Encrypt private key
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $privkey = openssl_encrypt($privkey, 'aes-256-cbc', $new_password, 0, $iv);

        // Update database
        $this->db->update('armor_keys', [
            'iv' => base64_encode($iv), 
            'private_key' => $privkey
        ], "uuid = %s AND algo = 'rsa'", $uuid);
    }

}



