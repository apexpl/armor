
# Validator

The `Apex\Armor\User\Validator` class allows you to validate profile information before creating / updating a user.  You may either validate the full profile information at once, or only the items you would prefer.  This class validates on both, base format plus based on the [ArmorPolicy Configuration](armorpolicy.md) for things such as duplicates, minimum username length, et al.

## Validate Full Profile

All profile information is validated before creating a user, but you may wish to validate beforehand to properly render any validation errors.  The recommended way is to validate all profile information via the `Validate::validate()` method, and catch any `ArmorProfileValidationException` errors thrown so as to obtain the exact validation error.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;
use Apex\Armor\Exceptions\ArmorProfileValidationException;

// Init Armor
$armor = new Armor();

// Set variables
$uuid = '';   // auto-generate uuid
$password = 'secret_pass';
$username = 'jsmith';
$email = 'jsmith@domain.com';
$phone = '14165551234';

// Validate profile
try {
    $validator = new Validator($armor);
    $validator->validate($uuid, $password, $username, $email, $phone);
} catch (ArmorProfileValidationException $e) { 
    echo "Validation error: " . $e->getMessage();
}

// Create user
$user = $armor->createUser($uuid, $password, $username, $email, $Phone);
~~~


## Available Methods

The following methods are available within the `Validator` class:

* [validate()](./validator/validate.md)
* [checkUuid()](./validator/checkUuid.md)
* [checkUsername()](./validator/checkUsername.md)
* [checkPassword()](./validator/checkPassword.md)
* [checkEmail()](./validator/checkEmail.md)
* [checkPhone()](./validator/checkPhone.md)


