
# AuthSession class

The `Apex\Armor\Auth\AuthSession` class provides an object of an authenticated session.  You may obtain the current auth session via the `Armor::checkAuth()` method, for example:

~~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// $session is an instance of AuthSession class.
~~~


## Available Functionality

The `AuthSession` class allows for the following functionality:

* [Two Factor Requests](two_factor.md)
* [Encrypt / Decrypt Data](session_encrypt.md)
* [Session Attributes](session_attributes.md)


## Get Methods

The `AuthSession` class includes the following get methods:

* getUuid()
* getUser() - Returns instance of [ArmorUserInterface class](armoruser.md) of the authenticated user.
* getStatus() - One of the `SessionStatus` enum constants.
* getId() - Unique id# of the session
* getIpAddress() _ IP address upon first login.
* getUserAgent() - User agent upon first login.
* getExpiresAt() - Time in seconds since epoch when the session expires.
* getHistoryId() - Used internally to track page requests to session for login history.
* getEncHash() - Used for encryption, can be ignored.


## Logout

You may easily logout and destroy a session by calling the `logout()` method on any session, for example:

~~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// Logout
$session->logout();

Echo "You have been successfully logged out\n";
~~~





