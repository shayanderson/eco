## Request Class
The Request class is used to access request variables:
- `cookie($key)` - cookie value getter
- `cookie_has($key)` - cookie var exists
- `cookie_remove($key, $path)` - remove cookie
- `cookie_set($name, $value, $expire, [...])` - set cookie
- `get($key)` - GET var getter
- `get_has($key)` - GET var exists
- `get_request_ip_address()` - request IP address getter
- `get_request_uri($query_string)` - request URI getter
- `input($key)` - input var getter
- `input_has($key)` - input var exists
- `is_request_post()` - is POST request
- `is_request_secure()` - is HTTPS request
- `post($key)` - POST var getter
- `post_has($key)` - POST var exists
- `request_server($key)` - SERVER var getter
- `request_server_has($key)` - SERVER var exists