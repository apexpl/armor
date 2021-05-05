
# AdapterInterface::sendEmail()

Sends an e-mail message.

> `void AdapterInterface::sendEmail(ArmorUserInterface $user, string $type, string $armor_code, string $new_email = '')`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user to send e-mail message to.  Obtain the e-mail address to send to via the `$user->getEmail()` method.
`$type` | string | The type of e-mail message being sent, and will be one of the constants within the `Apex/Armor\Enums\EmailMessageType` class.
`$armor_code` | string | Either the 48 character hash or 6 digit verification code needed to authenticate the request.  This needs to be included in the contents of the e-mail message.
`$new_email` | string | Used when user is updating their e-mail address, and verification is required before the database is updated.  If this variable is not an empty string, send to this e-mail address instead of `$user->getEmail()`.



