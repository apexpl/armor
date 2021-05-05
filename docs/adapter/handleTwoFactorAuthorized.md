
# AdapterInterface::handleTwoFactorAuthorized()

Handles a request that has been successfully authenticated via two factor e-mail / SMS authentication.  A PSR7 compliant `ServerRequestInterface` object is passed to this method, and is the exact HTTP request that was being processed before it was interrupted to require two factor authentication.  

This method should parse the HTTP request accordingly, and process it the exact same way as previously before it was interrupted.  The session status will have been updated to `SessionStatus::TWO_FACTOR_AUTHORIZED`, so the next time `AuthSession::requireTwoFactor()` method is called it will not interrupt the code execution, and will continue executing the request.

> `void AdapterInterface::handleTwoFactorAuthorized(AuthSession $session, ServerRequestInterface $request, bool $is_login)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$session` | AuthSession | The current authenticated session.  See [AuthSession class](../auth_session.md) for details.
`$request` | ServerRequestInterface | A PSR7 compliant server request object of the exact HTTP request being processed before it was interrupted to require two factor athentication.
`$is_login` | bool | Whether or not two factor authentication was initiated during a login due to the [ArmorPolicy configuration](../armorpolicy.md), or normally via the `AuthSession::requireTwoFactor()` method.


