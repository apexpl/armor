
# ArmorUser::listIpAllow()

Retrive all authorized IP addresses / ranges the user may login with.

> `array ArmorUser::listIpAllow()`

**Parameters**

This method does not accept any parameters.

**Return Value**

Returns a one-dimensional array of all authorized IP addresses / ranges.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Get IPs
$ips = $user->listIpAllow();
print_r($ips);
~~~


