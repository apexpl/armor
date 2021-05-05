
# AdapterInterface::handleSessionStatus()

Handles the session status, and mainly meant to display the appropriate template / view such as when e-mail / SMS two factor authentication is required, or if the [ArmorPolicy Configuration](../armorpolicy.md) requires users verify their e-mail address but a user logs in with an unverified e-mail.

> `void AdapterInterface::handleSessionStatus(AuthSession $session, string $status)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$session` | AuthSession | The current authenticated session.  See [AuthSession class](../auth_session.md) for details.
`$status` | string | The status of the session, and will be one of the constants within the `Apex\Armor\Enums\SessionStatus` class.


