
# Armor::getUuid()

Load user by UUID.

> `ArmorUserInterface Armor::getUuid(string $uuid, bool $is_deleted = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID (universal unique identifier) of the user you wish to load. 
`$is_deleted` | No | bool | Whether or not to retrieve user who was previously deleted.  Defaults to false.


**Return Value**

Returns an instance of the [ArmorUser class](../armoruser.md) of the user.  Will throw a `ArmorUuidNotExistsException` if the UUID specified does not exist.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Load user
$armor = new Armor();
$user = $armor->getUuid('U:384');

// $user is now an instance of ArmorUser class.
~~~



