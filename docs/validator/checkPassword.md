
# Validator::checkPassword()

Validate a password, including the minimum password strength defined within the [ArmorPolicy Configuration](../armorpolicy.md).

> `bool Validator::checkPassword(string $password, bool $throw_error = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$password` | Yes | string | The plain text password to validate.
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
if (!$validator->checkPassword('my_screwt')) { 
    echo "This password is not valid\n";
}
~~~



