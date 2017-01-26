# Eco
Eco is a PHP Framework for PHP 5.5+

Install example:
```
# wget shayanderson.com/eco.zip
# unzip ./eco.zip
```

#### Documentation Topics
- [**Routing**](#routing)
- [**Route Parameters**](#route-parameters)
- [**Views**](#views)
- [**Logging**](#logging)
- [**Error Handling**](#error-handling)
- [**Hooks**](#hooks)
- [**Configuration**](#configuration)
- [**Core Methods**](#core-methods)
- [**Database**](https://github.com/shayanderson/eco/blob/master/docs/database.md)
- [**Helper Classes**](#helper-classes)  ([Cache](https://github.com/shayanderson/eco/blob/master/docs/cache.md), [Form](https://github.com/shayanderson/eco/blob/master/docs/form.md), [HTTP](https://github.com/shayanderson/eco/blob/master/docs/http.md), [Model](https://github.com/shayanderson/eco/blob/master/docs/model.md), [Table](https://github.com/shayanderson/eco/blob/master/docs/table.md))
- [**Helper Functions**](https://github.com/shayanderson/eco/blob/master/docs/helper.md) ([core](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions), [alias](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions), [factory](https://github.com/shayanderson/eco/blob/master/docs/helper.md#factory-helper-functions), [flash](https://github.com/shayanderson/eco/blob/master/docs/helper.md#flash-helper-functions), [redirect](https://github.com/shayanderson/eco/blob/master/docs/helper.md#redirect-helper-function), [request](https://github.com/shayanderson/eco/blob/master/docs/helper.md#request-helper-functions), [view](https://github.com/shayanderson/eco/blob/master/docs/helper.md#view-helper-functions))
- [**Extending Eco**](#extending-eco)

## Routing
Basic routes can be setup in `app/com/bootstrap.php`, for example:
```php
// set routes
eco::route([
	// class method
	'/' => 'IndexController->home',
	// namespace
	'login' => 'Account\UserController->login',
	// callable
	'logout' => function() { /* do something */ },
	// more routes
]);
```
The request `/` would call the method `home` in the class `IndexController` in file `app/mod/IndexController.php`.

The request `/login` would call the method `login` in the class `\Account\UserController` in file `app/mod/Account/UserController.php`.

#### Route Callbacks
Route callbacks can be a callable name or callable (the first array element must be the controller/action):
```php
function auth() { /* do something */ }
eco::route('account/secure',
	['AccountController->secure', 'auth', function() { /* invoked */ }]);
```

#### Dynamic Route Setting
Dynamic route setters can be used for larger applications that have many routes, for example:
```php
eco::route('account*', 'AccountController::getRoutes()');
```
Now all requests that being with `/account` will load the routes using the method:
```php
class AccountController
{
    public static function getRoutes()
    {
        return [
            'account/login' => 'AccountController->login',
            // do not have to use class name if in same class:
            'account/logout' => 'logout'
        ];
    }
}
```

#### CLI Routing
CLI routing is simple to use, for example:
```php
eco:route('bin/test/:id', function($id) {
    echo 'CLI test, id: ' . $id;
});
```
Now a CLI command can be issued:
```
$ php index.php bin/test/5
Bin test: 5
```
> The above route would also work in a Web browser. To create a CLI only route (will not work in Web browser) add a `$` to the beginning of route, for example:
```php
// this route will only work as CLI command
// the request '/bin/test/5' in a Web browser would result in a 404 error
eco:route('$bin/test/:id', function($id) {
    echo 'CLI test, id: ' . $id;
});
```


## Route Parameters
Named route parameters:
```php
eco::route('user/:id', 'UserController->view');
```
The `:id` value will be passed to the callable or class method:
```php
class UserController
{
    public function view($id)
    {
        // do something
    }
}
```

#### Optional Parameters
Optional parameters can be used:
```php
eco::route('page/:id/:title?', function($id, $title = null) {});
```

#### Wildcard Parameters
Wildcard parameters example:
```php
eco::route('product/:info+', function($info) {
    // if request is '/product/1/abc/x'
    // $info is an array: ['1', 'abc', 'x']
});
```

#### Route Parameter Callbacks
Route parameter callbacks can be used in several different ways:
```php
eco::route('page/:title:strtoupper', 'PageController->view');
```
Or set parameter callbacks in array (the first array element must be the class method / callable):
```php
eco::route('page/:title', ['PageController->view', ['title' => 'strtoupper']]);
```
Also, global route parameter callbacks can be set, for example:
```php
// globally set route param callback
// now every ':id' param will use this callback
// global param callbacks are overwritten by local ones
eco::param('id', function($id) { return strtoupper($id); });
```

> Route callbacks can also be used with route parameter callbacks:
```php
eco::route('page/:title', [
  'PageController->view',
  'route_callback',
  function() { /* route callback */ },
  // route param callback
  ['title' => 'strtoupper']
]);
```

#### Route Parameter Regular Expressions
Route parameter regular expressions can be used, if the match fails the route will not be invoked:
```php
eco::route('user/:id@[0-9]+', ...);
```

#### Route Parameter Syntax
The order of syntax matters when using different types of parameters and/or combining callbacks and regular expressions, here is the correct syntax:
```php
// when using callback with regular expression
// the callback must come first:
eco::route('user/:id:strtoupper@[0-9]+', ...);

// use optional param and callback:
eco::route('user/:id/:name?:strtoupper', ...);

// use optional param and regular expression:
eco::route('user/:id/:name?@[a-z]+', ...);

// use optional param, callback and regular expression:
eco::route('user/:id/:name?:strtoupper@[a-z]+', ...);

// use wildcard param and callback:
eco::route('user/:params+:strtoupper', ...);

// use wildcard param and regular expression:
eco::route('user/:params+@[a-z]+', ...);

// use wildcard param, callback and regular expression:
eco::route('user/:params+:strtoupper@[a-z]+', ...);
```


## Views
The view object can be used in a route actions:
```php
class PageController
{
    public function get($id)
    {
        $page = PageModel::get($id);

        // set view param
        eco::view()->set('title', $page->title);

        // or use view() helper function
        // view()->set('title', $page->title);

        // or like this:
        view()->author = $page->author;

        // load template file 'page/view'
        // and can also set view param 'content'
        view('page/view', [
            'content' => $page->content
        ]);
    }
}
```
> Route parameters can also be accessed using [`Router`](https://github.com/shayanderson/eco/blob/master/docs/router.md) class methods.

#### View Template
The `app/tpl/page/view.tpl` file example:
```php
<?php include PATH_TEMPLATE_GLOBAL . 'header.tpl'; ?>
<h1><?=$title?></h1>
<p>Author: <?=$author?></p>
<?=$content?>
<?php include PATH_TEMPLATE_GLOBAL . 'footer.tpl'; ?>
```


## Logging
Logging messages example:
```php
eco::log()->error('Bad error / fatal');
eco::log()->warning('Warning conditions');
eco::log()->notice('Notice message');
eco::log()->debug('File has loaded');
```
Categories can be used when logging a message:
```php
// category 'payment'
eco::log()->error('Failed to access payment gateway', 'payment');
```
Debugging info can also be added:
```php
eco::log()->warning('Request failed', /* category optional */ null,
    ['request' => 'x', 'client' => 'y']);
```
Get entire log:
```php
$log = eco::log()->get();
```
Setup custom log handler example:
```php
eco::log()->setHandler(function($message, $level, $category, $info){
    // handle custom logging
    return true; // handled
});
```
> If the custom log handler callable does not return `true` the internal logger will continue as normal


## Error Handling
Trigger error examples:
```php
eco::error('Error message');
// or use helper function
error('Something bad');
// custom error code
error('Page not found', 404);
// add log category
error('Failed to load user', null, 'account');
// by default the HTTP error response code is sent
// to not send HTTP code use:
error('Page not found', 404, 'category' false);
```
The error message sent to the `eco::error()` function can be accessed using:
```php
$error_message = eco::errorGetLast();
```
> A [404 callback](https://github.com/shayanderson/eco/blob/master/docs/router.md#router-methods) can be used to handle the 404 before an error is called


## Hooks
Hooks can be used to load files or use callbacks during execution, examples:
```php
// called before routing starts
eco::hook(eco::HOOK_BEFORE, function() { /* do something */ });

// called before the route callable or class method is called
// include a file example:
eco::hook(eco::HOOK_MIDDLE, PATH_LIB . 'hook/middle.php');

// called after dispatching
eco::hook(eco::HOOK_AFTER, function() { /* do something */ });
```


## Configuration
Application and Eco configuration settings are handled separately.

#### Eco Configuration
The Eco configuration settings file is located at `app/com/conf/eco.conf.php` and contains all framework settings. All documentation for Eco configuration settings can be found in the file.

#### Application Configuration
Application configuration settings can be stored by Eco, example:
```php
// load config file(s)
eco::conf(PATH_CONF . 'app.conf.php');
eco::conf(PATH_CONF . 'api.conf.php');
// use config settings
$app_username = eco::conf()->app->username;
```
The configuration files must return an array, `app.conf.php` example:
```php
return [
    'app' => [
        'username' => 'x',
        'password' => 'y'
    ],
    'core' => [ /* more */ ]
];
```
> Configuration settings cannot be overwritten when using multiple files, so the primary array keys must be unique even in different files. Never use the primary array key `__eco__` which is used by Eco for internal framework settings.

Configuration settings can also be used separately from Eco, for example:
```php
// do not store internally
$conf = eco::conf(PATH_CONF . 'app.conf.php', false);
// use config settings
$app_username = $conf->app->username;
```


## Core Methods
Eco offers the following methods:
- [`eco::autoload($paths)`](https://github.com/shayanderson/eco/blob/master/docs/autoload.md) - class autoloader
- [`eco::breadcrumb()`](https://github.com/shayanderson/eco/blob/master/docs/breadcrumb.md) - access Breadcrumb class ([`breadcrumb()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::clear($key)`](https://github.com/shayanderson/eco/blob/master/docs/vars.md) - clear global variable
- [`eco::conf($file_path, $store)`](#configuration) - register / load application configuration settings file ([`conf()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::db($connection_id)`](https://github.com/shayanderson/eco/blob/master/docs/database.md) - access Database class ([`db()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)
- [`eco::error($message, $code, $category, $http_response_code)`](#error-handling) - trigger an error ([`error()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)
- [`eco::filter()`](https://github.com/shayanderson/eco/blob/master/docs/data.md#filter-class) - access data Filter class ([`filter()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::flash()`](https://github.com/shayanderson/eco/blob/master/docs/session.md#flash-class) - access session Flash class ([`flash()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::format()`](https://github.com/shayanderson/eco/blob/master/docs/data.md#format-class) - access data Format class ([`format()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::get($key)`](https://github.com/shayanderson/eco/blob/master/docs/vars.md) - get a global variable
- [`eco::has($key)`](https://github.com/shayanderson/eco/blob/master/docs/vars.md) - check if a global variable exists
- [`eco::hook($name, $callback)`](#hooks) - add a hook
- [`eco::log()`](#logging) - access Log class ([`logger()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)
- [`eco::model()`](https://github.com/shayanderson/eco/blob/master/docs/model.md) - Model class loader ([`model()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)
- [`eco::param($id, $callback)`](#route-parameter-callbacks) - map route parameter callback ([`param()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::redirect($location, $use_301)`](https://github.com/shayanderson/eco/blob/master/docs/redirect.md) - redirect method (overridable) ([`redirect()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#redirect-helper-function) helper function available)
- [`eco::request()`](https://github.com/shayanderson/eco/blob/master/docs/request.md) - access Request class ([`request`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::route($route, $action)`](#routing) - map route
- [`eco::router()`](https://github.com/shayanderson/eco/blob/master/docs/router.md) - access core Router class
- `eco::run()` - run the application
- [`eco::session()`](https://github.com/shayanderson/eco/blob/master/docs/session.md) - access Session class ([`session()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::set($key, $value)`](https://github.com/shayanderson/eco/blob/master/docs/vars.md) - set a global variable
- `eco::stop()` - gracefully stop the application ([`stop()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)
- [`eco::validate()`](https://github.com/shayanderson/eco/blob/master/docs/data.md#validate-class) - access data Validate class ([`validate()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#alias-helper-functions) helper function available)
- [`eco::view($template, $view_params)`](#views) - load view template file and access View class ([`view()`](https://github.com/shayanderson/eco/blob/master/docs/helper.md#core-helper-functions) helper function available)


## Helper Classes
Helper classes can be used to simplify common application tasks:
- [`\Eco\Cache`](https://github.com/shayanderson/eco/blob/master/docs/cache.md) - Server-side cache helper class
- [`\Eco\Form`](https://github.com/shayanderson/eco/blob/master/docs/form.md) - HTML form helper class
- [`\Eco\Http`](https://github.com/shayanderson/eco/blob/master/docs/http.md) - HTTP request helper class
- [`\Eco\Table`](https://github.com/shayanderson/eco/blob/master/docs/table.md) - HTML table helper class


## Helper Functions
Helper functions can be used to quickly access Eco core methods or other useful functionality. Find helper functions [documentation here](https://github.com/shayanderson/eco/blob/master/docs/helper.md).


## Extending Eco
Eco can be extended by extending the core `\Eco\System` class. Initially this is setup in the `app/com/bootstrap.php` file:
```php
// set Eco access class
class eco extends \Eco\System {}
```
The extending class can be used to add methods or override `\Eco\System` overridable methods.


[^ Back to Top](#)