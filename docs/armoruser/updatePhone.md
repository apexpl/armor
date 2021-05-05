
# ArmorUser::updatePhone()

Updates a user's phone number.

> `string ArmorUser::updatePhone(string $new_phone, bool $is_verified = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$new_phone` | Yes | string | Phone number to update user profile with.
`$is_verified` | No | bool | Whether or not the phone number has already been verified.  If true, phone number will be updated without any verification, such as when updating from administration panel.  If false, verification will occur based on the [ArmorPolicy](../armorpolicy.md) configuration.


**Return Value**

Will return a constant of the `UpdateStatus` enum, and will be one of the following:

* `UpdateStatus::SUCCESS` - Update completed.
* `UpdateStatus::FAIL` - Only returned if the new and existing phone numbers are the same.
* `UpdateStatus::PENDING_VERIFY` - SMS message has been sent to the phone number with confirmation code, which must be input by user.  If this status is returned, you should display a template asking user to input confirmation code.  See [Verifying Phone Numbers](../verify_phone.md) page for details.  Only returned if the phone verification within the ArmorPolicy is set to `VerifyPhone::REQUIRE` or `VerifyPhone::OPTIONAL`.

Please note, this method may also throw a `ArmorProfileValidationException` if an invalid or duplicate item is specified.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update phone
$status = $user->updatePhone('14165551234');

// Check status
if ($status == UpdateStatus::PENDING_VERIFY) { 
    // Display template with textbox asking user to enter confirmation code sent via SMS

} elseif ($status == UpdateStatus::FAIL) { 
    // Display error saying you need to enter a new and valid phone number

} else { 
    // Display success template
}

~~~




