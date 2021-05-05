
# Devices::delete()

Delete a single device.

> `bool Devices::delete(string $device_id)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$device_id` | Yes | string | The device id# to delete.


**Return Value**

Returns a boolean as to whether or not the device was successfully deleted, or not found in the database.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Delete device 
if (!$devices->delete('2b6f0cc904d137be2e1730235f5664094b831186')) { 
    die("No device exists with that id#");
}
~~~


