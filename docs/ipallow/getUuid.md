
# IpAllow::getUuid()

Get list of allows IP addresses / ranges on user's account.

> `?array IpAllow::getUuid(string $uuid)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$uuid` | Yes | string | The UUID of the user to get IP addresses for.

**Return Value**

Returns a one-dimensional array of allowed IP addresses / ranges, or null if the user has none assigned to their account.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\IpAllow;


// Init Armor
$armor = new Armor();
$ipallow = new IpAllow($armor);

// Get IPs
if (!$ips = $ipallow->getUuid('u:326')) { 
    echo "There are no IPs on this user.\n";
} else { 
    print_r($ips);
}

~~~


