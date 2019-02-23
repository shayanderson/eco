## Socket Classes
The `\Eco\Socket\Client` and `\Eco\Socket\Server` classes are available for creating sockets (using streams, no PHP extension required).

### Client-Server Example
For this example the server can be setup using the route `'/server' => 'IndexController->server'`. The `IndexController` method `server` example:
```php
// listen on port 8081
$server = new \Eco\Socket\Server('127.0.0.1', 8081);
// create a respond only service (does not wait for client request data)
$server->listen(function(){
	return 'Server time: ' . date('H:i:s');
});
```
Next, start the server from the application root directory using CLI:
```bash
# php index.php server
```
Now a client can be setup to read from the server:
```php
// acccess port 8081
$client = new \Eco\Socket\Client('127.0.0.1', 8081);
// get server response
var_dump($client->read());
```
Example output:
```html
string(21) "Server time: 13:44:56"
```

### Client-Server Write Example
First, create a server route like in the example above. Next, create the server:
```php
```php
// listen on port 8081
$server = new \Eco\Socket\Server('127.0.0.1', 8081);
// create request service that will wait for client request data and respond
$server->listen(true, function($request){
	switch($request)
	{
		case 'getAbc':
			return '{"status":"ok","id":"' . uniqid() . '","data":"abc"}';
			break;

		case 'getXyz':
			return '{"status":"ok","id":"' . uniqid() . '","data":"xyz"}';
			break;
	}

	return '{"status":"error","message":"unknown request"}';
});
```
Next, start the server like in the example above:
```bash
# php index.php server
```
Now a client can be setup to write to the server and receive a response:
```php
// access port 8081
$client = new \Eco\Socket\Client('127.0.0.1', 8081);
// send data + get server response
var_dump($client->write('getXyz'));
```
Example output:
```html
string(49) "{"status":"ok","id":"5c65bab756769","data":"xyz"}"
```