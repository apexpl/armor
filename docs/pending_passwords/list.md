
# PendingPasswords::list()

Retrieves an array of all pending password changes awaiting processing.

> `array PendingPasswords::list()`

**Parameters**

This method does not accept any parameters.

**Return Value**

Returns a one-dimensional array, with each element being an associative array that represents one pending password change.  The associative array has the following elements:

Variable | Type | Description
------------- |------------- |------------- 
`id` | int | The unique id# of the pending password change.
`user` | ArmorUserInterface | The object instance of the user who requested the password change.  See the [ArmorUser Class](../armoruser.md) page for details.
`created_at` | DateTime | The time the pending password change was created.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Get pending changes
$changes = $pending->list();
foreach ($changes as $row) { 
    echo "Pending change for username: " . $row['user']->getUsername() . " -- e-mail: " . $row['user']->getEmail() . "\n";
}
~~~


