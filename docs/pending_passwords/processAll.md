
# PendingPasswords::processAll()

Process and complete all pending password changes.

> `void PendingPasswords::processAll(string $master_password)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$master_password` | Yes | string | The master encryption password defined during installation of Armor.

**Return Value**

This method does not return any value, but will throw an exception if any issues occur.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Process
$pending->processAll('the_master_password');
~~~

