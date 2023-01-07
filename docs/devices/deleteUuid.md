
# Devices::deleteUuid()

Delete all devices assigned to a specific user.

> `int Devices::deleteUuid(string $uuid)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to delete all devices from.


**Return Value**

Returns the number of devices deleted from the database.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\Devices;


// Init Armor
$armor = new Armor();
$devices = new Devices($armor);

// Get devices 
$num = $devices->deleteUuid('u:321');

echo "Deleted total of $num devices.\n";
~~~


