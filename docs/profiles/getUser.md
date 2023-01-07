
# Armor::getUser()

Load user by username.  This does not necessarily mean the username column, and instead is dependant on what is defined as the username column within the [ArmorPolicy configuration](../armorpolicy.md).

> `ArmorUserInterface Armor::getUser(string $username, string $type = 'user', bool $is_deleted = false, bool $throw_error = true)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$username` | Yes | string | The username, e-mail address or phone number of the username to load, dependant on what username column is defined within the [ArmorPolicy configuration](../armorpolicy.md).
`$type` | No | string | The type of user to retrieve, defaults to "user".
`$is_deleted` | No | bool | Whether or not to retrieve user who was previously deleted.  Defaults to false.
`$throw_error` | No | bool | If true and username does not exist, will throw a `ArmorUsernameNotExistsException`.  Otherwise, if false and username does not exist will simply return null. 


**Return Value**

Returns an instance of the [ArmorUser class](../armoruser.md) of the user.  Will throw a `ArmorUsernameNotExistsException` if the username specified does not exist.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Load user
$armor = new Armor();
$user = $armor->getUser('jsmith@gmail.com');

// $user is now an instance of ArmorUser class.
~~~



