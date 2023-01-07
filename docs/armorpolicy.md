
# ArmorPolicy Configuration

Every instance of Armor requires an ArmorPolicy configuration object, which is a series of settings that defines how the system operates such as username column, whether or not e-mail / phone verification is required or optional, minimum password strength required, et al.  

If no ArmorPolicy is passed to the `Armor` constructor, the defaults within the container file (defaults to ~/config/container.php) are used.  Optionally, you may save and reuse policies by using the [Policy Manager](policy_manager.md) class.


## Constructor Parameters

All settings can be defind within the constructor parameters, which are all optional and described in the below table.  Many of the items are enums, and you may see their supported values within the corresponding constants file located within the `/src/Enums/` directory.

Variable | Type | Default | Description
------------- |------------- |------------- |------------- 
`$username_column` | Enums\UsernameColumn | USERNAME | Which database column is used as the username upon logging in.  Can be username, email, or phone
`$create_as_pending` | bool | false | Whether or not all new users are created as pending, meaning they are unable to login until manually activated by administrator.
`$require_password` | Enums/RequirePassword | REQUIRE | Whether the password field is disabled, required, or defined after registration by the user clicking on a link e-mailed to them.
`$require_email` | Enums/RequireEmail | REQUIRE | Whether or not the e-mail field is disabled, optional, required, or required and unique.
`$require_phone` | Enums/RequirePhone | OPTIONAL | Whether or not the phone field is disabled, optional, required, or required and unique.
`$verify_email` | Enums/VerifyEmail | DISABLED | Whether or not e-mail verification is disabled, required, or optional.  Can be either clicking on a link within a e-mail message, or entering a confirmation code that is e-mailed to them.
`$verify_phone` | Enums/VerifyPhone | DISABLED | Whether or not phone verification via SMS is disabled, required, optional, or required after registration upon first login.
`$two_factor_type` | Enums/TwoFactorType | DISABLED | Which two factor authentication method to use.  Can be one of: disabled, optional and let user define, e-mail, e-mail via OTP, phone via SMS, or PGP.
`$two_factor_frequency` | Enums/TwoFactorFrequency | DISABLED | Whether two factor authentication during login is disabled, optional and defind by user, or required either for every login or only when a new device / browser is detected.
`$default_two_factor_type` | Enums/TwoFactorType | DISABLED | Only applicable if `$two_factor_type` is set to `OPTIONAL`, and is the two factor type to create all new users with.
`$default_two_factor_frequency` | Enums/TwoFactorFrequency | DISABLED | Only applicable if `$two_factor_frequency` is set to `OPTIONAL`, and is the two factor frequency to create all new users with.
`$min_password_strength` | Enum/MinPasswordStrength | MEDIUM | The minimum password strength required upon user creation and password changes.
`$min_username_length` | int | 0 | The minimum character lengths of usernames.  0 for disabled.
`$expire_verify_email_secs` | int | 600 | Number of seconds before verification links sent via e-mail expire.
`$expire_verify_phone_secs` | int | 600 | Number of seconds before confirmation codes sent via SMS expire.
`$expire_session_inactivty_secs` | int | 1800 | Number of seconds of inactivty before a user is automatically logged out.
expire_redis_session_secs | int | 0 | If greater than 0, the redis key that holds each auth session will expire after this number of seconds instead of the "expire_session_inactivty_secs" setting.  This allows Armor to gracefully handle a `SessionStatus::EXPIRED` status instead of abruptly ending an inactive session with no reasoning.  Useful if using the [Armor-ApiKeys](https://github.com/apexpl/armor-apikeys) package for a REST API that utilizes refresh tokens.
lock_redis_expiration | bool | false | Useful if utilizing the [Armor ApiKeys](https://github.com/apexpl/armor-apikeys) package for a REST API and wish to enforce refresh tokens.  If true, the expiration time of the auth session created will be locked, whereas if fault it will be renewed for each page request.
`$remember_device_days| int | 90 | If users check the "remember me" box when logging in, Armor will remember their computer / browser for this many days.  Helps determine whether or not two factor authentication is required upon login.
`$remember_me_days` | int | 30 | If users check the "remember me" box upon logging in, Armor will store their username in a cookie for this number of days, which can later be retrived via the `Armor::getCookieUsername()` method.
`$enable_ipcheck` | bool | false | Whether or not to enable IP based restrictions for logins and user sessions.  Due to overhead required, only set this to true if you actually need it.
`$brute_force_policy` | BruteForcePolicy | - | The brute force policy used.  Please see the [Brute Force Policy](brute_force_policy.md) page for details.


## Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\{UsernameColumn, RequirePhone, RequireEmail, VerifyEmail};


// Define policy
$policy = new ArmorPolicy(
    username_column: UsernameColumn::EMAIL, 
    require_email: RequireEmail::REQUIRE_UNIQUE, 
    verify_email: VerifyEmail::REQUIRE
    require_phone: RequirePhone::DISABLED
);

// Init armor
$armor = new Armor
    policy: $policy
);

// Users no longer require a username or phone
$user = $armor->createUser(
    email: 'jsmith@gmail.com', 
    password: 'password12345'
);
~~~


## Get / Set Methods

All constructor parameters also have corresponding get / set methods within the `ArmorPolicy` class.  All methods are in proper PSR compliant calmel case, and for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\ArmorPolicy;
use Apex\Armor\Enums\VerifyPhone;

$policy = new ArmorPolicy();
$policy->setTwoFactorType('email');
$policy->setVerifyPhone(VerifyPhone::REQUIRE);

// Init Armor
$armor = new Armor(
    policy: $policy
);
~~~


