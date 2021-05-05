
# Registration Info

the `Apex\Armor\User\RegistrationInfo` class is used for session based information during user registration.  This includes the user's IP address, user agent, and geo-location information such as country, city, latitude and longitude.

Normally this class can be ignored, and it will be automatically generated based upon the HTTP request during registration.  However, you may pass an instance of this class to the `[Armor::createUser()](profiles.md) method when creating a user to override the auto-generated registration info.

The constructor accepts the following parameters:

Variable | Type | Description
------------- |------------- |------------- 
`$reg_ip` | string | IP address of the user.
`$reg_user_agent` | No | User agent of the user, as returned by the `Apex\Armor\Auth\Operations\UserAgent::get()` method.
`$reg_country` | string | Two character country code obtained via geo-lookup of IP address.
`$reg_province_iso_code` | string | ISO code of province obtained via geo-lookup of IP address.
`$reg_province_name` | string | Name of province obtained via geo-lookup of IP address.
`$reg_city` | string | Name of city obtained via geo-lookup of IP address.
`$reg_latitude` | float | Latitiude of user location obtained via geo-lookup of IP address.
`$reg_longitude` | float | Longitude of user location obtained via geo-lookup of IP address.

Any parameters not defined will be automatically generated based on session information.  If you define an IP address but not any geo-location information, the geo-location will be retrived by looking up the IP address defined, and not the session IP address.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\RegistrationInfo;

// Init Armor
$armor = new Armor();

// Define registration info
$reginfo = new RegistrationInfo(
    armor: $armor, 
    reg_ip: '142.250.217.110'
);

// Create user
$user = $armor->createUser(
    username: 'myuser', 
    password: 'secret_pass', 
    email: 'jsmith@domain.com, 
    reginfo: $reginfo
);
~~~


## Get Methods

The following get methods are available within this class, and by extension are also available within the [ArmorUser Class](armoruser.md)

* getRegIpAddress()
* getRegUserAgent()
* getRegCountry()
* getRegProvinceISOCode()
* getRegProvinceName()
* getRegCity()
* getRegLatitude()
* getRegLongitude()



