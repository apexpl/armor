
# IpAllow::deleteUuid()

Delete all IP addresses / ranges from a user's account.

> `int IpAllow::deleteUuid(string $uuid)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to delete IP address from.


**Return Value**

Returns number of IP addresses / ranges deleted from the database.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\IpAllow;


// Init Armor
$armor = new Armor();
$ipallow = new IpAllow($armor);

// Delete
$num = $ipallow->deleteUuid('u:361');
echo "Deleted $num entries\n":
~~~



