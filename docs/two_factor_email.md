
# Two Factor - E-Mail

You may require two factor authentication against any session by simply calling the `requireTwoFactor`()` method on the session, for example:

~~~php
use Apex\Armor\Armor;

// Get session
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in.");
}

// Require 2FA
$session->requireTwoFactor();

// Code below this line will only be executed after the request has been authenticated.
~~~


## Part One - Initiate 2FA

Once 2FA via e-mail has been initiated, the [AdapterInterface::sendEmail()](adapter.md) method will be called, which must send an e-mail to the user with verification link.  An `$armor_code` variable is passed to the method, which is the 48 character string that must be included in the link.  For example, you may include a URL within the e-mail such as:  

> `https://domain.com/verify/$armor_code`.

The [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method will also be called with a status of "email", which should display a template asking the user to check their e-mail and click the link.


## Part Two - Verify Hash

Your application must accept requests to the URL included within the e-mail, and call the `Apex\Armor\Auth\TwoFactor\TwoFactorEmail::verify()` method, passing the `$armor_code` hash to it.  This method will either call the [AdapterInterface::handleTwoFactorAuthorized()](./adapter/handleTwoFactorAuthorized.md) method, or return null on failure.

For example:

~~~php
use Apex\Armor\Armor
use Apex\Armor\Auth\TwoFactor\TwoFactorEmail;

// Get hash from URI
$parts = explode('/', $_SERVER['REQUEST_URI']);
$armor_code = array_pop($parts);

// Init Armor
$armor = new Armor();

// Verify hash
$verifier = new TwoFactorEmail($armor);
if (!$uuid = $verifier($armor_code)) { 
    die("Invalid hash, please check the URL");
}

/**
 * AdapterInterface::handleTwoFactorAuthorized() will be called here, which should parse the PSR-7 ServerRequest 
 * Accordingly and perform the request as normal.
 */
~~~

That's it.  With the above two steps in place, e-mail 2FA is fully implemented and working.



