
# Validator::checkEmail()

Validate an e-mail address.

> `bool Validator::checkEmail(string $email, string $type = 'user', bool $throw_error = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$email` | Yes | string | The e-mail address to validate.
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
if (!$validator->checkEmail('jsmith@domain.com')) { 
    echo "This e-amil address is not valid\n";
}
~~~



