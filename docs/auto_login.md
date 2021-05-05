
# Auto Login

If needed, such as offering the ability for administrators to login under a user's account to help diagnose support issues, Armor does allow the ability to auto-login via the `Apex\Armor\Auth\AutoLogin` class.  This is the exact same as a typical login session, except it bypasses the authentication checks and simply creates the [AuthSession Object](./auth_session.md).

## Auto-Login via UUID

You may auto-login via UUID, for example:

~~~
use Apex\Armor\Armor;
use Apex\Armor\Auth\AutoLogin

// Init
$armor = new Armor();
$login = new AutoLogin($armor);

// Login
$session = $login->loginUuid('u:321');

// $session is now an AuthSession object for user u:321, and cookie set in browser.
~~~


## Auto-Login via Username

You may auto-login via username, for example:

~~~
use Apex\Armor\Armor;
use Apex\Armor\Auth\AutoLogin

// Init
$armor = new Armor();
$login = new AutoLogin($armor);

// Login
$session = $login->loginUsername('jsmith', 'user');

// $session is now an AuthSession object for user jsmith, and cookie set in browser.
~~~


