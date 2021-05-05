
# Devices::add()

Add new device to user.

> `string Devices::add(string $uuid, string $device_id = '', string $type = 'pc')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to add device to.
`$device_id` | No | string | Used for mobile apps, and is the device id# of the Android or iOS device.
`$type` | No | string | The type of device, and may be either: `pc, andriod, ios`.  Defaults to "pc".


**Return Value**

Returns the device id that was added.  In cases where the `$type` is "pc", this will be the value of the cookie that was set in the user's web browser to recognize the device during future logins.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Add iOS device for future Firebase messages
$devices->add('u:326', '2b6f0cc904d137be2e1730235f5664094b831186', 'ios');

// Add user's current web browser to be remembered for future logins
$devices->add('u:321');
~~~


