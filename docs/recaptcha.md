
# reCaptcha

Armor allows you to easily verify reCaptcha forms with the checkbox by passing your reCaptcha secret key to the `Apex\Armor\Auth\Web\reCaptcha::verify()` method.  This method will return a boolean whether or not the verification was successful, for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Web\ReCaptcha;

// Init
$armor = new Armor();
$recaptcha = new reCaptcha($armor);

// Verify
if (!$recaptcha->verify('your_secret_key')) { 
    echo "Could not verify you are human\n";
}
~~~








