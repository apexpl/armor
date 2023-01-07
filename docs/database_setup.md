
# Setup Database Connection

Armor does require both a redis connection, and a SQL database connected via the <a href="https://github.com/apexpl/db">Apex Database Layer</a>, which must be available via the `DbInterface::class` item of the container.  However, you may easily import an existing Doctrine, Eloquent or PDO connection into ADL for use with Armor as well.

Please note, all database tables used by Armor are prefixed with "armor_", meaning they should not interfere with any existing tables within your database.

## redis Connection

Within the container defitions file (default ~/config/container.php) you will notice a `redis::class` item at the top.  This is a closure that should be modified as necessary to connect to redis on your system and return the connection.  Please note, it's possible to pass the redis and SQL connections during instantiation instead, and see bottom section for details.


## Connect with ADL

The easiest way is within the container file (defaults to ~/config/container.php) simply modify the `DbInterface::class` item as necessary with your database credentials.  Armor is fully tested with mySQL, PostgreSQL and SQLite, and you may also change the driver as desired, for example:

~~~php
DbInterface::class => [\Apex\Db\Drivers\PostgreSQL\PostgreSQL::class, ['params' => [
    'dbname' => 'my_database', 
    'user' => 'myuser', 
    'password' => 'secret_password', 
    'host' => 'localhost', 
    'port' => 5432]]
], 
~~~

That's it, and Armor will function perfectly fine.


## Import Existing Doctrine, Eloquent or PDO Connection

ADL allows you to easily <a href="https://github.com/apexpl/db/blob/master/docs/wrappers.md">import existing connections</a> From Doctrine, Eloquent or PDO.  The easiest way is to modify the `DbInterface::class` item of the container file (defaults to ~/config/container.php), and set it to a callable which imports the connection into ADL, for example:

~~~php
use Apex\Db\Drivers\mySQL\mySQL;

function ConnectArmorDb()
{

    /**
     * Get existing connection, must be either:
     *
     *    \Doctrine\ORM\EntityManager; instance
     *    Eloquent Manager instance
    *     PDO instance
     */
    $manager = '';

    // Import from Doctrine
    $db = \Apex\Db\Wrappers\Doctrine::import(new mySQL(), $manager);

    // Import from Eloquent
    $db = \Apex\Db\Wrappers\Eloquent::import(new mySQL(), $manager);

    // Import from PDO
    $db = \Apex\Db\Wrappers\PDO::import(new mySQL(), $manager);

    // Return
    return $db;
}
~~~

That's it, and the existing database connection will be extracted and imported into an ADL instance for use with Armor.  Please note, this uses the existing connection and does not establish a second database connection.


## Instantiate with redis / SQL Connection

Instead of creating new connections using credentials / closures within the container, you may also pass existing connections to the constructor during initialization.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Db\Drivers\mySQL\mySQL;
use redis;

// Connect to redis
$redis = new redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('redis_password');

// Import Doctrime manager instance
$doctrine_manager = '';
$db = Apex\Db\Wrappers\Doctrine::import(new mySQL(), $doctrine_manager);

// Init Armor
$armor = new Armor(
    redis: $redis, 
    db: $db
);
~~~

By passing existing connections into the constructor, they will be placed into the container as necessary instead of new database connections being established.  


## Install Database

Once the database connection has been established, please move on to the [Install Database](database_install.md) page of the documentation.



