
# Devices::get()

Get details on a specific device.

> `?array Devices::get(string $device_id)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$device_id` | Yes | string | The device id# to look up.


**Return Value**

Returns an array containing all device information, or null if the device id# does not exist.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Get device 
if (!$vars = $devices->get('2b6f0cc904d137be2e1730235f5664094b831186')) { 
    die("No device exists with that id#");
}

print_r($vars);
~~~


