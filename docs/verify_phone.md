
# Verify Phone Numbers via SMS

You may either require or optionally allow users to verify their phone number upon creation by entering a one-time verification code which is sent to them via SMS.  Within the [ArmorPolicy configuration](armorpolicy.md) there is a "verify_phone" setting, which must be set to one of the following `VerifyPhone` enum constants:

* `VerifyPhone::DISABLED` - No verification.
* `VerifyPhone::REQUIRE` - A one-time six digit confirmation code will be sent via SMS upon creation, which user must enter in a form.  User will not be able to successfully login until phone number is verified.
* `VerifyPhone::OPTIONAL` - A one-time six digit confirmation code will be sent via SMS upon creation, which user must enter in a form.  User will still be able to successfully login without issue.

For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\{RequirePhone, VerifyPhone};

$policy = new ArmorPolicy(
    require_phone: RequirePhone::REQUIRE_UNIQUE, 
    verify_phone: VerifyPhone::REQUIRE, 
    expire_verify_phone_secs: 900   // Six digit confirmation code will expire after 15 mins.
);

$armor = new Armor
    policy: $policy
);
~~~

Optionally, you may define the policy settings within the container file (default ~/config/container.php) or within a saved Armor policy.  See the [Policy Manager[(policy_manager.md) page for details.


]## Part One - Send SMS Message

Users should be created with `$auto_login` set to true, as verification of the six digit code requires the `ArmorUser` object of the user submitting the form.  FOr example:

~~~
use Apex\Armor\Armor;

$armor = new Armor();
$user = $armor->createUser(
    password: 'password12345', 
    username: 'jsmith', 
    email: 'jsmith@domain.com', 
    phone: '14165551234', 
    auto_login: true
);
~~~

Upon user creation, the [AdapterInterface::sendSMS()](adapter.md) method will be called, which must send an SMS message to the user with verification code.  An `$armor_code` variable is passed to the method, which is the 6 digit verification code that the user must submit.

After user registration, you should display a template with a textbox, asking the user to check their phone and enter the verification code. 


## Part Two - Verify Code

Your application must obtain the six digit confirmation code from the user, and call the `Apex\Armor\User\Verify\VerifyPhone::verify()` method, passing the verification code to it.  This method will either return the UUID of the user who was verified upon success, or null upon failure.

For example:

~~~php
use Apex\Armor\Armor
use Apex\Armor\User\Verify\VerifyPhone;

// Init Armor
$armor = new Armor();

// Get auth session
if (!$session = $armor->checkAuth()) { 
    die("You're not logged in");
}

// Verify code
$verifier = new VerifyPhone($armor);
if (!$uuid = $verifier($session->getUser(), $_POST['code'])) { 
    die("Invalid verification code");
}

echo "Successfully verified phone number of user $uuid\n";
~~~

That's it.  With the above two steps in place, phone verification for users is fully implemented and working.


## Resend Phone Verification

If desired, you may have the SMS verification resent to a user by calling the `Apex\Armor\User\Verify\VerifyPhone::init()` method and passing the `ArmorUser` object to it.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Verify\VerifyPhone;

// Init armor, get user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Resent e-mail verification
$verifier = new VerifyPhone($armor);
$verifier->init($user);
~~~

That's it, and another SMS message with a new code will be sent to the user.


## Unverified Logins

If the "verify_phone" setting of the [ArmorPolicy configuration](armorpolicy.md) is set to `VerifyPhone::REQUIRE`, and a user with an unverified phone number logs in, the session status will be `SessionStatus::VERIFY_PHONE`.  Also, the [AdapterInterface::handleSessionStatus()](adapter.md) method will be called, where you should stop the user from continuing and instead display a template requesting they verify their phone number.


