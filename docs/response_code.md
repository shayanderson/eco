## HTTP Response Code Class
The `\Eco\Http\ResponseCode` class can be used to respond with custom HTTP response status codes. By default Eco handles `403`, `404` and `500` errors.

Example usage:
```php
use Eco\Http\ResponseCode;
// send 405 status code with response headers
new ResponseCode(ResponseCode::HTTP_METHOD_NOT_ALLOWED);
```