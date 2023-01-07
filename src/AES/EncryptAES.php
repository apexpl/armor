<?php
declare(strict_types = 1);

namespace Apex\Armor\AES;

use Apex\Armor\Armor;
use Apex\Armor\AES\KeyManager;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Armor\Exceptions\{ArmorUuidNotExistsException, ArmorRsaEncryptErrorException};

/**
 * Encrypt data to a user.
 */
class EncryptAES
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
     * Encrypt to user
     */
    public function toUuids(string $data, array $uuids, bool $include_admin = false):int
    {

        // Include admins, if needed
        if ($include_admin === true) { 
            $admins = $this->db->getColumn("SELECT uuid FROM armor_users WHERE type = 'admin' AND is_deleted = false");
            foreach ($admins as $admin_uuid) { 
                if (in_array($admin_uuid, $uuids)) { 
                    continue;
                }
                $uuids[] = $admin_uuid;
            }
        }

        // Get random password, and initialization vector
        $data_pass = openssl_random_pseudo_bytes(32);
        $data_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        // Encrypt data
        $encdata = openssl_encrypt($data, 'aes-256-cbc', $data_pass, 0, $data_iv);
        $index_data = base64_encode($data_iv) . "\r\n" . base64_encode($data_pass);

        // Begin transaction
        $this->db->beginTransaction();

        // Add to database
        $this->db->insert('armor_data', [
            'encdata' => $encdata
        ]);
        $data_id = $this->db->insertId();

        // Add UUIDs to database
        $rsa = Di::make(KeyManager::class);
        foreach ($uuids as $uuid) {

            // Get public key
            if (!$pubkey = $rsa->getPublic($uuid)) {
                throw new ArmorUuidNotExistsException("Unable to encrypt data as UUID '$uuid' does not exist.");
            }
            $pubkey = openssl_pkey_get_public($pubkey);

            // Encrypt password
            if (!openssl_public_encrypt($index_data, $index_enc, $pubkey, OPENSSL_PKCS1_OAEP_PADDING)) { 
                throw new ArmorRsaEncryptErrorException("Unable to encrypt via RSA to uuid '$uuid'.  Error: " . openssl_error_string());
            }

            // Add to db
            $this->db->insert('armor_data_index', [
                'data_id' => $data_id, 
                'uuid' => $uuid, 
                'keydata' => base64_encode($index_enc)
            ]);
        }

        // Commit, and return
        $this->db->commit();
        return $data_id;
    }

    /**
     * Encrypt to password
     */
    public function toPassword(string $data, string $password):string
    {

        // Encrypt
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encdata = openssl_encrypt($data, 'aes-256-cbc', hash('sha256', $password), OPENSSL_RAW_DATA, $iv);

        // Return
        return base64_encode($iv . "\r\n" . $encdata);
    }

}


