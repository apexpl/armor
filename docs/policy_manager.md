
# Policy Manager

For convenience, the `Apex\Armor\Policy\PolicyManager` class allows you to save and reuse [ArmorPolicy configurations](armorpolicy.md).  When instantiating the central `Armor` class, instead of passing a full `ArmorPolicy` object you may simply pass the name of a policy, which will then be loaded from the database.


## Save Policies

You may use the `savePolicy()` method to save an `ArmorPolicy` to the database for later reuse, for example:

~~~php
use Apex\Armor\Armor;
use Apex\Armor\Policy\{ArmorPolicy, PolicyManager};
use Apex\Armor\Enums\{UsernameColumn, RequireEmail, VerifyEmail};

// Define policy
$policy = new ArmorPolicy(
    username_column: UsernameColumn::EMAIL, 
    require_email: RequireEmail::REQUIRE_UNIQUE, 
    verify_email: VerifyEmail::REQUIRE
);

// Init Armor
$armor = new Armor();

// Save policy
$manager = new PolicyManager($armor);
$manager->savePolicy('my_policy', $policy);
~~~

Simply pass the desired name along with an `ArmorPolicy` object, and it will be saved in the database for future use.  


## Instantiating with Saved Policies

You may instantiate the central `Armor` class with a saved policy by passing the `$policy_name` to the constructor, for example:

~~~php
use Apex\Armor\Armor;

// Init Armor
$armor = new Armor(
    policy_name: 'my_policy'
);
~~~

Armor will load and set the policy named "my_policy" for the request.


## Load Policy by Name

Alternatively, you may call the `Armor::loadPolicy()` method after instantiation to load a saved policy for use, for example:

~~~
use Apex\Armor\Armor;

// Init Armor
$armor = new Armor();

// Load policy
$armor->loadPolicy('my_policy');
~~~

The policy "my_policy" will be loaded, and used throughout the instance.


## Additional Methods

Aside from the `SavePolicy()` method, the `Apex\Armor\Policy\PolicyManager` class also contains the following methods:

* loadPolicy(string $name):ArmorPolicy
* listPolicies():array
* deletePolicy(string $name):bool
* purgePolicies():void





