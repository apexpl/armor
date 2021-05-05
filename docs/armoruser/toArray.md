
# ArmorUser::toArray()

Returns an associative array of all user profile information.  Useful when rendering templates.

> `array ArmorUser::toArray()`

**Parameters**

This method does not accept any parameters.

**Return Value**

Returns an associative array contains all user profile information, and is useful when rendering templates with the user's profile within them.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Get profile
$vars = $user->toArray();
print_r($vars);
~~~


