
# Devices

Armor allows multiple devices to be stored against users for both, the "remember me" feature which helps determine whether or not two factor authentication is required upon login, and for storing device IDs for mobile apps / Firebase messages.  All devices are managed through the `Apex\Armor\User\Extra\Devices` class.

Upon successfully logging in, if the "remember me" box is checked, a cookie will be set for the number of days defined within the "remember_device_days" setting of the [ArmorPolicy Configuration](armorpolicy.md).  Upon subsequent logins this cookie will be identified and two factor authentication will only be required if the frequency is set to "new_device".


## Available Methods

The `Apex\Armor\User\Extra\Devices` class contains the following methods:


* [add()](./devices/add.md)
* [get()](./devices/get.md)
* [getUuid()](./devices/getUuid.md)
* [delete()](./devices/delete.md)
* [deleteUuid()](./devices/deleteUuid.md)
* [purge()](./devices/purge.md)




