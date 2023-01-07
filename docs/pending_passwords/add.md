
# PendingPasswords::add()

Adds new pending password change to the queue.  This should never need to be executed by you, and is handled automatically during the [ArmorUser::updatePassword()](../armoruser/updatePassword.md) method.

> `void PendingPasswords::add(ArmorUserInterface $user, string $new_password)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$user` | Yes | ArmorUserInterface | The user that a password change is being conducted on.  See the [ArmorUser Class](../armoruser.md) for details.
`$new_passwrd` | Yes | string | The plain text password to update the user's account with.


**Return Value**

This method does not return anything.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Add pending password change
$pending->add('u:test', 'my_new_password');

~~~


