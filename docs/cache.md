## Cache Class
The `\Eco\Cache` class can be used for server-side caching, for example:
```php
// the default cache write directory can be set in config file:
// app/com/conf/eco.conf.php

// set cache object with cache key
$cache = new \Eco\Cache('article-14');

// check if cache exists
if($cache->has()) // cache exists
{
    $data = $cache->get();
}
else // cache does not exists
{
    $data = 'the cache value';

    $cache->set($data); // write cache
}

echo $data; // output
```
All cache data is serialized by default so different PHP types can be cached, for example:
```php
$data = ['x' => 'y'];
$cache->set($data);
var_dump($cache->get());
// array(1) { ["x"]=> string(1) "y" }
```

### Cache Path
A cache subpath can be used:
```php
// this local cache will be written in the subpath:
// '<global cache path>/account'
$cache->path('account');
```

### Cache Expire
An expire time can be set for cache files:
```php
// the global expire time for all cache files can be set
// in config file: app/com/conf/eco.conf.php

// or set locally (override global expire)
$cache->expire('30 seconds');
```
> By default the expire is set to `0` which is never expire

### Cache Metadata
The default cache expire method checks the cache file modified time for determining if the cache has expired. If the file modified time does not work for an application a more precise expire time can be stored in cache file metadata. To use this option enable metadata:
```php
$cache->metadata(true);
```

### Cache File Compression
Cache file compression can be used (disabled by default) and requires the ZLIB functions, for example:
```php
$cache->compression(true);
```
> Compression cannot be used with encoding

### Cache File Encoding
Cache file value encoding (base64) can be used:
```php
$cache->encoding(true);
```
> Encoding cannot be used with compression

### Using Encoded Cache Key
A cache file can be loaded using the actual encoded cache key, for example:
```php
$cache = new \Eco\Cache;
// will load the cache file:
// '<global cache path>/a94a8fe5ccb19ba61c4c0873d391e987982fbbd3'
$cache->key('a94a8fe5ccb19ba61c4c0873d391x187982fbbd3', true);
```

### Global Configuration Settings
Global cache settings are set in the Eco configuration settings file `app/com/conf/eco.conf.php` in the `cache` section.

All the global cache settings are:
- `compression` - use cache file compression (requires ZLIB functions), cannot be used with encoding (default: `false`)
- `encoding` - use cache file encoding (base64), cannot be used with compression (default: `false`)
- `expire` - set global expire time (default: `0`, no expire)
- `metadata` - use cache file metadata (default: `false`)
- `path` - set global cache path (default: `PATH_APP . 'var' . DIRECTORY_SEPARATOR . 'cache'`)
- `serialize` - serialize the cache data, if metadata is used serialization is forced (default: `true`)

### Class Methods
These are the `\Eco\Cache` methods:
- `compression($use_compression)` - use cache file compression, cannot be used with encoding
- `delete()` - delete cache file
- `encodeKey($key)` - encode key to cache key
- `encoding($use_encoding)` - use cache file encoding, cannot be used with compression
- `expire($time)` - set expire time
- `extension($file_extension)` - set cache file extension (ex: `.cache`)
- `flush()` - flush entire cache directory, or subdirectory
- `get()` - get cache value
- `getFilePath()` - get cache file path
- `getKey()` - get cache key
- `getPath()` - get cache path
- `has()` - check if cache file exists (or is expired)
- `key($key, $is_encoded_key)` - set key (if not set by class constructor)
- `metadata($use_metadata)` - use metadata
- `path($path)` - set cache subpath
- `prefix($name)` - set cache file prefix
- `serialize($use_serialization)` - use cache data serialization
- `set($value)` - write cache value