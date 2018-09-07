## HTTP Request Class
> **Required:** this class uses and requires the [PHP cURL Library](http://php.net/manual/en/book.curl.php)

The `\Eco\Http` class can be used to issue HTTP requests (`GET`, `POST`, `DELETE`, `HEAD`, `PATCH`, `PUT`). Here is a quick GET request example:
```php
$http = new \Eco\Http('http://www.example.com');
$response = $http->get();
// response is string or false on error

if($http->isError())
{
    echo 'Error: ' . $http->getError();
}
else
{
    echo 'Response: ' . $response;
}
```
Request parameters can be used:
```php
$http = new \Eco\Http('http://www.example.com?id=5&x=y');
$response = $http->get();

// or params as array
// $response = $http->get(['id' => 5, 'x' => 'y']);

// or set params separately
$http = new \Eco\Http('http://www.example.com');
$http->param('id', 5);
$http->param('x', 'y');
$response = $http->get();
```
POST request:
```php
$http = new \Eco\Http('http://www.example.com');
$response = $http->post(['id' => 14]);
```
HEAD request:
```php
$http = new \Eco\Http('http://www.example.com');
$response = $http->head();
// for head request the response is either true or false (error)
```

### Class Properties
The `\Eco\Http` class properties are used a configuration settings for the request. The class properties are:
- `$cert_file_path` - certificate file path (used with verify peer)
- `$force_tls_v1_2` - force TLS v1.2 connection
- `$headers` - add headers like `['Accept-Language: en-US', 'Accept-Encoding: gzip, deflate']`
- `$headers_get` - include headers in response
- `$proxy` - proxy server IP address and port like `1.2.3.4:8080`
- `$redirects_ignore` - ignore request redirects
- `$referer` - request referer
- `$timeout` - max seconds to allow cURL functions to execute (use `0` to wait indefinitely)
- `$timeout_connection` - seconds to wait while trying to connect (use `0` to wait indefinitely)
- `$user_agent` - request user agent
- `$verify_peer` - verify peers certificate

### Class Methods
- `delete(array $params = null)` - send DELETE request
- `get(array $params = null)` - send GET request
- `getError()` - last error message getter
- `getErrorNumber()` - last error number getter
- `getResponseCode()` - HTTP response code getter
- `getUrl()` - request URL getter
- `head(array $params = null)` - send HEAD request
- `isError()` - check if connection error occurred
- `param(string $id, mixed $value)` - request param setter
- `patch(array $params = null)` - send PATCH request
- `post(array $params = null)` - send POST request
- `put(array $params = null)` - send PUT request
