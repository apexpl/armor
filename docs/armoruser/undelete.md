
# ArmorUser::undelete()

Marks a user as not deleted, who was previously marked as deleted.

> `void ArmorUser::undelete()`

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

// undelete
$user->undelete();
~~~


