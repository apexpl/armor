
# CSRF Validation

CSRF validiation is available, which vastly helps prevent bots from auto-submitting HTML forms, and is handled via the `Apex\Armor\Auth\Web\CSRF` class.

## Part One - Display HTML Form

You first must initialize the CSRF check by calling the `Apex\Armor\Auth\Web\CSRF::init()` method, for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Web\CSRF;

// Init
$armor = new Armor();
$csrf = new CSRF($armor);

// initialize check
list($name, $value) = $csrf->init();
~~~

This will return a two element array, which is the name and value of a form field you must place within your HTML form.  Add a hidden form field with the name `$name` and value of `$value`.


## Part Two - Verify CSRF

Upon form submission, you may validate the CSRF check by calling the `Apex\Armor\Auth\Web\CSRF::verify()` method, for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Auth\Web\CSRF;

// Init
$armor = new Armor();
$csrf = new CSRF($armor);

// Verify
if (!$csrf->verify()) { 
    die("No bots allowed");
}
~~~

That's it, and with the two above steps in place and implemented, your HTML forms will be far more secure against automated bot submissions.



