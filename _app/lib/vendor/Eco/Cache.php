<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2016 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

/**
 * Key/value cache
 *
 * @author Shay Anderson
 */
class Cache
{
	/**
	 * Global use compression flag
	 *
	 * @var boolean
	 */
	private static $__conf_compression = false;

	/**
	 * Global use encoding flag
	 *
	 * @var boolean
	 */
	private static $__conf_encode = false;

	/**
	 * Global expire time
	 *
	 * @var mixed
	 */
	private static $__conf_expire = 0;

	/**
	 * Global metadata flag
	 *
	 * @var boolean
	 */
	private static $__conf_is_metadata = false;

	/**
	 * Global cache path
	 *
	 * @var string
	 */
	private static $__conf_path;

	/**
	 * Expire time
	 *
	 * @var mixed
	 */
	private $__expire;

	/**
	 * File extension
	 *
	 * @var string
	 */
	private $__ext;

	/**
	 * Use compression flag (requires ZLIB functions)
	 *
	 * @var boolean
	 */
	private $__is_compression = false;

	/**
	 * Use encoding flag
	 *
	 * @var boolean
	 */
	private $__is_encode = false;

	/**
	 * Use metadata flag
	 *
	 * @var boolean
	 */
	private $__is_metadata = false;

	/**
	 * Cache key
	 *
	 * @var string
	 */
	private $__key;

	/**
	 * Cache path
	 *
	 * @var string
	 */
	private $__path;

	/**
	 * Cache relative path
	 *
	 * @var string
	 */
	private $__path_relative;

	/**
	 * Cache file prefix
	 *
	 * @var string
	 */
	private $__prefix;

	/**
	 * Value used with metadata load
	 *
	 * @var mixed
	 */
	private $__value;

	/**
	 * Init
	 *
	 * @param string $key (optional)
	 * @throws \Exception (invalid cache path)
	 */
	public function __construct($key = null)
	{
		if(!self::$__conf_path)
		{
			throw new \Exception(__METHOD__ . ': global cache path is not set');
		}

		// set defaults
		$this->compression(self::$__conf_compression);
		$this->encoding(self::$__conf_encode);
		$this->expire(self::$__conf_expire);
		$this->__path = self::$__conf_path;
		$this->metadata(self::$__conf_is_metadata);

		if(func_num_args())
		{
			$this->key($key);
		}
	}

	/**
	 * Read cache file
	 *
	 * @return mixed
	 */
	private function __read()
	{
		if($this->__is_compression)
		{
			return @unserialize(gzuncompress(file_get_contents($this->getFilePath())));
		}
		else if($this->__is_encode)
		{
			return @unserialize(base64_decode(file_get_contents($this->getFilePath())));
		}
		else
		{
			return @unserialize(file_get_contents($this->getFilePath()));
		}
	}

	/**
	 * Use compression flag setter (requires ZLIB functions)
	 *
	 * @param boolean $use_compression
	 * @return void
	 */
	public function compression($use_compression)
	{
		$this->__is_compression = (bool)$use_compression;
	}

	/**
	 * Delete cache file
	 *
	 * @return boolean
	 */
	public function delete()
	{
		return @unlink($this->getFilePath());
	}

	/**
	 * Use encoding flag setter
	 *
	 * @param boolean $use_encoding
	 * @return void
	 */
	public function encoding($use_encoding)
	{
		$this->__is_encode = (bool)$use_encoding;
	}

	/**
	 * Expire time setter
	 *
	 * @param mixed $time (ex: '30 seconds', or 0 for no expire)
	 * @return void
	 */
	public function expire($time)
	{
		$this->__expire = $time == 0 ? (int)$time : strtotime('-' . $time);
	}

	/**
	 * Cache file extension setter
	 *
	 * @param string $file_extension
	 * @return void
	 */
	public function extension($file_extension)
	{
		$this->__ext = $file_extension;
	}

	/**
	 * Flush entire cache directory
	 *
	 * @return void
	 */
	public function flush()
	{
		// recursive dir rm
		$r_rmdir = function($path, $is_root = false) use(&$r_rmdir)
		{
			if(is_dir($path))
			{
				if($dh = @opendir($path))
				{
					while(($f = readdir($dh)) !== false)
					{
						if($f === '.' || $f === '..') // ignore
						{
							continue;
						}

						$f = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $f;
						if(is_dir($f))
						{
							$r_rmdir($f);
						}
						else
						{
							@unlink($f);
						}
					}

					closedir($dh);
					unset($dh);
				}
				else
				{
					throw new \Exception('Failed to open cache directory \'' . $path . '\'');
				}

				if(!$is_root)
				{
					rmdir($path);
				}
			}
		};

		$r_rmdir($this->__path, true);
	}

	/**
	 * Format cache key
	 *
	 * @param mixed $key
	 * @return string
	 */
	public function formatKey($key)
	{
		return sha1($key);
	}

	/**
	 * Cache value getter
	 *
	 * @return mixed (false when no cache)
	 */
	public function get()
	{
		if($this->__value !== null) // already read for metadata
		{
			return $this->__value['value'];
		}

		if($this->has())
		{
			if($this->__is_metadata)
			{
				return $this->__read()['value'];
			}

			return $this->__read();
		}

		return false;
	}

	/**
	 * Cache file path getter
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->getPath() . $this->getKey() . $this->__ext;
	}

	/**
	 * Cache key getter
	 *
	 * @return string
	 * @throws \Exception (cache key not set)
	 */
	public function getKey()
	{
		if(!strlen($this->__key))
		{
			throw new \Exception(__METHOD__ . ': cache key has not been set');
		}

		return $this->__prefix . $this->formatKey($this->__key);
	}

	/**
	 * Cache path getter
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->__path;
	}

	/**
	 * Cache file exists (and is not expired) flag getter
	 *
	 * @return boolean
	 */
	public function has()
	{
		$file = $this->getFilePath();

		if(is_readable($file))
		{
			if(!$this->__expire) // never expires
			{
				return true;
			}

			if(!$this->__is_metadata) // check expire: file modification time
			{
				if(@filemtime($file) >= $this->__expire)
				{
					return true;
				}

				$this->delete();
				return false;
			}

			// check expire: metadata expire date
			$this->__value = $this->__read();
			if(isset($this->__value['metadata']))
			{
				$write = (int)@$this->__value['metadata']['write'];
				if($write >= $this->__expire)
				{
					return true;
				}

				$this->delete();
				return false;
			}
			else
			{
				$this->delete();
			}
		}

		return false;
	}

	/**
	 * Cache key setter
	 *
	 * @param mixed $key
	 * @return void
	 */
	public function key($key)
	{
		$this->__key = $key;
	}

	/**
	 * Use metadata flag setter
	 *
	 * @param boolean $use_metadata
	 * @return void
	 */
	public function metadata($use_metadata)
	{
		$this->__is_metadata = (bool)$use_metadata;
	}

	/**
	 * Cache relative path setter
	 *
	 * @param string $path
	 * @return void
	 */
	public function path($path)
	{
		$this->__path_relative = rtrim(ltrim($path, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR)
			. DIRECTORY_SEPARATOR;
		$this->__path = self::$__conf_path . $this->__path_relative;
	}

	/**
	 * Cache file prefix setter
	 *
	 * @param string $name
	 * @return void
	 */
	public function prefix($name)
	{
		$this->__prefix = $name;
	}

	/**
	 * Write cache
	 *
	 * @param mixed $value
	 * @return void
	 * @throws \Exception (write failed)
	 */
	public function set($value)
	{
		if($this->__path_relative) // verify relative dir(s)
		{
			$parts = explode(DIRECTORY_SEPARATOR, rtrim($this->__path_relative,
				DIRECTORY_SEPARATOR));
			$path = self::$__conf_path;

			foreach($parts as $v)
			{
				$path .= $v . DIRECTORY_SEPARATOR;

				if(!is_dir($path) && @mkdir($path) === false)
				{
					throw new \Exception('Failed to write cache directory \'' . $path . '\''
						. ' (check write permissions)');
				}
			}
		}

		if($this->__is_metadata)
		{
			$value = [
				'metadata' => [
					'write' => time()
				],
				'value' => $value
			];
		}

		$is_write = false;

		if($this->__is_compression)
		{
			$is_write = @file_put_contents($this->getFilePath(), gzcompress(serialize($value)),
				LOCK_EX);
		}
		else if($this->__is_encode)
		{
			$is_write = @file_put_contents($this->getFilePath(), base64_encode(serialize($value)),
				LOCK_EX);
		}
		else
		{
			$is_write = @file_put_contents($this->getFilePath(), serialize($value), LOCK_EX);
		}

		if($is_write === false)
		{
			throw new \Exception('Failed to write cache file \'' . $this->getFilePath() . '\''
				. ' (check write permissions)');
		}
	}

	/**
	 * Global use compression flag setter (requires ZLIB functions)
	 *
	 * @param boolean $use_compression
	 * @return void
	 */
	public static function setGlobalCompression($use_compression)
	{
		self::$__conf_compression = (bool)$use_compression;
	}

	/**
	 * Global use encoding flag setter
	 *
	 * @param boolean $use_encoding
	 * @return void
	 */
	public static function setGlobalEncoding($use_encoding)
	{
		self::$__conf_encode = (bool)$use_encoding;
	}

	/**
	 * Global expire time setter
	 *
	 * @param mixed $time (ex: '30 seconds', or 0 for no expire)
	 * @return void
	 */
	public static function setGlobalExpire($time)
	{
		self::$__conf_expire = strtotime($time);
	}

	/**
	 * Global use metadata flag setter
	 *
	 * @param boolean $use_metadata
	 * @return void
	 */
	public static function setGlobalMetadata($use_metadata)
	{
		self::$__conf_is_metadata = (bool)$use_metadata;
	}

	/**
	 * Global cache path setter
	 *
	 * @param string $path
	 * @return void
	 */
	public static function setGlobalPath($path)
	{
		self::$__conf_path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}
}