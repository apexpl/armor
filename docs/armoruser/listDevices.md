
# ArmorUser::listDevices()

Retrieve all devices registered to the user.

> `array ArmorUser::listDevices(string $type = '')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | No | string | The type of device to retrieve, supported values are: `android, ios, ps`.  Leave blank to retrieve all devices, defaults to blank. 

**Return Value**

Returns a one-dimensional array, each element being an associative array of one device.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Get devices
$devices = $user->listDevices();
print_r($devices);
~~~


