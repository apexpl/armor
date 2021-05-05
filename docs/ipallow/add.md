
# IpAllow::add()

Add new authorized IP address / range to user.

> `void IpAllow::add(string $uuid, string $ip)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to add IP to.
$ip | Yes | string | A full or partial IP address which is allowed to login.


**Return Value**

This method does not return anything.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\IpAllow;


// Init Armor
$armor = new Armor();
$ipallow = new IpAllow($armor);

// Add IP
$ipallow->add('u:488', '24.134.82');
~~~


