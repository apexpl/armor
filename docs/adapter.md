
# Adapter Interface Class

Armor does not provide either, sending of e-mail / SMS messages, or rendering of templates / views.  Instead, it's expected your existing application already contains this functionality and it simply needs to be tied into Armor, which is where the Adapter Interface comes in.  However, if desired there is a default `MercuryAdapter.php` adapter in place which you may use if preferred, and see below for details.

This is a small interface, which you may view at ~/src/Interfaces/AdapterInterface.php, and it's expected you will write your own PHP class that implements this interface.  Within the container file (defaults to ~/config/container.php) you must change the `AdapterInterface::class` item to the location of your PHP class, for example:

~~~
AdapterInterface::class => Apex\Armor\Adapters\MySiteAdapter::class, 
~~~


## Available Methods

The AdapterInterface contains the following methods, and click on the below links for details:

* [getUuid()](./adapter/getUuid.md)
* [sendEmail()](./adapter/sendEmail.md)
* [sendSMS()](./adapter/sendSMS.md)
* [handleSessionStatus()](./adapter/handleSessionStatus.md)
* [handleTwoFactorAuthorized()](./adapter/handleTwoFactorAuthorized.md)
* [requestInitialPassword()](./adapter/requestInitialPassword.md)
* [requestResetPassword()](./adapter/requestResetPassword.md)
* [pendingPasswordChange()](./adapter/pendingPasswordChange.md) 
* [onUpdate()](./adapter/onUpdate.md)


## Mercury Integration

Armor comes installed with the default MercuryAdapter.php adapter class, which utilizes the <a href="https://github.com/apexpl/mercury">Apex Mercury</a> package for sending e-mail and SMS messages.  If preferred, you may quite easily keep this integration instead of implementing your existing application's functionality.

To do this, copy the `sendEmail()` and `sendSMS()` methods from the MercuryAdapter.php class into your own PHP class.  Next, within the container file (defaults to ~/config/container.php) you need to modify the following two items:

* `NexmoConfig::class` - Update with your Nextmo account details.
* `Emailer::class` - Modify with SMTP information, or remove it entirely to send e-mails via PHP's mail() function.

That's it, with that in place Armor will send out both, e-mail and SMS messages without issue.  The e-mail messages used can be found within the ~/config/ directory, although you may wish to modify the `sendEmail()` method accordingly and change how e-mail messages are retrieved / generated.


## Example Syrus Implementation

While implementing Armor into your back-end application, you may wish to look through the [Example Syrus Implementation](syrus_example.md) for an example implementation of Armor.


