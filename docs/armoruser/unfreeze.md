
# ArmorUser::unfreeze()

Marks a user as unfrozen, allowing them to login again.  This should never have to be executed, and is used internally for brute force prevention.

> `void ArmorUser::unfreeze()`

**Parameters**

This method does not accept any parameters.


**Return Value**

This method does not return anything.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Unfreeze
$user->unfreeze();
~~~


