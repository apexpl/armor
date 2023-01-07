
# ArmorUser::updateTwoFactor()

Updates a user's two factor authentication settings.  Please note, this is only applicable if the ArmorPolicy configuration has the two factor settings set to optional.

> `void ArmorUser::updateTwoFactor(string $type, string $frequency)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | Yes | string | The type of two factor authentication (none, e-mail, phone, et al).  Please see the `TwoFactorType` enum for supported values.
`$frequency` | Yes | string | The frequency of two factor authentication (none, always, new_device, et al).  Please see the `TwoFactorFrequency` enum for supported values.

**Return Value**

Does not return anything, but will throw an `ArmorProfileValidationException` or other error if not successful.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update two factor
$user->updateTwoFactor('phone', 'new_device');
~~~

