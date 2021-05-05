
# Armor - User and Session Management

Designed to provide a solid base foundation for development of a custom user management system, and provides highly configurable base functionality including collection and management of basic user info (username, password, e-mail, phone, geo-location data, et al), e-mail / phone verification, authenticated sessions, 2FA e-mail / SMS requests, user segregated AES256 bit encryption, and more.  This is not meant to be a user management system in and of itself, but instead is intended to be extended by one to provide a base foundation.  It supports:

* Easy implementation with only one eight method adapter interface, along with the templates / views.
* Easy storage and management of username, password, e-mail, phone number, and basic registration info (date created, geo-location data, et al).
* Multiple user groups, providing central management of different groups of users that may exist throughout your back-end application (eg. admins, customers, developers with API access, support staff, et al).
* Highly configurable with support for multiple policies, each of which consists of 21 different settings allowing for hundreds of different configurations.
* E-mail address and phone verification with built-in support for <a href="https://vonage.com">Vonage / Nexmo</a> for sending SMS messages.
* Easy one-line of code to secure any requests / code behind two factor e-mail / SMS authentication.
* 4096 bit RSA key-pair automatically generated for every user, allowing for segregated user-based AES256 encryption including multi-recipient encryption.
* User device management for both, "remember me" feature and mobile apps / Firebase messages.
* Optional per-user IP based restrictions.
* Historical activity log showing all actions taken against a user's account.
* Full login and session history for each user.
* Fully tested with mySQL, PostgreSQL, and SQLite.


## Extensions and Demo

Several extensions are available providing functionality for different authentication schemas:

* PGP - [https://github.com/apexpl/armor-pgp/](https://github.com/apexpl/armor-pgp/)
* API Keys - [https://github.com/apexpl/armor-apikeys/](https://github.com/apexpl/armor-apikeys/)
* x509 Certs - [https://github.com/apexpl/armor-x509/](https://github.com/apexpl/armor-x509/)

An example implementation using the [Syrus template engine](https://github.com/apexpl/syrus/) can be found at:

* Website - [https://armor.demo.apexpl.io/](https://armor.demo.apexpl.io/)
* Github - [https://github.com/apexpl/armor-syrus/](https://github.com/apexpl/armor-syrus/)




## Installation

Install via Composer with:

> `composer require apex/armor`

Please see the implementation guide linked below.


## Table of Contents

1. [Implementation Guide](https://github.com/apexpl/armor/blob/master/docs/implementation.md)
    1. [Setup Database Connection](https://github.com/apexpl/armor/blob/master/docs/database_setup.md)
    2. [Install Database](https://github.com/apexpl/armor/blob/master/docs/database_install.md)
    3. [AdapterInterface Class](https://github.com/apexpl/armor/blob/master/docs/adapter.md)
    4. [Example Syrus Implementation](https://github.com/apexpl/armor/blob/master/docs/syrus_example.md)
2. [Armor Class](https://github.com/apexpl/armor/blob/master/docs/armor.md)
    1. [Container Definitions](https://github.com/apexpl/armor/blob/master/docs/container.md)
    2. [ArmorPolicy Configuration](https://github.com/apexpl/armor/blob/master/docs/armorpolicy.md)
    3. [Brute Force Policy](https://github.com/apexpl/armor/blob/master/docs/brute_force_policy.md)
    4. [Policy Manager](https://github.com/apexpl/armor/blob/master/docs/policy_manager.md)
3. [User Profiles (create, load, remove users)](https://github.com/apexpl/armor/blob/master/docs/profiles.md)
    1. [ArmorUser Class](https://github.com/apexpl/armor/blob/master/docs/armoruser.md)
    2. [Registration Info](https://github.com/apexpl/armor/blob/master/docs/registration_info.md)
    3. [Validator](https://github.com/apexpl/armor/blob/master/docs/validator.md)
    4. [Devices](https://github.com/apexpl/armor/blob/master/docs/devices.md)
    5. [Pending Password Changes](https://github.com/apexpl/armor/blob/master/docs/pending_password_changes.md)
    6. [IP Restrictions](https://github.com/apexpl/armor/blob/master/docs/ipallow.md)
    7. [Activity Log](https://github.com/apexpl/armor/blob/master/docs/userlog.md)
    8. [Login History](https://github.com/apexpl/armor/blob/master/docs/login_history.md)
4. [Verifying users](https://github.com/apexpl/armor/blob/master/docs/verify.md)
    1. [E-Mail](https://github.com/apexpl/armor/blob/master/docs/verify_email.md)
    2. [E-Mail via OTP](https://github.com/apexpl/armor/blob/master/docs/verify_email_otp.md)
    3. [Phone via SMS](https://github.com/apexpl/armor/blob/master/docs/verify_phone.md)
    4. [Reset Password](https://github.com/apexpl/armor/blob/master/docs/reset_password.md)
    5. [Define Password After Registration](https://github.com/apexpl/armor/blob/master/docs/request_initial_password.md)
    6. [Define Phone After Registration](https://github.com/apexpl/armor/blob/master/docs/request_initial_phone.md)
5. Login and Auth Sessions
    1. [Login and Request Authentication](https://github.com/apexpl/armor/blob/master/docs/login.md)
    2. [Auto Login](https://github.com/apexpl/armor/blob/master/docs/auto_login.md)
    3. [AuthSession Class](https://github.com/apexpl/armor/blob/master/docs/auth_session.md)
    4. [Encrypt / Decrypt Data](https://github.com/apexpl/armor/blob/master/docs/session_encrypt.md)
    5. [Session Attributes](https://github.com/apexpl/armor/blob/master/docs/session_attributes.md)
    6. [CSRF](https://github.com/apexpl/armor/blob/master/docs/csrf.md)
    7. [reCaptcha](https://github.com/apexpl/armor/blob/master/docs/recaptcha.md)
6. [Two Factor Requests](https://github.com/apexpl/armor/blob/master/docs/two_factor.md)
    1. [E-Mail](https://github.com/apexpl/armor/blob/master/docs/two_factor_email.md)
    2. [E-Mail via OTP](https://github.com/apexpl/armor/blob/master/docs/two_factor_email_otp.md)
    3. [Phone via SMS](https://github.com/apexpl/armor/blob/master/docs/two_factor_phone.md)
7. AES Encryption
    1. [User Based Encryption](https://github.com/apexpl/armor/blob/master/docs/encrypt_user.md)
    2. [Password Based Encryption](https://github.com/apexpl/armor/blob/master/docs/encrypt_password.md)



## Basic Usage

~~~php
use Apex\Armor\Armor;

// Create user
$armor = new Armor();
$user = $armor->createUser('', 'password12345', 'jsmith', 'jsmith@domain.com', '14165551234');
$uuid = $user->getUuid();

// Get user by UUID
$user = $armor->getUuid($uuid);

// Update e-mail address
$user->updateEmail('new@domain.com');


// Check if request is authenticated session
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// Require two factor authentication
$session->requireTwoFactor();

// Code below this line will not be executed until authenticated via e-mail / phone.

// Encrypt data to user's RSA key
$data_id = $session->encryptData('some sensitive data');

// Decrypt data at a later date
$text = $session->decryptData($data_id);
~~~


## Support

If you have any questions, issues or feedback, please feel free to drop a note on the <a href="https://reddit.com/r/apexpl/">ApexPl Reddit sub</a> for a prompt and helpful response.


## Follow Apex

Loads of good things coming in the near future including new quality open source packages, more advanced articles / tutorials that go over down to earth useful topics, et al.  Stay informed by joining the <a href="https://apexpl.io/">mailing list</a> on our web site, or follow along on Twitter at <a href="https://twitter.com/mdizak1">@mdizak1</a>.



