<?php
declare(strict_types = 1);

namespace Apex\Armor\AES;

use Apex\Armor\Armor;
use Apex\Armor\AES\KeyManager;
use Apex\Container\Di;
use Apex\Db\Interfaces\DbInterface;
use Apex\Armor\Exceptions\{ArmorDataNotExistsException, ArmorUuidNotExistsException, ArmorRsaDecryptErrorException, ArmorInvalidKeyPasswordException};

/**
 * Decrypt AES data
 */
class DecryptAES
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
     * Decrypt by UUID
     */
    public function fromUuid(int $data_id, string $uuid, string $password, bool $is_ascii = true):?string
    {

        // Get encdata
        if (!$row = $this->db->getRow("SELECT * FROM armor_encdata WHERE data_id = %i AND uuid = %s", $data_id, $uuid)) { 
            throw new ArmorDataNotExistsException("Encrypted data does not exist, id# $data_id, or was never encrypted to uuid '$uuid'");
        }

        // Get private key
        $rsa = Di::make(KeyManager::class);
        $privkey = $rsa->getPrivate($uuid, $password, $is_ascii);
        $privkey = openssl_pkey_get_private($privkey);

        // Decrypt the index data
        if (!openssl_private_decrypt(base64_decode($row['keydata']), $index_data, $privkey, OPENSSL_PKCS1_OAEP_PADDING)) { 
            throw new ArmorRsaDecryptErrorException("Unable to decrypt file header via RSA.  Error: " . openssl_error_string());
        }
        list($iv, $encpass) = explode("\r\n", $index_data, 2);

        // Decrypt data
        if (!$data = openssl_decrypt($row['encdata'], 'aes-256-cbc', base64_decode($encpass), 0, base64_decode($iv))) { 
            throw new ArmorRsaDecryptErrorException("Unable to decrypt encrypted data via AES.  Error: " . openssl_error_string());
        }

        // Return
        return $data;
    }

    /**
     * From password
     */
    public function fromPassword(string $encdata, string $password):?string
    {

        // Decrypt
        list($iv, $encdata) = explode("\r\n", base64_decode($encdata), 2);
        if (!$data = openssl_decrypt($encdata, 'aes-256-cbc', hash('sha256', $password), OPENSSL_RAW_DATA, $iv)) { 
            throw new ArmorInvalidKeyPasswordException("Unable to decrypt data, as invalid password was supplied.");
        }

        // Return
        return $data;
    }

}


