## Global Variables
Global variables are controlled by the methods:
```php
// set a global variable
eco::set('user', new User(14));

// get a global variable
$user_name = eco::get('user')->name;

// check if global variable exists
if(eco::has('user')) // do something

// clear / delete global variable
eco::clear('user');
```
After a global variable is set it can be used anywhere in the application.
> The [`keep()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function is available