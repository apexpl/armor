
# Verify E-Mail Addresses

You may either require or optionally allow users to verify their e-mail address upon creation by clicking on a link within an e-mail message sent to them.  Within the [ArmorPolicy configuration](armorpolicy.md) there is a "verify_email" setting, which must be set to one of the following `VerifyEmail` enum constants:

* `VerifyEmail::REQUIRE` - E-mail with link will be sent upon creation, which user must click on.  User will not be able to successfully login until e-mail is verified.
* `VerifyEmail::OPTIONAL` - E-mail with link will be sent upon creation, which user must click on.  User will still be able to successfully login without issue.

For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\{RequireEmail, VerifyEmail};

$policy = new ArmorPolicy(
    require_email: RequireEmail::REQUIRE_UNIQUE, 
    verify_email: VerifyEmail::REQUIRE, 
    expire_verify_email_secs: 900   // Verification links will expire after 15 mins
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


## Part Two - Verify Hash

Your application must accept requests to the URL included within the e-mail, and call the `Apex\Armor\User\Verify\VerifyEmail::verify()` method, passing the `$armor_code` hash to it.  This method will either return the UUID of the user who was verified upon success, or null upon failure.

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

echo "Successfully verified e-mail address of user $uuid\n";
~~~

That's it.  With the above two steps in place, e-mail verification for users is fully implemented and working.


## Resend E-Mail Verification

If desired, you may have the e-mail verification resent to a user by calling the `Apex\Armor\User\Verify\VerifyEmail::init()` method and passing the `ArmorUser` object to it.  For example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Verify\VerifyEmail;

// Init armor, get user
$armor = new Armor();
$user = $armor->getUser('some_username');

// Resent e-mail verification
$verifier = new VerifyEmail($armor);
$verifier->init($user);
~~~

That's it, and another e-mail verification message with a new hash will be sent to the user.


## Unverified Logins

If the "verify_email" setting of the [ArmorPolicy configuration](armorpolicy.md) is set to `VerifyEmail::REQUIRE`, and a user with an unverified e-mail address logs in, the session status will be `SessionStatus::VERIFY_EMAIL`.  Also, the [AdapterInterface::handleSessionStatus()](adapter.md) method will be called, where you should stop the user from continuing and instead display a template requesting they verify their e-mail address.



