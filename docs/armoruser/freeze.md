
# ArmorUser::freeze()

Marks a user as frozen, temporarily limiting their ability to login.  This should never need to be executed, and is used internally for brute force prevention.  If one of the rules defined within the [Brute Force Policy](../brute_force_policy.md) is triggered, the user will be temporarily frozen for a pre-defined amount of time and will be unable to login during that period.

> `void ArmorUser::freeze(DateTime $unfreeze_at)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$unfreeze_at` | Yes | DateTime | The time at which the freeze will expire.  The user will not be permitted to login again until this time.


**Return Value**

This method does not return anything.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Freeze
$unfreeze_at = new \DateTime(time() + 3600);
$user->freeze($unfreeze_at);
~~~


