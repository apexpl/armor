
# PendingPasswords::deleteAll()

Deletes all pending password changes from the database.

> `int PendingPasswords::deleteAll()`

**Parameters**

This method does not accept any parameters.

**Return Value**

Returns the number of deleted pending password changes.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Add pending password change// Delete all
$num = $pending->deleteAll();
echo "Total of $num entries deleted.\n";
~~~

