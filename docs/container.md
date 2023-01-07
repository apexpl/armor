
# Container Definitions

You may define the location of your container definitions file via the `$container_file` variable when instantiating the central `Armor` class.  If no container file is specified, it defaults to the ~/config/container.php file.

The below table details all items within the container file:


Item | Description
------------- |-------------
`redis::class` | Generally a closure that connects to redis and returns the connection. 
`DbInterface::class` | Connection to the SQL database.  Armor is fully tested on mySQL, PostgreSQL and SQLite.  See the [Setup Database Connection](database_setup.md) page for details.
`cookie_prefix` | Anything you wish, and all cookies set by Armor will be prefixed with this.
`cookie` | Array containing the parameters to use when setting a cookie.  You need to change the domain name within this array.
`default_policy` | If no ArmorPolicy is defined during instantiation, these settings will be used.  Please see the [ArmorPolicy Configuration](armorpolicy.md) page for details.
`default_brute_force_policy` | If not `BruteForcePolicy` is defined within the ArmorPolicy, this policy will be used.  Please see the [Brute Force Policy](brute_force_policy.md) page for details.
`reserved_usernames` | Array of reserved usernames that Armor will check against upon creation.  Any items beginning with a ~ tilda character will be treated as if the username contains that word, and all other items will be exact matches. 
`AdapterInterface::class` | The adapter to use, which many times will be developed by you to implement Armor into your back-end application.  Please see the [AdapterInterface Class](adapter.md) page for details.
`maxmind_dbfile` | If you would like to track geo-location details on all user registrations, this is the location of the GeoLite2-City.mmdb file downloaded from MaxMind.  Defaults to the Armor installation directory, and please see the [Install Database](database_install.md) page for details. 
`Emailer::class` | Only applicable if you will be using the [Apex Mercury](https://github.com/apexpl/mercury) package to send e-mails which is included with Armor, and is the SMTP connection information to use.
`NexmoConfig::class` | Only applicable if you will be using the [Apex Mercury[(https://github.com/apexpl/mercury) package to send SMS messages via Nexmo which is included with Armor, and is your Nexmo API details.
`DebuggerInterface::class` | Only applicable if you have the [Apex Debugger](https://github.com/apexpl/debugger) installed, and can be left as is.
`migrations.yaml_file` | Ignore this item, and leave as is.  It's required by the database migrations package, and must remain as is.



