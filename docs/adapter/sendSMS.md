
# AdapterInterface::sendSMS()

Send a SMS message.

> `void AdapterInterface::sendSMS(ArmorUserInterface $user, string $type, string $armor_code, string $new_phone = '')`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user to send SMS message to.  Obtain the phone number to send to via the `$user->getPhone()` method.
`$type` | string | The type of SMS message being sent, and will be one of the constants within the `Apex/Armor\Enums\PhoneMessageType` class.
`$armor_code` | string | The six digit verification code needed to authenticate the request.  This must be included within the SMS message.
`$new_phone` | string | Used when user is updating their phone number, and verification is required before the database is updated.  If this variable is not an empty string, send to this phone number instead of `$user->getPhone()`.




