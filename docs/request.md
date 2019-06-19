## Request Class
The Request class is used to access request variables:
- `cookie($key)` - cookie value getter
- `cookieHas($key)` - cookie var exists
- `get($key)` - GET var getter
- `getHas($key)` - GET var exists
- `header($name)` - header value getter
- `headers()` - headers getter
- `input($convert_html_entities)` - input data getter
- `ipAddress()` - request IP address getter
- `isPost()` - is POST request
- `isSecure()` - is HTTPS request
- `method()` - request method getter
- `post($key)` - POST var getter
- `postHas($key)` - POST var exists
- `server($key)` - SERVER var getter
- `serverHas($key)` - SERVER var exists
- `uri($query_string)` - request URI getter