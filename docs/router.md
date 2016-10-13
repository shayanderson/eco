## Router Class
The `eco::router()` method can be used to access internal router properties and methods.
> Note: these properties and methods can only used *after* `eco::run()` is called

### Router Properties
The Router class has two properties `route` and `request`:
```php
// something like 'user/:id'
echo 'The route is: ' . eco::router()->route;

// something like '/user/14'
echo 'The request is: ' . eco::router()->request;
```

### Router Methods
The Router class has several methods used for route parameters:
```php
if(eco::router()->hasParam('id'))
{
    echo 'The param ID value is: ' . eco::router()->getParam('id');
}

// get all params
$route_params = eco::router()->getParams();

// clear param
// now the param will not be passed to the route action
eco::router()->clearParam('id');
```
A 404 callback can be used to handle the 404 before an error is called:
```php
eco::router()->set404Callback(function($request){
	// do something
	return true; // handled, no 404 error called
	// if true is not return a 404 error is called
});
// static class method example:
// eco::router()->set404Callback(['ClassName', 'staticMethod']);
```