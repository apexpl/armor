
# PendingPasswords::processUuid()

Process and complete pending password changes for a specific user.

> `bool PendingPasswords::processUuid(string $uuid, string $master_password)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |-------------
`$uuid` | Yes | string | The UUID of the user to process password change for. 
`$master_password` | Yes | string | The master encryption password defined during installation of Armor.

**Return Value**

Returns a boolean as to whether or not a pending password change existsed for the user and was process or not.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Process
if (!$pending->processUuid('u:821', 'my_master_password')) { 
    echo "No password change exists for user\n";
}
~~~



