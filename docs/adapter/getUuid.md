
# AdapterInterface::getUuid()

Load user by UUID.  This allows an instance of your own user class (which generally extends the `ArmorUser` class) to be returned every time Armor loads a user, providing better integration into your back-end application.

> `?ArmorUserInterface AdapterInterface::getUuid(DbInterface $db, string $uuid, bool $is_deleted = false)`

**Parameters**

Variable | Type | Description
------------- |------------- |-------------
`$db` | DbInterface | Connection object to the SQL database. 
`$uuid` | string | The UUID (universal unique identifier) of the user you wish to load. 
`$is_deleted` | bool | Whether or not to retrieve user who was previously deleted.  Defaults to false.


**Return Value**

Either an instance of your ow user class, or instance of the `Apex\Armor\User\ArmorUser` class.  The object returned must implement the `ArmorUserInterface` interface.  This method may return null if no user exists.


### Example

Within the `/src/Adapter/MercuryAdapter.php` class, the `getUuid()` method looks like:

~~~php
public function getUuid(DbInterface $db, string $uuid, bool $is_deleted = false):?ArmorUserInterface
{

    // Get user as object from database
    if (!$user = $db->getObject(ArmorUser::class, "SELECT * FROM armor_users WHERE uuid = %s AND is_deleted = %b", $uuid, $is_deleted)) { 
        return null;
    }

    // Return
    return $user;
}
~~~

Within your own adapter class, you can simply change this to for example:

~~~php
public function getUuid(DbInterface $db, string $uuid, bool $is_deleted = false):?ArmorUserInterface
{

    // Get user as object from database
    if (!$user = $db->getObject(MyAppUser::class, "SELECT * FROM users, armor_users WHERE users.uuid = %s AND users.uuid = armor_users.uuid AND armor_users.is_deleted = %b", $uuid, $is_deleted)) { 
        return null;
    }

    // Return
    return $user;
}
~~~

Simply switch out the `ArmorUser::class` with your own user class name, and modify the SQL statement to select from both user database tables.  Now every time Armor loads or returns a user, it will load your full user class which by extension retains all functionality of the [ArmorUser Class](../armoruser.md).

