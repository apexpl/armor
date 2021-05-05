
# Validator::checkUsername()

Validate a username.

> `bool Validator::checkUsername(string $username, string $type = 'user', bool $throw_error = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$username` | Yes | string | The username to validate.
`$type` | No | string | Applicable if using multiple user groups, and is the type of user being validated.  Defaults to "user".
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
if (!$validator->checkUsername('my_user')) { 
    echo "This username is not valid\n";
}
~~~



