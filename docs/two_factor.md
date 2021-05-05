
# Two Factor Requests

You may secure any request / code behind two factor e-mail / SMS authentication by simply calling the `AuthSession::requireTwoFactor()` method where desired, and no code below that call will be executed until the user has successfully authenticated via 2FA.  For example:

~~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You're not logged in.");
}

// Require 2FA
$session->requireTwoFactor();

// Code below this line will not be executed until the user has successfully authenticated via e-mail / SMS authentication.
~~~


## How it Works

Upon calling the `requireTwoFactor()` method, the "two_factor_type" setting within the [ArmorPolicy Configuration](armorpolicy.md) is checked to determine which authentication method, if any, to use:

* `TwoFactorType::DISABLED` - None, and will not perform any two factor authentication.
* `TwoFactorType::PHONE_OR_EMAIL` - If user has a verified phone number, will use SMS authentication.  Otherwise, will use e-mail authentication.
* `TwoFactorType::OPTIONAL` - Uses whatever the user has defined within their profile.
* Any other value will be used as the authentication method (email, e-mail via otp, or phone).

Once determined, the HTTP request is encrypted and saved as a PSR-7 `ServerRequestInterface` object, the necessary e-mail / SMS message is sent, and the [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method is called.  This should display the necessary template to the user (ie. check e-mail and click link, or enter confirmation code and submit form).

Once authenticated (see below), the status of the session will be changed to `SessionStatus::TWO_FACTOR_AUTHORIZED`, and the next time it encounters the `requireTwoFactor()` method it will allow code execution to continue for the request.


## Verifying Authentication

For details on how to authenticate the different authentication methods, see the below links:

* [E-Mail](./two_factor_email.md)
* [E-Mail via OTP](./two_factor_email_otp.md)
* [Phone / SMS](./two_factor_phone.md)


