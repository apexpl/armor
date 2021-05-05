
# Login History

Armor keeps track of all login sessions, including which pages were accessed during each session.  These logs are stored within the `armor_history_logins` and `armor_history_reqs` tables of the SQL database, and handled by the `Apex\Armor\User\Extra\LoginHistory` class.


## Available Methods

The `Apex\Armor\User\Extra\LoginHistory` class contains the following methods:

* int getCountAll() - Get total number of login sessions in database.
* int getCountUuid(string $uuid) - Get number of login sessions for given user.
* array listAll(int $start = 0, int $limit = 0, bool $sort_desc = true) - Get list of all login sessions.  The `user` element within each element is an instance of the [ArmorUser Class](armoruser.md).
* array listUuid(string $uuid, int $start = 0, int $limit = 0, bool $sort_desc = true) - Get list of all login sessions on user's account.
* listPageRequests(int $history_id, int $start = 0, int $limit = 0, bool $sort_desc = true) - Get list of all pages accessed during a given login session.  The `$history_id` is the "id" element of any login session retrived by the previous two methods.

## Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Extra\LoginHistory;

// Init Armor
$armor = new Armor();
$history = new LoginHistory($armor);

// Get login history
$log = $history->listUuid('u:392');
print_r($log);
~~~


