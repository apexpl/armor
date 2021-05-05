
# Validator::checkUuid()

Validate a UUID, and simply checks for a duplicate.  Every user must have a unique UUID regardless of user type.

> `bool Validator::checkUuid(string $uuid, bool $throw_error = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | No | string | The uuid to validate.
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
if (!$validator->checkUuid('u:388')) { 
    echo "This UUID is now valid\n":
}
~~~



