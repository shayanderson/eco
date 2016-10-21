## Helper Functions
Eco offers a collection of helper functions that can be used to quickly access widely used methods and other functionality. The available helper functions are divided into files that can be included as desired:
- **core** - `PATH_LIB . 'vendor/Eco/helper/eco.php'`
- **alias** - `PATH_LIB . 'vendor/Eco/helper/alias.php'`
- **factory** - `PATH_LIB . 'vendor/Eco/helper/factory.php'`
- **flash** - `PATH_LIB . 'vendor/Eco/helper/flash.php'`
- **redirect** - `PATH_LIB . 'vendor/Eco/helper/redirect.php'`
- **request** - `PATH_LIB . 'vendor/Eco/helper/request.php'`
- **view** - `PATH_LIB . 'vendor/Eco/helper/view.php'`


### Core Helper Functions
The core helper functions are mostly used to quickly access Eco methods (shorthand) and include several other useful functions:
- `error($message, $code, $http_response_code)` - `eco::error()` alias
- `logger()` - `eco::log()` alias
- `pa($v)` - HTML or CLI friendly printer for all PHP types
- `session_exists()` - determine if a PHP session exists
- `stop()` - `eco::stop()` alias
- `view($template, $view_params)` - `eco::view()` alias


### Alias Helper Functions
The alias helper functions are alias functions for the core Eco methods:
- `breadcrumb()` - `eco::breadcrumb()` alias, example:

    ```php
    // access object
    breadcrumb()->add('x', '/y/z');
    // or use as function
    breadcrumb('x', '/y/z');
    ```

- `conf($file_path, $store)` - `eco::conf()` alias
- `filter()` - `eco::filter()` alias
- `flash()` - `eco::flash()` alias, example:
  ```php
  // access object
  flash()->set('x', 'y');
  // or use as function
  flash('x', 'y'); // set
  flash('x'); // get
  ```
- `format()` - `eco::format()` alias
- `keep()` - alias for eco::clear(), eco::get(), eco::has(), eco::set(), example:
  ```php
  // access keep object
  keep()->set('x', 'y');
  // or use as function
  keep('x', 'y'); // set
  $x = keep('x'); // get
  ```
- `param($id, $callback)` - `eco::param()` alias
- `request()` - `eco::request()` alias
- `session()` - `eco::session()` alias, example:
  ```php
  // access object
  session()->set('x', 'y');
  // or use as function
  session('x', 'y'); // set
  $x = session('x'); // get
  ```
- `validate()` - `eco::validate()` alias


### Factory Helper Functions
The factory helper functions can be used as object factory helpers:
- `&factory($args, $class_name, $use_as_single_arg)` - object factory helper
- `factory_props(&$object, $props, $use_prop_must_exist)` - object factory properties helper

Here are `factory()` function examples:
```php
class Car
{
    public $make;
    public $model;

    public function __construct($data = null)
    {
        if($data !== null)
        {
            $this->make = $data['make'];
            $this->model = $data['model'];
        }
    }

    public function loadObj(stdClass $obj)
    {
        $this->make = $obj->make;
        $this->model = $obj->model;
    }
}

// create object with constructor args
$car = factory(['make' => 'Ford', 'model' => 'F150'], 'Car');
// Car Object([make] => Ford, [model] => F150)

// or create + call method with single arg object
$ford = new stdClass;
$ford->make = 'Ford';
$ford->model = 'F150';
$car = factory($ford, ['Car', 'loadObj'], true);
// Car Object ([make] => Ford, [model] => F150)
```
All of the above examples will work with multidimensional arrays, for example:
```php
$ford = new stdClass;
$ford->make = 'Ford';
$ford->model = 'F150';

$honda = new stdClass;
$honda->make = 'Honda';
$honda->model = 'Accord';

$car = factory([$ford, $honda], ['Car', 'loadObj']);
// Array(
//    [0] => Car Object ([make] => Ford, [model] => F150)
//    [1] => Car Object ([make] => Honda, [model] => Accord)
// )
```
An object (or objects) can be created without having to use named keys, for example:
```php
// first change Car class constructor to:
//  public function __construct($make, $model)
//  {
//      $this->make = $make;
//      $this->model = $model;
//  }
$car = factory(['Ford', 'F150'], 'Car', /* turn off single arg */ false);
// Car Object([make] => Ford, [model] => F150)
```

Here is `factory_props()` function example:
```php
class Car
{
    public $make;
    public $model;
    public $color;

    public function __construct(array $data)
    {
        factory_props($this, $data);
    }
}

// create object
$car = factory(['make' => 'Ford', 'model' => 'F150', 'color' => 'White'], 'Car');
// Car Object([make] => Ford, [model] => F150, [color] => White)
```


### Flash Helper Functions
The flash helper functions are:
- `flash_alert($message)` - set alert message
- `flash_alert_get()` - get alert message(s)
- `flash_error($message)` - set error message
- `flash_error_get()` -get error message(s)

> The templates for the `alert` and `error` messages can be set with:
```php
eco::flash()->template('alert', [template]);
eco::flash()->template('error', [template]);
```


### Redirect Helper Function
The `redirect($location, $use_301)` function is a `eco::redirect()` alias.


### Request Helper Functions
The request helper functions are:
- `get($key)` - `eco::request()->get()` alias
- `get_has($key)` - `eco::request()->get_has()` alias
- `post($key)` - `eco::request()->post()` alias
- `post_has($key)` - `eco::request()->post_has()` alias


### View Helper Functions
The view helper functions are:
- `decorate($decorator, $value, $filter, $is_indexed_array)` - easily decorate arrays and objects
- `header_no_cache()` - send no cache header
- `html($value)` - prepare safe HTML output string
- `json($data, $value)` - easily output JSON response with content-type header

Here is are `decorate()` function examples:
```php
$str = decorate('ID: {$id}, Name: {$name}',
    ['id' => 4, 'name' => 'some name']);
```
This would output:
```html
ID: 4, Name: some name
```
Multidimensional arrays are supported, example:
```php
$str = decorate('ID: {$id}, Name: {$name}', [
    ['id' => 4, 'name' => 'name1'],
    ['id' => 5, 'name' => 'name2']
]);
```
Objects are supported (and array with objects), example:
```php
$user = new stdClass;
$user->id = 4;
$user->name = 'name';
$str = decorate('ID: {$id}, Name: {$name}', $user);
```
Filters can also be used to filter data, example:
```php
$str = decorate('ID: {$id}, Name: {$name}, Encoded-ID: {$id_enc}',
    ['id' => 4, 'name' => 'some name'],
    function($r) { $r->id_enc = base64_encode($r->id); return $r; });
```
Indexed arrays (one-dimensional) are also supported:
```php
$str = decorate('{$key}: {$value}<br />', ['one', 'two'], null, true);
// outputs:
// 0: one
// 1: two
```

Here is a `json()` function example:
```php
json(['id' => 5, 'name' => 'some name']);
// outputs: {"id":5,"name":"some name"}

// or use as single key/value example:
json('id', 5);
// outputs: {"id":5}
```