
# Armor::find()

Search the database of users for various criteria.  Alternatively, and probably preferred, you may also simply query the `armor_users` table within the database.

> `array Armor::find(string $username = '', string $email = '', string $phone = '', string $type = 'user', bool $is_deleted = false, string $reg_country = '', string $reg_province_code = '', string $reg_province_name = '', string $reg_city = '', string $reg_ip = '', ?DateTime $created_at_start = null, ?DateTime $created_at_end)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$username` | No | string | Username to search for, full or partial.
`$email` | No | string | E-mail address to search for, full or partial.
`$phone` | No | string | Phone number to search for, full or partial.
`$type` | No | string | The type of user to search for, defaults to "user".
`$is_deleted` | No | bool | Whether or not to search for users who have been previously deleted.  Defaults to false.
`$reg_country` | No | string | The two letter country code to search for.  This is obtained by doing a geo IP lookup upon registration.
`$reg_province_code` | No | string | The ISO province code to search for.  This is obtained by doing a geo IP lookup upon registration.
`$reg_province_name` | No | string | The province name to search for.  This is obtained by doing a geo IP lookup upon registration.
`$reg_city` | No | string | The city name to search for.  This is obtained by doing a geo IP lookup upon registration.
`$reg_ip` | No | string | The IP address of the user upon registration to search for.
`$created_at_start` | No | DateTime | Search all users who were created at this date time or later.
`$created_at_end` | No | DateTime | Search all users who were created at this date time or before.


**Return Value**

Returns an array of associative arrays, each representing one row from the `armor_users` database table.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Get all @gmail.com users
$armor = new Armor();
$users = $armor->find('', '@gmail.com');

// Go through users
foreach ($users as $u) { 
    echo "Uuid: $u[uuid], username: $u[username], email: $u[email]\n";
}
~~~



