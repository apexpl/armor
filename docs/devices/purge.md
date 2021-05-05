
# Devices::purge()

Delete all devices from the database.

> `void Devices::purge(string $type = '')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | No | string | Optional type of device to delete, supported values are: `pc, ios, android`.  If left blank, will delete all devices regardless of type.


**Return Value**

This method does not return anything.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Purge
$devices->purge();
~~~


