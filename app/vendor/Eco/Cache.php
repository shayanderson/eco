<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2017 Shay Anderson <http://www.shayanderson.com>
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
	 * Global conf keys
	 */
	const CONF_COMPRESSION = 'compression';
	const CONF_ENCODING = 'encoding';
	const CONF_EXPIRE = 'expire';
	const CONF_METADATA = 'metadata';
	const CONF_PATH = 'path';
	const CONF_SERIALIZE = 'serialize';

	/**
	 * Global conf
	 *
	 * @var array
	 */
	private static $__conf = [
		self::CONF_COMPRESSION => false,
		self::CONF_ENCODING => false,
		self::CONF_EXPIRE => 0,
		self::CONF_METADATA => false,
		self::CONF_PATH => null,
		self::CONF_SERIALIZE => true
	];

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
	 * Exact cache key flag
	 *
	 * @var boolean
	 */
	private $__is_encoded_key = false;

	/**
	 * Use metadata flag
	 *
	 * @var boolean
	 */
	private $__is_metadata = false;

	/**
	 * Use serialization
	 *
	 * @var boolean
	 */
	private $__is_serialize = true;

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
		if(!self::$__conf[self::CONF_PATH])
		{
			throw new \Exception(__METHOD__ . ': global cache path is not set');
		}

		self::$__conf[self::CONF_PATH] = rtrim(self::$__conf[self::CONF_PATH],
			DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		// set defaults
		$this->compression(self::$__conf[self::CONF_COMPRESSION]);
		$this->encoding(self::$__conf[self::CONF_ENCODING]);
		$this->expire(self::$__conf[self::CONF_EXPIRE]);
		$this->metadata(self::$__conf[self::CONF_METADATA]);
		$this->__path = self::$__conf[self::CONF_PATH];
		$this->serialize(self::$__conf[self::CONF_SERIALIZE]);

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
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				return @gzuncompress(file_get_contents($this->getFilePath()));
			}

			return @unserialize(gzuncompress(file_get_contents($this->getFilePath())));
		}
		else if($this->__is_encode)
		{
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				return @base64_decode(file_get_contents($this->getFilePath()));
			}

			return @unserialize(base64_decode(file_get_contents($this->getFilePath())));
		}
		else
		{
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				return @file_get_contents($this->getFilePath());
			}

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
	 * Encode key to cache key
	 *
	 * @param mixed $key
	 * @return string
	 */
	public function encodeKey($key)
	{
		return sha1($key);
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

		return $this->__prefix
			. ( $this->__is_encoded_key ? $this->__key : $this->encodeKey($this->__key) );
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
	 * @param boolean $is_encoded_key
	 * @return void
	 */
	public function key($key, $is_encoded_key = false)
	{
		if($is_encoded_key)
		{
			$this->__is_encoded_key = true;
		}

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
		$this->__path = self::$__conf[self::CONF_PATH] . $this->__path_relative;
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
	 * Use serialization flag setter
	 *
	 * @param boolean $use_serialization
	 * @return void
	 */
	public function serialize($use_serialization)
	{
		$this->__is_serialize = (bool)$use_serialization;
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
			$path = self::$__conf[self::CONF_PATH];

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
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				$is_write = @file_put_contents($this->getFilePath(), gzcompress($value), LOCK_EX);
			}
			else
			{
				$is_write = @file_put_contents($this->getFilePath(), gzcompress(serialize($value)),
					LOCK_EX);
			}
		}
		else if($this->__is_encode)
		{
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				$is_write = @file_put_contents($this->getFilePath(), base64_encode($value),
					LOCK_EX);
			}
			else
			{
				$is_write = @file_put_contents($this->getFilePath(),
					base64_encode(serialize($value)), LOCK_EX);
			}
		}
		else
		{
			if(!$this->__is_serialize && !$this->__is_metadata)
			{
				$is_write = @file_put_contents($this->getFilePath(), $value, LOCK_EX);
			}
			else
			{
				$is_write = @file_put_contents($this->getFilePath(), serialize($value), LOCK_EX);
			}
		}

		if($is_write === false)
		{
			throw new \Exception('Failed to write cache file \'' . $this->getFilePath() . '\''
				. ' (check write permissions)');
		}
	}

	/**
	 * Global config setter
	 *
	 * @param mixed $key (array|string)
	 * @param mixed $value
	 * @return void
	 */
	public static function setGlobalConf($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				self::setGlobalConf($k, $v);
			}

			return;
		}

		if(isset(self::$__conf[$key]) || array_key_exists($key, self::$__conf))
		{
			self::$__conf[$key] = $value;
		}
	}
}