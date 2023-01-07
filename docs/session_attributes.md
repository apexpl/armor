
# Session Attributes

Armor allows you to set and get additional attributes on each individual [AuthSession Object](./auth_session.md) through the below methods.  This allows you to store any necessary custom information with authenticated sessions.  Please note, these attributes only last the length of the session, and are lost once the session is destroyed and/or a new session is created with a new login.


## Attribute Methods

The following methods are available via instances of the [AuthSession Class](auth_session.md):

* `void setAttribute(string $name, string $value)` - Set an attribute
* `?string getAttribute(string $name)` - Get value of an attribute.
* `array getAttributes()` - Get associative array of all attributes stroed within the session.
* `bool delAttribute(string $name)` - Delete an attribute from a session.


## Example

~~php
use Apex\Armor\Armor;

// Authenticate request
$armor = new Armor();
if (!$session = $armor->checkAuth()) { 
    die("You are not logged in");
}

// Set attribute
$session->setAttribute('city', 'Vancouver');

// On a later request, get the attribute
$city = $session->getAttribute('city');
echo "City is: $city\n";

// Get all attributes
$attr = $session->getAttributes();
print_r($attr);

// Delete attribute
$session->delAttribute('city');
~~~




