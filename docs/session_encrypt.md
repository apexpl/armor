
# Encrypt / Decrypt Data

Using the `encryptData()` and `decryptData()` methods you may encrypt data to a user.  Armor automatically generates a 4096 bit RSA keypair for each user, meaning any data encrypted to a user is encrypted to their unique RSA key, and can only be decrypted by that user.  This provides segregation of encrypted data, so if an attacker ever accesses the database, they will not have access to all encrypted data.


## Encrypt Data

You may encrypt data to a user via the `AuthSession::encryptData()` method, which accepts two parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$data` | Yes | string | The plain text data to encrypt.
`$include_admin` | No | bool | Whether or not to also encrypt the data to all administrators.  If administrators need to view this data via the admin panel for example, this must be set to true.  Defaults to true.

This method returns an integer being the id# of the encrypted data.  You will need to store this id# somewhere as it is required to decrypt the data.


## Decrypt Data

You may decrypt any previously encrypted data by passing the returned `$data_id` integer to the `decryptData()` method.  This will return either the plain text data, or null on failure.


## Example

~~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in.");
}

// Encrypt data
$data_id = $session->encryptData("the secret text");

// At a later data, decrypt the data
$data = $session->decryptData($data_id);
echo "The secret is: $data\n";
~~~









