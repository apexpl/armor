
# Login and Request Authentication

User logins are conducted via the `Apex\Armor\Auth\Login::withPassword()` method, which provides an easy interface to connect into your login form.

**Parameters**

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$username` | Yes | string | The username to login with.  This may be the e-mail address or phone number of the user, depending on your [ArmorPolicy configuration](armorpolicy.md).
`$password` | Yes | string | The password to login with, in plain text.
`$type` | No | string | The type of user logging in, defaults to "user".
`$set_cookie` | No | bool | Whether or not to set the session cookie within the user's web browser.  Defaults to true, and must be true and required to track authenticated session of user in web browser.
`$remember_me` | No | bool | Whether or not to remember this device for future logins.  Used to help determine whether or not two factor authentication is required on future logins.


**Return Values**

Returns an instance of the [AuthSession class](auth_session.md) upon successful login, and null otherwise.  If null, you may obtain the reason for failure via the `Login::getFailReason()` method.


### Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Login;


// Init
$armor = new Armor();
$login = new Login($armor);

// Login user
if (!$session = $login->withPassword('some_username', 'mypassword')) {
    $reason = $login->getFailReason(); 
    echo "Invalid username or password.  Reason: $reason\n";

} else { 
    echo "Welcome to the client area!\n":
}

// $session is an instance of AuthSession class.
~~~


## Request Authentication

Once a user is logged in, authenticating future page requests is easily done via the `Armor::checkAuth()` method, which will either return an instance of the [AuthSession Class](auth_session.md) or null if not logged in.  For example:

~~~php
use apex\Armor\Armor;

// Load Armor, and authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// Get ArmorUser object of authenticated user
$user = $session->getUser();
~~~

That's all there is to it.  If you're utilizing multiple user types (eg. admin), you can check whether or not a user of a specific type is authenticated by passing the `$type` variable to the `checkAuth()` method.  For example, if a user with type `admin` logged in, you can authenticate with:

~~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth('admin')) { 
    die("This area is for admins only");
}

// Logged in as an admin
~~~


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




