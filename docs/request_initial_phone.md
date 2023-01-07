
# Define Phone After Registration

For quicker onboarding, you may allow users to register without a phone number, but require they verify a phone number upon login.  This can be done by setting the "verify_phone" variable within the [ArmorPolicy Configuration](armorpolicy.md) to `VerifyPhone::REQUIRE_AFTER_REGISTER`.

For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\RequireEPassword;

$policy = new ArmorPolicy(
    require_phone: RequirePhone::OPTIONAL, 
    verify_phone: VerifyPhone::REQUIRE_AFTER_REGISTER
);

$armor = new Armor
    policy: $policy
);
~~~

Optionally, you may define the policy settings within the container file (default ~/config/container.php) or within a saved Armor policy.  See the [Policy Manager[(policy_manager.md) page for details.


## Part One - Initial Login

Upon the user's first login, the [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method will be called with a status of `SessionStatus::DEFINE_PHONE` at which point you should display a template asking the user to define a phone number.  Upon obtaining their phone number, you can update their profile via the [ArmorUser::updatePhone()](./armoruser/updatePhone.md) method, for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Operations\Phone;

// Init Armor, get session
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// Get phone number from 'phone' + 'phone_country' form fields.
$phone = Phone::get();

// Update phone
$user = $session->getUser();
$user->updatePhone($phone);

// Display template with form, asking user to input six digit verification code.
~~~

Upon updating their profile with a phone number, the [AdapterInterface::sendSMS()](adapter.md) method will be called, which must send an SMS message to the user with verification code.  An `$armor_code` variable is passed to the method, which is the 6 digit verification code that the user must submit.  You should now display a template with a textbox asking the user to enter the six digit verification code.


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

That's it, and another e-mail verification message with a new code will be sent to the user.



