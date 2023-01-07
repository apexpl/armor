
# Validator::validate()

Validate all profile information at once.  All parameters are optional, and this will only validate those parameters that are defined.

> `bool Validator::validate(string $uuid = '', string $password = '', string $username = '', string $email = '', string $phone = '', string $type = 'user', bool $throw_error = true)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | No | string | The uuid to validate.
`$password` | No | string | Plain text password to validate.
`$username` | No | string | Username to validate.
`$email` | No | string | E-mail address to validate.
`$phone` | No | string | Phone number to validate.
`$type` | No | string | Applicable if using multiple user groups, and is the type of user to validate.  Defaults to "user".
`$throw_error` | No | bool | Whether or not to throw an exception upon validation error.  If false, will only return a false boolean, otherwise will throw an `ArmorProfileValidationException` error.


**Return Value**

Returns a boolean as to whether or not the profile information is valid.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;
use Apex\Armor\Exceptions\ArmorProfileValidationException;

// Init Armor
$armor = new Armor();
$validator = new Validator($armor);

// Validate
try {
    $validator->validate('', 'secret_pass', 'jsmith', 'jsmith@domain.com');
} catch (ArmorProfileValidationException $e)
    echo "Invalid profile information, " . $e->getMessage();
}

~~~

// $user is now an instance of Armo

