
# AdapterInterface::requestInitialPassword()

Only applicable if the `require_password` setting within [ArmorPolicy configuration](../armorpolicy.md) is set to `RequirePassword::REQUIRE_AFTER_REGISTER`, and should display a template asking the user to define their password.  With this configuration setting, users may register without a password.

Upon registering, they will receive an e-mail message with a link to set their password.  Upon clicking said link, this method is called which should display a template asking them to define a password.  Please see the [Define Password After Registration](../request_initial_password.md) page for details.

> `void AdapterInterface::requestInitialPassword(ArmorUserInterface $user)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user who is requesting the password.  See the [ArmorUser Class](../armoruser.md) page for details on this object.


