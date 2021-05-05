
# Armor::removeUuid()

Permanently remove user from database by UUID.

> `bool Armor::removeUuid(string $uuid, bool $is_deleted = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID (universal unique identifier) of the user you wish to remove.
`$is_deleted` | No | bool | Whether or not to removee user who was previously marked as deleted.  Defaults to false.


**Return Value**

Returns a boolean as to whether or not the user was successfully found and removed.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Remove user
$armor = new Armor();
$ok = $armor->removeUuid('U:384');
~~~



