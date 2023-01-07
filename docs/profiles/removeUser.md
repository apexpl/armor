
# Armor::removeUser()

Permanently remove a user from the database by username.  This does not necessarily mean the username column, and instead is dependant on what is defined as the username column within the [ArmorPolicy configuration](../armorpolicy.md).

> `bool Armor::removeUser(string $username, string $type = 'user', bool $is_deleted = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$username` | Yes | string | The username, e-mail address or phone number of the username to remove, dependant on what username column is defined within the [ArmorPolicy configuration](../armorpolicy.md).
`$type` | No | string | The type of user to remove, defaults to "user".
`$is_deleted` | No | bool | Whether or not to removee user who was previously marked as deleted.  Defaults to false.


**Return Value**

Returns a boolean as to whether or not a user was found and removed.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Remove user
$armor = new Armor();
$ok = $armor->removeUser('jsmith@gmail.com');
~~~



