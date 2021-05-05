
# ArmorUser::delete()

Marks a user as deleted, hence they will not be able to login, or appear in any searches or other retrievals from the database.  If you wish to permanently remove a user from the database, please see the [Remove / Purge Users](../profiles_remove.md) page of the documentation.

> `void ArmorUser::delete()`

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

// Delete
$user->delete();
~~~


