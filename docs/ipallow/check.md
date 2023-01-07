
# IpAllow::check()

Check whether or not user is allowed to login with specified IP address.

> `bool IpAllow::check(string $uuid, string $ip = '')`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to check IP access for.
$ip | No | string | The current IP address to check.  If left blank, the current IP address of the session will be used.


**Return Value**

Returns a bool whether or not the user is authorized to continue.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\IpAllow;


// Init Armor
$armor = new Armor();
$ipallow = new IpAllow($armor);

// Check
if (!$ipallow->check('u:391')) { 
    die("You can not login with this IP");
}
~~~


