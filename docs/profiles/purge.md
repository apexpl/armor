
# Armor::purge()

Permanently delete all users from the database.

> `void Armor::purge(string $type = '')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | No | string | If defined, will only delete all users of the specified type.  Otherwise, will delete all users of all types.

**Return Value**

This method does not return any value.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Purge al users
$armor = new Armor();
$armor->purge();
~~~


