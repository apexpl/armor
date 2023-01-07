
# ArmorUser Class

The `Apex\Armor\User\ArmorUser` class provides objects / models for individual user accounts.  This section covers only the `ArmorUser` class itself, and if you need details on how to create and load users, please visit the [user Profiles](profiles.md) section of the documentation.

Please note, this class is not intended to be the main object class for your user management system.  Instead, it's expected your user class will extends this class.

Below is a brief example of how to load a user with UUID `u:618`:

~~~php
use Apex\Armor\Armor;

// Get profile
$armor = new Armor();
$user = $armor->getUuid('u:618');

// $user is ArmorUser object for UUID u:618
~~~


## Update Profile

The `ArmorUser` object includes the following methods to update various user profile information:

* [activate()](./armoruser/activate.md)
* [deactivate()](./armoruser/deactivate.md)
* [delete()](./armoruser/delete.md)
* [freeze()](./armoruser/freeze.md)
* [undelete()](./armoruser/undelete.md)
* [unfreeze()](./armoruser/unfreeze.md)
* [updateEmail()](./armoruser/updateEmail.md)
* [updatePassword()](./armoruser/updatePassword.md)
* [updatePhone()](./armoruser/updatePhone.md)
* [updateUsername()](./armoruser/updateUsername.md)


### Is / Has Methods

The `ArmorUser` class includes the following is / has methods, each of which returns a boolean and should be self exclamatory:

* hasEmail()
* hasPassword()
* hasPhone()
* hasUsername()
* isActive()
* isDeleted()
* isEmailVerified()
* isFrozen()
* isPending()
* isPhoneVerified()


## Get Methods

The `ArmorUser` class includes the following get methods, all of which should be self exclamatory:

* getCreatedAt() - Returns `DateTime` object.
* getEmail()
* getPassword() - Returns the Bcrypt hash, not the plain text password.
* getPhone()
* getPhoneCountryCode()
* getPhoneFormatted() - Returns phone number for display in web browser (eg. +1 416-555-1234)
* getPhoneNational()
* getRegCity()
* getRegCountry()
* getRegIpAddress()
* getRegLatitude()
* getRegLongitude()
* getRegProvinceISOCode()
* getRegProvinceName()
* getRegUserAgent()
* getTwoFactorFrequency()
* getTwoFactorType()
* getType() - Defaults to "user" unless otherwise specified upon user creation.
* getUnfreezeAt() - Returns `DateTime` object or null if user is not frozen.
* getUpdatedAt() - Returns `DateTime` object or null if user has never been updated.
* getUsername()
* getUuid()

## toArray / List Methods

The `ArmorUser` class contains the following methods to obtain additional information on the user:

* getActivityLogCount()
* getLoginHistoryCount()
* [listActivityLog()](./armoruser/listActivityLog.md)
* [listDevices()](./armoruser/listDevices.md)
* [listIpAllow()](./armoruser/listIpAllow.md)
* [listLoginHistory()](./armoruser/listLoginHistory.md)
* [toArray()](./armoruser/toArray.md) - useful when including user information within a template / view.



