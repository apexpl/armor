
# Install Database

Once the connection is in place, you need to install the Armor database tables.  Open the ~/install.php file, and near the top you will see where you may define the `$master_password` variable.  This is optional and may be left blank, but it is recommended for most operations that you define a master encryption password.  See below for details.

Assuming the `DbInterface::class` container item was set correctly as described in the previous page, simply run the install.php within terminal.  It will only take a second, and all necessary database tables will be created, making Armor ready for use.


## Master Encryption Password

Armor automatically generates a 4096 bit RSA key-pair for every user created, and stores the private key encrypted via AES256 to the user's password.  This allows for segregration of encrypted data, and means only the user can decrypt information encrypted to their account with their password.  

When a user changes their password, the current / old password is required, allowing Armor to decrypt the RSA private key, and encrypt it again to the new password ensuring the integrity of all data encrypted to the user's account.  However, lost passwords cause a predicament since Armor has no way to decrypt that private RSA key, meaning all data encrypted to the user's account becomes inaccessible.

If a master encryption password is defined, every time a RSA key-pair is generated the private key will be encrypted to both, the user's password via AES256, and to the master RSA key.  In instances where the user has lost their password, their password may be changed by using the master password.  Please see the [Pending Password Changes](pending_password_changes.md) file for details.

**Warning:** Defining a master encryption password has its obvious vulnerabilities as one password will unlock all encrypted data within the database.  However, please note if you do not define a master password, and a user loses their password, all encrypted data associated with their account will be permanently lost upon resetting their password.


## MaxMind GeoIP Database

Due to MaxMind's software license, it is not possible to include the GeoLiteCity database with Armor, but it is free for you to download.  If you would like to record geo-location data on all userrs upon registration including country, province, and city, you must signup for a free account at [https://maxmind.com/](https://maxmind.com/), and within the client area download the GeoLite2-City.mmdb database file.

Once downloaded, you may place the file anywhere on your server you wish.  Within the container file (defaults to ~/config/container.php) there is a `armor.maxmind_dbfile` item, which must be the location of the GeoLite2-City.mmdb file on your server, for example:

~~~
`GeoLite2-City.mmdb` => '/path/to/GeoLite2-City.mmdb', 
~~~

The default location is the Armor installation directory, but you may change it as necessary.


## Develop Adapter PHP Class

With the database tables installed, the last and largest step is to develop a [AdapterInterface Class](adapter.md) that ties your back-end application into Armor.


