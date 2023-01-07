
# IpAllow::purge()

Delete all IP addresses from all users.

> `int IpAllow::purge()`

**Parameters**

This method does not accept any parameters.

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
$num = $ipallow->purge();
echo "Deleted $num entries\n":
~~~



