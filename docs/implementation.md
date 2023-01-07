
# Implementation Guide

Armor is not intended to be a fully fledged user management system, and instead is intended to work alongside / behind your own user management system, while providing the base functionality such as collecting base account information, verifying accounts via e-mail / SMS, login and authenticated sessions, two factor requests, user segregated encryption, et al.

**NOTE:** It's recommended you quickly read through this page, as it will give you an understanding of what the end implementation will look like.  Afterwards, install Armor and familiarize yourself with the package, then come back to this page again and it will make more sense.


## Users Database / Model

All users are stored within the `armor_users` table, and utilize the `Apex\Armor\User\ArmorUser` class as the model, which is fully documented on the [ArmorUser Class](armoruser.md) page of this documentation.  Armor only stores the base information necessary for account verification and authentication, which includes username, password, e-mail address and phone number.  All other information such as full name, address, locale, and so on must be stored by your own user management system.

It's expected you will have your own user database table(s), and class which will extend the `ArmorUser` class.  Within your own users database table, you should include a `uuid VARCHAR(30)` column that's either a primary key or at the very leaste a unique column, which will reference the `uuid` column of the `armor_users` table.  You should also look at the columns within the `armor_users` table, and ensure your users database table does not have any duplicate column names aside from `uuid`.

Within the [AdapterInterface Class](adapter.md) you will need to develop, you will notice the `getUuid()` method, which is used to obtain users by their UUID.  If you look at the default `MercuryAdapter.php` adapter class, you will see:

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


## Authenticating Requests

Authenticating requests within your secure area and retrieving the authenticated user is also very simple, for example:

~~~php
use Apex\Armor\Armor;

// Check if session authenticated
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in!");
}

/**
 * Get user, which will be an instance of your user class as returned by the above getUuid() method.
 */
$user = $session->getUser();

// If performing a sensitive operation, require two factor authentication
$session->requireTwoFactor();

/**
 * Any code below this line will not be executed until the request is authenticated 
 * via two factor e-mail / SMS authentication.
 */

// Encrypt data, segregated to user's public RSA key which was auto-generated upon creation.
$data_id = $session->encryptData("any data goes here");

// Save $data_id somewhere, and decrypt later with:
$plain_text = $session->decryptData($data_id);
~~~

With the above, `$session` is an instance of the [AuthSession Class](auth_session.md), while `$user` is an instance of your own user class.  All sessions are stored in redis, and are extensible via the `getAttribute()` / `setAttribute()` methods.


## Login Form

Upon submission of your login form, use code such as:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Login;

// Init Armor
$armor = new Armor();
$login = new Login($armor);

// Check login
if (!$session = $login->withPassword($_POST['username'], $_POST['password'])) { 
    $reason = $login->getFailReason();
    die("Unable to login, $reason");
}

// If here, display the dashboard / homepage of secure area.
~~~

If execution gets to the end, the user has been fully authenticated and has permission to access the secure area.  If any two factor e-mail / SMS authentication or e-mail address / phone verification is required to proceed with the login, the necessary template should have already been rendered as the [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method would have been called.


## Creating Users

Within your normal user registration process flow, include code such as:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Validator;
use Apex\Armor\Auth\Operations\Phone;
use Apex\Armor\Exceptions\ArmorProfileValidationException;

// Init Armor
$armor = new Armor();

/**
 * Optionally, use the below to gather phone number from phone.  This assumes two form fields 
 *  named "phone" for the number itself, and "phone_country" as a select list of country codes.
 */
$phone = Phone::get();

// Validate profile
try { 
    $validator = new Validator($armor);
    $validator->validate('', $_POST['password'], $_POST['username'], $_POST['email'], $phone);
} catch (ArmorProfileValidationException $e) { 
    die("Error: " . $e->getMessage());
}

// Create user
$user = $armor->createUser('', $_POST['password'], $_POST['username'], $_POST['email'], $phone);


/**
 * Get auto-generated UUID to insert into your own users database table.  Optionally, 
 * generate your own UUID and pass it as the first parameter to createUser() method.
 */
$uuid = $user->getUuid();
~~~

The above will create a user within Armor, including send out any necessary e-mail / SMS messages for account verification, which is dependent on the settings defined within the [ArmorPolicy configuration](armorpolicy.md).  The four fields (username, password, e-mail, phone) are not necessarily all required, and the [ArmorPolicy Configuration](armorpolicy.md) can be configured to only require a single e-mail address or phone number, or any combination thereof.


## Moving Forward

Above should give you a solid outline as to how you will implement Armor into your application.  Please read through the rest of the documentation for specifics such as how to authenticate SMS verification codes, process authenticated two factor requests, et al.  If you have any questions or need assistance with implementation, please feel free to post on the <a href="https://reddit.com/r/apexpl">/r/apexpl Reddit sub</a> for a prompt and helpful response.

To continue with installation of Armor, see the [Setup Database Connection](database_setup.md) page of this documentation.

