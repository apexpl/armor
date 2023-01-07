
# Pending Password Changes

Due to the 4096 bit RSA keypair that is automatically generated for each user, it's not possible to simply overwrite a user's password.  Instead, their private RSA key must be decrypted via AES256, then encrypted again to their new password, otherwise all data encrypted to their account will be permanently lost.  

In instances where a user loses their password, has encrypted data assigned to their account, and assuming you defined a master encryption key upon installation, the password changes will be added to a pending queue.  This queue can be processed and password successfully updated via the `Apex\Armor\User\Extra\PendingPasswords` class.

When a new pending password change is queued, the [AdapterInterface::pendingPasswordChange()](./adapter/pendingPasswordChange.md) method is called, which should notify the administrator that there is a pending password change awaiting processing.


## Available Methods

The `Apex\Armor\User\Extra\PendingPassword` class contains the following methods, which explain how to list and process the pending password changes:

* [add()](./pending_passwords/add.md)
* [deleteAll()](./pending_passwords/deleteAll.md)
* [deleteUuid()](./pending_passwords/deleteUuid.md)
* [getCount()](./pending_passwords/getCount.md)
* [list()](./pending_passwords/list.md)
* [processAll()](./pending_passwords/processAll.md)
* [processUuid()](./pending_passwords/processUuid.md)



