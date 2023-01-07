
# Devices::getUuid()

Get details on all devices assigned to a user.

> `array Devices::getUuid(string $uuid, string $type = '')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to retrieve devices of.
`$type` | No | string | Optional type of device to retrieve, supported values are: `pc, ios, andriod`.  If left blank, will return devices of all types.


**Return Value**

Returns an array with each element being an associative array of device details.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Get devices 
$rows = $devices->getUuid('u:321');

print_r($rows);
~~~


