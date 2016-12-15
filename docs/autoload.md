## Class Autoloading
The `\Eco\System::autoload($paths)` method is used to autoload classes. Initially this is setup in the `app/com/app.bootstrap.php` file:
```php
// class autoloading
eco::autoload([
	PATH_LIB,
	PATH_LIB . 'vendor'
]);
```
Now classes will be autoloaded from the `app/lib` and `app/lib/vendor` directories.