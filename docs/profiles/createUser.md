
# Armor::createUser()

Creates a new user.  All parameters are optional, and dependant on your [ArmorPolicy configuration](../armorpolicy.md).  If no UUID is specified, one will be auto-generated.

> `ArmorUser Armor::createUser(string $uuid = '', string $password = '', string $username = '', string $email = '', string $phone = '', string $type = 'user', ?registrationInfo $reginfo = null, bool $auto_login = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | No | string | The UUID (universal unique identifier) of the user, any alpha-numeric string maximum 30 characters in length.  If a UUID is not supplied, one will be auto-generated.
`$password` | No | string | The plain text password of the user.
`$username` | No | string | Username of the user.
`$email` | No | string | E-mail address of the user.
`$phone` | No | string | Phone number of the user.
`$type` | No | string | The type of user, defaults to "user".  Only applicable if using multiple user types (eg. user, admin, apidev, staff, et al).
`$reginfo` | No | RegistrationInfo | Base registration info including IP address, user agent, and geo IP location information.  If not defined, will be auto-generated based on user's session information.
`$auto_login` | No | bool | Whether or not to automatically login user and create auth session after creation.


**Return Value**

Returns an instance of the [ArmorUser class](../armoruser.md) of the newly created user.

Please note, this method may also throw a `ArmorProfileValidationException` if any invalid or duplicate profile items are specified.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;
use Apex\Armor\Exceptions\ArmorProfileValidationException;

// Init Armor
$armor = new Armor();

// Set variables
$username = 'jsmith';
$password = 'password12345';
$email = 'jsmith@gmail.com';
$phone = '14165551234';

// Validate profile
try {
    $validator = new Validator($armor);
    $validator->validate('', $password, $username, $email, $phone);
} catch (ArmorProfileValidationException $e) {
    // Display template giving $e->getMessage() validation error.
}

// Create user
$user = $armor->createUser('', $password, $username, $email, $phone);

/**
 * $user is now instance of ArmorUser class.
 * 
 * Get the UUID of new user to insert into your own database table(s).
 */
$uuid = $user->getUuid();
~~~


