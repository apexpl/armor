
# AdapterInterface::pendingPasswordChange()

This method is called when a user changes their password, no old / current password is specified, a master encryption password was defined during installation of Armor, and there is encrypted data within the database assigned to the user's account.

Due to the user segregated AES encryption, Armor can't simply overwrite a user's password, as it must decrypt the user's RSA private key and encrypt it again to the new password.  If the user loses their old / current password, the password change can be completed using the master encryption password.  Please see the [Pending Password Changes](../pending_password_changes.md) page for details.

This method should simply notify the administrator somehow that a pending password change is awaiting.  Please note, you should never automatically process the pending password changes using this method as that would require the master password to be online in some fashion, which is a huge security vulnerability.

> `void AdapterInterface::pendingPasswordChange(ArmorUserInterface $user)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user who is requesting a password change.  See the [ArmorUser Class](../armoruser.md) page for details on this object.


