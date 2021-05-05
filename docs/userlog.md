
# activity Log

Armor keeps an activity log of all actions taken against a user's account such as e-mail or password change, e-mail / phone verified, et al.  This log is stored within the `armor_users_log` table of the database, and Armor will only ever add entries to this table, but never modify or delete them.

All historical activity against any user account can be retrived through the `Apex\Armor\User\Extra\UserLog` class.


## Available Methods

The `Apex\Armor\User\Extra\UserLog` class contains the following methods.  The `listUuid()` and `listAll()` methods return an array of arrays, and each element contains an `action` key being which action was performed against the user.  This will always be one of the constants found in the `Apex\Armor\Enums\UserLogAction` class.

* int getCountAll() - Get total number of log entries in database.
* int getCountUuid(string $uuid) - Get number of log entries for given user.
* array listAll(int $start = 0, int $limit = 0, bool $sort_desc = true) - Get list of all actions taken against user accounts.  The `user` element within each element is an instance of the [ArmorUser Class](armoruser.md).
* array listUuid(string $uuid, int $start = 0, int $limit = 0, bool $sort_desc = true) - Get list of all log actions taken against specified user's account.

## Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\UserLog;

// Init Armor
$armor = new Armor();
$userlog = new UserLog($armor);

// Get suser log
$log = $userlog->listUuid('u:392');
foreach ($log as $row) { 
    echo $row['action'] . ' performed at ' . date('Y-m-d H:i:s', $row['created_at']->getTimestamp()) . "\n";
}
~~~

