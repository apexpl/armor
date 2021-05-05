
# IP Restrictions

Armor allows users to restrict logins to a defined set of IP addresses / ranges, which is handled via the `Apex\Armor\User\Extra\IpAllow` class.  

If the "enable_ipcheck" setting is set to true within the [ArmorPolicy Configuration](armorpolicy.md), and the user has one or more IP addresses registered within their restriction list, the software will enforce the IP restrictions and only allow logins from defined IP addresses / ranges.


## Available Methods

The `Apex\Armor\User\Extra\IpAllow` class contains the following methods:

* [add()](./ipallow/add.md)
* [check()](./ipallow/check.md)
* [getUuid()](./ipallow/getUuid.md)
* [delete()](./ipallow/delete.md)
* [deleteUuid()](./ipallow/deleteUuid.md)
* [purge()](./ipallow/purge.md)

