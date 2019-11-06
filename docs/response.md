## Response Class
The Response class is used to access response methods:
- `cookieRemove($key, $path)` - remove cookie
- `cookieSet($key, $value, $expire, [...])` - set cookie
- `header($key, $value)` - HTTP header setter
- `headerNoCache()` - send no cache headers
- `headerRemove($key)` - Remove HTTP header
- `json($data, $value)` - output JSON response with content-type header
- `redirect($location, $use_301)` - send redirect in response
- `statusCode($code)` - send HTTP response status code
- `type(string $type)` - send content-type in headers


### JSON Response
Here is a `json()` method example:
```php
eco::response()->json(['id' => 5, 'name' => 'some name']);
// outputs: {"id":5,"name":"some name"}

// or use as single key/value example:
eco::response()->json('id', 5);
// outputs: {"id":5}
```

### HTTP Response Status Code
The `statusCode()` method can be used to respond with custom HTTP response status codes. By default Eco handles `403`, `404` and `500` errors.

Example usage:
```php
use \Eco\System\Response;
// send 405 status code with response headers
eco::response()->statusCode(Response::CODE_METHOD_NOT_ALLOWED);
```

### Response Type
Example usage:
```php
// use preset
eco::response()->type(\Eco\System\Response::TYPE_TEXT_CSS);
// or custom
eco::response()->type('video/mpeg');
```