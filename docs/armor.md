
# Armor Class

The central class for Armor is located at `Apex\Armor\Armor`, and must be instantiated for all Armor functionality.  This class allows the following constructor parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |-------------
`$container_file` | No | string | Location of the container definitions file.  Defaults to ~/config/container.php. 
`$redis` | No | redis | A redis connection.  If undefined, will connect using the `redis::class` closure within the container file.
`$db` | No | DbInterface | The SQL database connection.  If undefined, will connect using the `DbInterface::class` item of the container file.
~$policy` | No | ArmorPolicy | The ArmorPolicy to use, if not defined defaults to the settings defined within the container file.  See the [ArmorPolicy Configuration](armorpolicy.md) page for details.
`$policy_name` | No | string | Only applicable if you're using the PolicyManager, and is the name of a previously saved ArmorPolicy to load and use.  Please see the [Policy Manager](policy_manager.md) page for details.

For example:

~~~php
use Apex\Armor\Armor;
use redis;

// Connect to redis
$redis = new redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('your_redis_password');

// Start Armor
$armor = new Armor(
    redis: $redis
);
~~~

## Available Methods

The central `Armor` class extends the `Apex\Armor\User\Profiles` class, allowing you to create, load, and delete users.  Please see the [User Profiles](profiles.md) page for details on methods available.  

Aside from the extended methods, the `Armor` class also contains the following methods:

* `getPolicy():ArmorPolicy` - Get the ArmorPolicy currently being used.
* `setPolicy(ArmorPolicy $policy):void` - Set the ArmorPolicy to use.
* `loadPolicy(string $policy_name):void` - Load and use the ArmorPolicy that was previously saved under this name.
* `checkAuth(string $type = 'user'):?AuthSession` - Will check whether or not the current user is authenticated, and if yes, will return a [AuthSession object](auth_session.md).  Will return null if not authenticated.
* `getCookieUsername():string` - If a user checks the "remember me" box upon login, it will save their username in a cookie, which will be returned by this method.  Returns blank string otherwise.



