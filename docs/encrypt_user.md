
# User Based Encryption

A 4096 bit RSA keypair is automatically generated for each user upon creation, with the private key being encrypted via AES256 to the user's password.  This provides segregation of encrypted data, meaning if an attacker ever gains access to the database, they will not have access to all encrypted data as all data is encrypted to each user's unique RSA key.


## Encrypt to Users
You may encrypt data to one or more user accounts by calling the `Apex\Armor\AES\EncryptAES::toUuids()` method, which accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$data` | Yes | string | The data to encrypt.
`$uuids` | Yes | array | An array of UUIDs to encrypt the data to.
`$include_admin` | No | bool | Whether or not to also encrypt to the data to all users with type of "admin".  If false, administrators will not be able to view this data from within the administration panel.  Defaults to false.

This will return an integer being the unique id# of the encrypted data.  This id# must be stored somewhere, as it is required later to decrypt the data.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\AES\EncryptAES;

// Init
$armor = new Armor();
$aes = new EncryptAES($armor);

// Encrypt 
$data_id = $aes->toUuids('some sensitive data', ['u:311', 'u:85'], true);

// Store $data_id somewhere for later when you wish to decrypt the data
~~~


## Decrypt by User

Assuming the user's password is available, you may decrypt previously encrypted data by calling the `Apex\Armor\AES\DecryptAES::fromUuid()` method.  However, please note it's recommended you use the [AuthSession::decrypt()](./session_encrypt.md) method for this which is available from a user's authenticated session.

The `fromUuid()` method accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$data_id` | Yes | int | The data id# that was returned when the data was encrypted via `EncryptAES::toUuids()` method.
`$uuid` | Yes | string | The UUID of the user decryption the data.
`$password` | Yes | string | The plain text password of the user.
`$is_ascii~ | No | bool | Whether or not the password is in plain text, or is already hashed via SHA256.  If you are calling this method, this should be true which is its default.

Assuming the data id# exists, is encrypted to the UUID provided and the password is correct, this will return the decrypted data.  Otherwise, it will either return null or throw an exception.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\AES\DecryptAES;

// Init
$armor = new Armor();
$aes = new DecryptAES($armor);

// Decrypt
$data_id = 511;
if (!$text = $aes->fromUuid($data_id, 'u:311', 'user_password')) { 
    die("Unable to decrypt");
}

echo "Decrypted, text is: $text\n";
~~~

