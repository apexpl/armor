
# ArmorUser::listLoginHistory()

Retrieve log of all prior logins by user.

> `array ArmorUser::listLoginHistory(int $start = 0, int $limit = 0, bool $sort_desc = true)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$start` | No | int | Where in the result set to start.  Used for pagination, along with the `getLoginHistory()` method.  Defaults to 0.
`$limit` | No | int | The number of results to return.  Used for pagination, defaults to 0 for all records available.
`$sort_desc` | No | bool | Whether or not to sort the results in descending order (most recent).  Defaults to true.

**Return Value**

Returns a one-dimensional array, each element being an associative array of one user login.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Enums\UpdateStatus;

// Load user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Get log
$log = $user->getLoginHistory();
print_r($log);
~~~


