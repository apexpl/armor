
# AdapterInterface::requestResetPassword()

Upon a user requesting a password reset, and clicking on the verification link within their e-mail, this method will be called.  It should display a template asking the user to define their new password.

> `void AdapterInterface::requestResetPassword(ArmorUserInterface $user)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user who is requesting the password.  See the [ArmorUser Class](../armoruser.md) page for details on this object.


