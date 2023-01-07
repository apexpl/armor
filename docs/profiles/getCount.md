
# Armor::getCount()

Gets the total number of users in database.

> `int Armor::getCount(string $type = '', bool $is_deleted = false)`

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$type` | No | string | If defined, will retrive count of users of the specified type.  Otherwise, will retrieve count of all users.
`$is_deleted` | No | bool | Whether or not to retrieve count of users who have been previously marked as deleted.  Defaults to false.

**Return Value**

The number of users within the database.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;

// Get count
$armor = new Armor();
$count = $armor->getCount();

echo "Total of $count users\n";
~~~


