
# ArmorUser::updatePasswordWithMaster()

Updates a user's password with the master encryption password.  Used by administrator for cases where the user has forgotten their old password, and also has encrypted data on their account, hence are unable to change the password on their own.

> `void ArmorUser::updatePasswordWithMaster(string $new_password, string $master_password)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$new_password` | Yes | string | Password in plain text to update user profile with.
`$master_password` | Yes | string | The master encryption password defined during installation in plain text.

**Return Value**

Does not return anything, but will throw an `ArmorProfileValidationException` or other error if not successful.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update password
$user->updatePasswordWithMaster('new_password', 'master_password');
~~~


