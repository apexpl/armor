
# PendingPasswords::deleteUuid()

Deletes any pending password changes associated with a specific user.

> `bool PendingPasswords::deleteUuid(string $uuid)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to delete password changes of.


**Return Value**

Returns a boolean as to whether or not any password changes were found and deleted.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\PendingPasswords;


// Init Armor
$armor = new Armor();
$pending = new PendingPasswords($armor);

// Delete
if (!$pending->delete('u:348')) { 
    echo "No password changes on user account.\n";
} else { 
    echo "Deleted\n";
}

~~~


