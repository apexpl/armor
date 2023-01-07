
# IpAllow::delete()

Delete a specific IP address / range from a user.

> `bool IpAllow::delete(string $uuid, string $ip)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to delete IP address from.
$ip | Yes | string | The IP address / range to delete.


**Return Value**

Returns whether or not the IP address was deleted, or false if it never existed on the user's account.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\IpAllow;


// Init Armor
$armor = new Armor();
$ipallow = new IpAllow($armor);

// Delete
$ipallow->delete('u:361', '24.178');
~~~




