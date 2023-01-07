
# Verify E-Mail Addresses via OTP

You may either require or optionally allow users to verify their e-mail address upon creation by entering a one-time verification code which is e-mailed to them.  Within the [ArmorPolicy configuration](armorpolicy.md) there is a "verify_email" setting, which must be set to one of the following `VerifyEmail` enum constants:

* `VerifyEmail::REQUIRE_OTP` - A one-time six digit confirmation code will be e-mailed upon creation, which user must enter in a form.  User will not be able to successfully login until e-mail is verified.
* `VerifyEmail::OPTIONAL_OTP` - A one-time six digit confirmation code will be e-mailed upon creation, which user must enter in a form.  User will still be able to successfully login without issue.

For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\{RequireEmail, VerifyEmail};

$policy = new ArmorPolicy(
    require_email: RequireEmail::REQUIRE_UNIQUE, 
    verify_email: VerifyEmail::REQUIRE_OTP, 
    expire_verify_email_secs: 900   // Six digit confirmation code will expire after 15 mins.
);

$armor = new Armor
    policy: $policy
);
~~~

Optionally, you may define the policy settings within the container file (default ~/config/container.php) or within a saved Armor policy.  See the [Policy Manager[(policy_manager.md) page for details.


## Part One - Send E-Mail

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

Upon user creation, the [AdapterInterface::sendEmail()](adapter.md) method will be called, which must send an e-mail to the user with verification link.  An `$armor_code` variable is passed to the method, which is the 6 digit verification code that the user must submit.

After user registration, you should display a template with a textbox, asking the user to check their e-mail and enter the verification code. 


## Part Two - Verify Code

Your application must obtain the six digit confirmation code from the user, and call the `Apex\Armor\User\Verify\VerifyEmailOTP::verify()` method, passing the verification code to it.  This method will either return the UUID of the user who was verified upon success, or null upon failure.

For example:

~~~php
use Apex\Armor\Armor
use Apex\Armor\User\Verify\VerifyEmailOTP;


// Init Armor
$armor = new Armor();

// Get auth session
if (!$session = $armor->checkAuth()) { 
    die("You're not logged in");
}

// Verify code
$verifier = new VerifyEmailOTP($armor);
if (!$uuid = $verifier($session->getUser(), $_POST['code'])) { 
    die("Invalid verification code");
}

echo "Successfully verified e-mail address of user $uuid\n";
~~~

That's it.  With the above two steps in place, e-mail verification for users is fully implemented and working.


## Resend E-Mail Verification

If desired, you may have the e-mail verification resent to a user by calling the `Apex\Armor\User\Verify\VerifyEmailOTP::init()` method and passing the `ArmorUser` object to it.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Verify\VerifyEmailOTP;

// Init armor, get user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Resent e-mail verification
$verifier = new VerifyEmailOTP($armor);
$verifier->init($user);
~~~

That's it, and another e-mail verification message with a new code will be sent to the user.


## Unverified Logins

If the "verify_email" setting of the [ArmorPolicy configuration](armorpolicy.md) is set to `VerifyEmail::REQUIRE_OTP`, and a user with an unverified e-mail address logs in, the session status will be `SessionStatus::VERIFY_EMAIL_OTP`.  Also, the [AdapterInterface::handleSessionStatus()](adapter.md) method will be called, where you should stop the user from continuing and instead display a template requesting they verify their e-mail address.



