
# PendingPasswords::getCount()

Get number of pending password changes awaiting processing.

> `int PendingPasswords::getCount()`

**Parameters**

This method does not accept any parameters.

**Return Value**

Returns number of pending password changes awaiting procssing.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Get count
$num = $pending->getCount();
echo "Total of $num pending password changes.\n";
~~~


