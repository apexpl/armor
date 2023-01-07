
# Brute Force Policy

The `Apex\Armor\Policy\BruteForcePolicy` class allows you to define the brute force policy to be used against brute force attacks.  Within the policy you define a set of rules, each of which define the number of invalid login attempts in a set period of time, and either how long to temporarily suspend the account or permanently deactivate it until it's manually reactivated.

## addRule()

You may add new rules to the policy with the `addRule()` method, which takes the following parameters:

Variable | Required | Type | Description
------------- |------------- |------------- |------------- 
`$attempts` | Yes | int | The number of sequential invalid login attempts to trigger rule.
`$seconds` | Yes | int | Number of seconds the number of invalid login attempts must be made within.
`$suspend_seconds` | No | int | The number of seconds to suspend the user from logging in once rule is triggered.  If set to 0, this will deactivate the user until they are manually reactivated.


## Example

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, BruteForcePolicy};

// Define brute force policy
$brute = new BruteForcePolicy();
$brute->addRule(5, 30, 3600);
$brute->addRule(20, 3600, 0);

// Define ArmorPolicy
$policy = new ArmorPolicy(
    username_column: UsernameColumn::EMAIL, 
    brute_force_policy: $brute
);

// Init Armor
$armor = new Armor(
    policy: $policy
);
~~~

With the above in place, after five invalid login attempts on a user's account within 30 seconds the account will be suspended for one hour.  After 20 invalid login attempts within a one hour period, the user will be deactivated until manually reactivated.


## Additional Methods

The `Apex\Armor\Policy\BruteForcePolicy` class also contains the following methods:

* getRules():array
* purgeRules():void







