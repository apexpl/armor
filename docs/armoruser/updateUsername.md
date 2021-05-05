
# ArmorUser::updateUsername()

Updates a user's username.

> `string ArmorUser::updateUsername(string $new_username)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$new_username` | Yes | string | Username to update user profile with.


**Return Value**

Will return a constant of the `UpdateStatus` enum, and will be one of the following:

* `UpdateStatus::SUCCESS` - Update completed.
* `UpdateStatus::FAIL` - Only returned if the new and existing usernames are the same.

Please note, this method may also throw a `ArmorProfileValidationException` if an invalid or duplicate item is specified.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Update username
$status = $user->updateUsername('new_username');

// Check status
if ($status == UpdateStatus::FAIL) { 
    // Display error saying you need to enter a new and valid username

} else { 
    // Display success template
}

~~~




