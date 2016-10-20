## Cache Class
The `\Eco\Cache` class can be used for server-side caching, for example:
```php
// set global cache root directory (required)
\Eco\Cache::setGlobalPath(PATH_APP . 'var/cache');

// set cache object with cache key
$cache = new \Eco\Cache('article-14');

// check if cache exists
if(!$cache->has())
{
    // cache does not exists
    $data = 'the cache value';

    // write cache
    $cache->set($data);

    // output
    echo $data;
}
else
{
    // cache exists, output
    echo $cache->get();
}
```
All cache data is serialized so all PHP types can be cached, for example:
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
// <global cache path>/account
$cache->path('account');
```

### Cache Expire
An expire time can be set for cache files:
```php
// globally set for all cache files
\Eco\Cache::setGlobalExpire('5 minutes');

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
Cache file compression can be used (is disabled by default) and requires the ZLIB functions, for example:
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

### Global Class Methods
These are the `\Eco\Cache` methods use for global settings:
- `setGlobalCompression($use_compression)` - use cache file compression (requires ZLIB functions), cannot be used with encoding
- `setGlobalEncoding($use_encoding)` - use cache file encoding (base64), cannot be used with compression
- `setGlobalExpire($time)` - set global expire time
- `setGlobalMetadata($use_metadata)` - use cache file metadata
- `setGlobalPath($path)` - set global cache path

### Class Methods
These are the `\Eco\Cache` methods:
- `compression($use_compression)` - use cache file compression, cannot be used with encoding
- `delete()` - delete cache file
- `encoding($use_encoding)` - use cache file encoding, cannot be used with compression
- `expire($time)` - set expire time
- `extension($file_extension)` - set cache file extension (ex: `.cache`)
- `flush()` - flush entire cache directory, or subdirectory
- `formatKey($key)` - format cache key
- `get()` - get cache value
- `getFilePath()` - get cache file path
- `getKey()` - get cache key
- `getPath()` - get cache path
- `has()` - check if cache file exists (or is expired)
- `key($key)` - set key (if not set by class constructor)
- `metadata($use_metadata)` - use metadata
- `path($path)` - set cache subpath
- `prefix($name)` - set cache file prefix
- `set($value)` - write cache value