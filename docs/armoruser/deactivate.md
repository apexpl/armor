
# ArmorUser::deactivate()

Deactivates a user, prohibiting them for logging in.

> `void ArmorUser::deactivate()`

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

// Deactivate
$user->deactivate();
~~~


