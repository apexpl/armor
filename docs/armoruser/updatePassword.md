
# ArmorUser::updatePassword()

Updates a user's password.

> `string ArmorUser::updatePassword(string $new_password, string $old_password = '', bool $delete_encrypted = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$new_password` | Yes | string | Password in plain text to update user profile with.
`$old_password` | No | string | The user's old / current password in plain text.  Should always be specified if possible due to the RSA key-pairs that are generated for each user and encrypted to their password.
`$delete_encrypted` | No | bool | Should always be left to its default of `false` except in extraordinary circumstances.  If true, this will delete any data currently in the database encrypted to the user.

Armor automatically generates a 4096 bit RSA key-pair for every user, and encrypts the private key via AES256 to their password.  Due to this you can not simply overwrite a user's password, and instead the private RSA key needs to be decrypted, then encrypted again to the new password.  If the `$old_password` is specified and correct, this is a non-issue and the password change occurs seamlessly.

However, if the `$old_password` is not available and a master encryption password was generated during installation, the password change will be queued for later processing.  Please see the [Pending Password Changes](../profiles_pending_password.md) page for details.

You may also use the [updatePasswordWithMaster()](updatePasswordWithMaster.md) method of the `ArmorUser` class instead of this method if you already know the master password will be required.  If the `$old_password` is not available, and no master encryption password was defined during installation, the only way to change the user's password is by setting the `$delete_encrypted` boolean to true, which will overwrite the user's password and delete any existing encrypted information within the database assigned to the user.

**Return Value**

Will return a constant of the `UpdateStatus` enum, and will be one of the following:

* `UpdateStatus::SUCCESS` - Update completed.
* `UpdateStatus::FAIL` - Returned if old and new passwords are the same.
* `UpdateStatus::PENDING_ADMIN` - The `$old_password` was not specified, and there is encrypted data on the user's account.  The password change has been queued, and is awaiting processing by the administrator.  This also triggers the `pendingPasswordChange()` method within the [Adapter Class](../adapter.md).  

Please note, this method may also throw a `ArmorProfileValidationException` if an invalid or duplicate item is specified.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update password
$status = $user->updatePassword('new_password', 'old_password');

// Check status
if ($status == UpdateStatus::PENDING_ADMIN) { 
    // Display message stating password change is pending processing by administrator.

} elseif ($status == UpdateStatus::FAIL) { 
    // Display error saying you need to enter a new and valid passwoword.

} else { 
    // Display success template
}

~~~



