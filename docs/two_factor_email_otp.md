
# Two Factor - E-Mail via OTP

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

Once 2FA via e-mail has been initiated, the [AdapterInterface::sendEmail()](adapter.md) method will be called, which must send an e-mail to the user with verification code.  An `$armor_code` variable is passed to the method, which is the 6 digit verification code that the user must submit.  

The [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method will also be called with a status of "email_otp", which should display a template that contains a form asking the user to submit the verification code.


## Part Two - Verify Code

Your application must obtain the confirmation code input by the user, and call the `Apex\Armor\Auth\TwoFactor\TwoFactorEmailOTP::verify()` method, passing the `$armor_code` hash to it.  This method will either call the [AdapterInterface::handleTwoFactorAuthorized()](./adapter/handleTwoFactorAuthorized.md) method, or return null on failure.

For example:

~~~php
use Apex\Armor\Armor
use Apex\Armor\Auth\TwoFactor\TwoFactorEmailOTP;

// Get user
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}
$user = $session->getUser();

// Verify hash
$verifier = new TwoFactorEmailOTP($armor);
if (!$uuid = $verifier($user, $_POST['code'])) { 
    die("Invalid code.  Please try again.");
}

/**
 * AdapterInterface::handleTwoFactorAuthorized() will be called here, which should parse the PSR-7 ServerRequest 
 * Accordingly and perform the request as normal.
 */
~~~

That's it.  With the above two steps in place, e-mail 2FA is fully implemented and working.




