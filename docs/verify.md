
# Verifying Users

Armor facilitates the verification of user e-mail addresses and phone numbers during both, initial registration and profile updates.  This verification can be either optional or required as defined within the [ArmorPolicy Configuration](armorpolicy.md), and e-mail verification can occur via either, clicking on a link that is e-mailed, or entering a one-time verification code that is e-mailed.

Only a minimal amount of work is required depending on the verification methods being used, as explained in the below links.  A few quick notes regarding account verification:

* Armor does not display any templates / views, and this is left up to you.
* Please see the [AdapterInterface::sendEmail()](./adapter/sendEmail.md) and [AdapterInterface::sendSMS()](./adapter/sendSMS.md) methods for details on how Armor sends e-mail / SMS messages.
* Upon initial registration, it's expected you know which template to show (ie. check your e-mail / phone) depending on your [ArmorPolicy Configuration](armorpolicy.md).
* If e-mail / phone verification is set to required, and an unverified user logs in, the [AdapterInterface::handleSessionStatus()](./adapter/handleSessionStatus.md) method is called, which should display the necessary template to disrupt their login session.
* If e-mail / phone required is set to required, when a user updates their e-mail address / phone number, it will not be updated in the database until it has been verified.
* The following variables within [ArmorPolicy Configuration](armorpolicy.md) affect account verification:
    * `verify_email` - Whether or not e-mail verification is required.
    * `verify_phone` - Whether or not phone verification is required.  If desired for quicker onboarding, this may be set to `RequirePhone::REQUIRE_AFTER_REGISTER` meaning no phone is required during registration, but is required upon user first logging in.
    * `require_password` - If desired, you may set this to `RequirePassword::REQUIRE_AFTER_REGISTER` meaning no password is required during registration, and instead user will be e-mailed a link which takes them to a page where they may define their password.


## Verification Methods

Please see the below links for details on implementation of methods used for verification:

1. [E-Mail](./verify_email.md)
2. [E-Mail via OTP](./verify_email_otp.md)
3. [Phone via SMS](./verify_phone.md)
4. [Reset Password](reset_password.md)
5. [Define Password After Registration](./request_initial_password.md)
6. [Define Phone After Registration](./request_initial_phone.md)





