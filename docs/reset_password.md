
# Reset Password

Users may reset their password in the standard way of entering their e-mail address / username into a form, clicking on a link that is e-mailed to them, then defining their new password.  

## Part One - Initiate Password Reset

This process can be initialized by calling the `byEmail()` or `byUsername()` methods within the `Apex\Armor\User\Verify\ResetPassword` class.  Both methods accept the same parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`username / $email` | Yes | No | The username / e-mail address of the user, depending on whether you're calling the `byUsername()` or `byEmail()` method.
`$type` | No | string | The type of user requesting a password reset.  Defaults to "user".

Both methods will return an integer defining the number of users a password reset was initiated on, or a null if no matching users were found.  For example, obtain the e-mail address via a form, and:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\User\Verify\ResetPassword; 

// Init Armor
$armor = new Armor();
$reset = new ResetPassword($armor);

// Init password reset
if (!$reset->byEmail($_POST['email'])) { 
    die("That e-mail address is not in the database.");
}

// Display template asking user to check their e-mail, and click on link.
~~~

Upon initiating the password reset, the [AdapterInterface::sendEmail()](adapter.md) method will be called, which must send an e-mail to the user with verification link.  An `$armor_code` variable is passed to the method, which is the 48 character string that must be included in the link.

For example, you may include a URL within the e-mail such as:  

> `https://domain.com/verify/$armor_code`.


## Part Two - Display Template

Your application must accept requests to the URL included within the e-mail, and call the `Apex\Armor\User\Verify\VerifyEmail::verify()` method, passing the `$armor_code` hash to it.  This method will call the [AdapterInterface::requestResetPassword()](./adapter/requestResetPassword.md) method, which must display a template with form allowing the user to define their password.  There are no special requirements for the template, just a textbox asking for the user's desired password.

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
 * The AdapterInterface::requestResetPassword() will have already been called at this point, and the 
 * necessary template asking user to define their password should now be displayed.
 */
~~~


## Part 3 - Form Submission

Upon submission of the prefious form, you simply need to call the `Apex\Armor\User\Verify\ResetPassword::finish()` method and pass the user's new password to it.  Nothing more needs to be done, and the user's password will be properly set in their profile.  For example:

~~~php
use Apex\Armor;
use Apex\Armor\User\Verify\ResetPassword;

// Init Armor
$armor = new Armor();
$verifier = new ResetPassword($armor);

// Finish, and set password
$verifier->finish($_POST['password']);

// Continue, and display welcome screen.
~~~

That's it.  With the above three steps in place, reset password functionality is fully implemented and working.



