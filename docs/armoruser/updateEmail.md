
# ArmorUser::updateEmail()

Updates a user's e-mail address.

> `string ArmorUser::updateEmail(string $new_email, bool $is_verified = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$new_email` | Yes | string | E-mail address to update user profile with.
`$is_verified` | No | bool | Whether or not the e-mail address has already been verified.  If true, e-mail will be updated without any verification, such as when updating from administration panel.  If false, verification will occur based on the [ArmorPolicy](../armorpolicy.md) configuration.


**Return Value**

Will return a constant of the `UpdateStatus` enum, and will be one of the following:

* `UpdateStatus::SUCCESS` - Update completed.
* `UpdateStatus::FAIL` - Only returned if the new and existing e-mail addresses are the same.
* `UpdateStatus::PENDING_VERIFY` - E-mail dispatched to user, and e-mail address will be updated upon user clicking link within e-mail.  Only returned if the e-mail verification within the ArmorPolicy is set to `VerifyEmail::REQUIRE` or `VerifyEmail::OPTIONAL`.
* `UpdateStatus::PENDING_OTP` - E-mail dispatched to user with a one-time verification code, which the user must input.  Upon receiving this status, you should display a template with textbox asking for the confirmation code.  Please see [Verifying E-Mail Addresses](../verify_email.md) page for details.  Only returned if the e-mail verification within the ArmorPolicy is set to `VerifyEmail::REQUIRE_OTP` or `VerifyEmail::OPTIONAL_OTP`.

Please note, this method may also throw a `ArmorProfileValidationException` if an invalid or duplicate item is specified.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update e-mail
$status = $user->updateEmail('new_email@domain.com');

// Check status
if ($status == UpdateStatus::PENDING_VERIFY) { 
    // Display template asking user to check their e-mail, and click on link.

} elseif ($status == UpdateStatus::PENDING_OTP) { 
    // Display template with textbox asking user to check e-mail, and enter confirmation code.

} elseif ($status == UpdateStatus::FAIL) { 
    // Display error saying you need to enter a new and valid e-mail address.

} else { 
    // Display success template
}

~~~



