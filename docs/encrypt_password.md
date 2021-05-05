
# Password Based Encryption

Sometimes you may wish to only encrypt data to a password instead of a user's RSA key.  This can be done with the `EncryptAES::toPassword()` and `DecryptAES::fromPassword()` methods as explained below.

## Encrypt Data

You may encrypt data to a password by calling the `Apex\Armor\AES\EncryptAES::toPassword()` method, which accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$data` | Yes | string | The data to encrypt.
`$password` | Yes | string | The password to encrypt data to.

This will return a string of encrypted data that you may store as desired.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\AES\EncryptAES;

// Init
$armor = new Armor();
$aes = new EncryptAES($armor);

// Encrypt data
$encdata = $aes->toPassword('some sensitive data', 'secret_password');

// Save $encdata for later
~~~


## Decrypt Data

You may decrypt previously encrypted data by calling the `Apex\Armor\AES\DecryptAES::fromPassword()` method, which accepts the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$encdata` | Yes | string | The encrypted data as returned by the `EncryptAES::toPassword()` method.
`$password` | Yes | string | The encryption password.

If the password is correct, this will return the decrypted data and will return null otherwise.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\AES\DecryptAES;

// Init
$armor = new Armor();
$aes = new DecryptAES($armor);

// Decrypt
if (!$data = $aes->fromPassword($encdata, 'secret_password')) { 
    die("Invalid encryption password");
}

echo "Decryption successful, text is: $data\n";
~~~



