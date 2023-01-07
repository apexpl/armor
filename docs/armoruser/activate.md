
# ArmorUser::activate()

Activates a user who was previously deactivated or is pending.

> `void ArmorUser::activate()`

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

// Activate
$user->activate();
~~~


