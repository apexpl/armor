
# Define Password After Registration

For quicker onboarding, you may set the `require_password` setting of the [ArmorPolicy Configuration](armorpolicy.md) to `RequirePassword::REQUIRE_AFTER_REGISTER`.  This will allow users to register without a password, and instead an e-mail will be sent which includes a link where they may define their password.

For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\RequireEPassword;

$policy = new ArmorPolicy(
    require_password: RequirePassword::REQUIRE_AFTER_REGISTER
);

$armor = new Armor 
    policy: $policy
);
~~~

Optionally, you may define the policy settings within the container file (default ~/config/container.php) or within a saved Armor policy.  See the [Policy Manager[(policy_manager.md) page for details.


## Part One - Send E-Mail

Upon user creation, the [AdapterInterface::sendEmail()](adapter.md) method will be called, which must send an e-mail to the user with verification link.  An `$armor_code` variable is passed to the method, which is the 48 character string that must be included in the link.

For example, you may include a URL within the e-mail such as:  

> `https://domain.com/verify/$armor_code`.

Please note, no additional steps are taken upon user creation.  It's up to you whether or not to display a template to the user saying they must check their e-mail and verify their e-mail address to continue.


## Part Two - Display Template

Your application must accept requests to the URL included within the e-mail, and call the `Apex\Armor\User\Verify\VerifyEmail::verify()` method, passing the `$armor_code` hash to it.  This method will call the [AdapterInterface::requestInitialPassword()](./adapter/requestInitialPassword.md) method, which must display a template with form allowing the user to define their password.  There are no special requirements for the template, just a textbox asking for the user's desired password.

For example:

~~~php
use Apex\Armor\Armor
use Apex\Armor\User\Verify\VerifyEmail;

// Get hash from URI
$parts = explode('/', $_SERVER['REQUEST_URI']);
$armor_code = array_pop($parts);

// Init Armor
$armor = new Armor();

// Verify hash
$verifier = new VerifyEmail($armor);
if (!$uuid = $verifier($armor_code)) { 
    die("Invalid hash, please check the URL");
}

/**
 * The AdapterInterface::requestInitialPassword() will have already been called at this point, and the 
 * necessary template asking user to define their password should now be displayed.
 */
~~~


## Part 3 - Form Submission

Upon submission of the prefious form, you simply need to call the `Apex\Armor\User\Verify\InitialPassword::finish()` method and pass the user's new password to it.  Nothing more needs to be done, and the user's password will be properly set in their profile.  For example:

~~~php
use Apex\Armor;
use Apex\Armor\User\Verify\InitialPassword;

// Init Armor
$armor = new Armor();
$verifier = new InitialPassword($armor);

// Finish, and set password
$verifier->finish($_POST['password']);

// Continue, and display welcome screen.
~~~

That's it.  With the above three steps in place, allowing users to define password after registration is fully implemented and working.


