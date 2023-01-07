
# AdapterInterface::onUpdate()

Called when a user's profile information is updated (e-mail address, password, status, et al).  Useful in cases where you're storing profile information in an external data source such as redis.

> `void AdapterInterface::onUpdate(ArmorUserInterface $user, string $column, string | bool $new_value)`

**Parameters**

Variable | Type | Description
------------- |------------- |------------- 
`$user` | ArmorUserInterface | The user who's profile was updated.
`$column` | string | The column that was updated, will be one of:  `username, password, email, phone, is_active, is_deleted, is_frozen, two_factor_type, two_factor_frequency`
`$new_value` | string / bool | The value the column was updated.  Will be a boolean if the column is one of: `is_active, is_deleted, is_frozen`.




