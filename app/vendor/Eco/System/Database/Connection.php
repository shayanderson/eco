<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Database;

use Eco\System;

/**
 * Database connection
 *
 * @author Shay Anderson
 */
class Connection
{
	/**
	 * Max log entries (mem safe)
	 */
	const QUERY_LOG_MAX_ENTRIES = 1000;

	/**
	 * Query return types
	 */
	const QUERY_RETURN_TYPE_AFFECTED = 1;
	const QUERY_RETURN_TYPE_BOOLEAN = 2;
	const QUERY_RETURN_TYPE_ROWS = 3;

	/**
	 * Database name
	 *
	 * @var string
	 */
	private $__database;

	/**
	 * Global limit
	 *
	 * @var int
	 */
	private $__global_limit;

	/**
	 * Host
	 *
	 * @var string
	 */
	private $__host;

	/**
	 * Connection ID
	 *
	 * @var mixed
	 */
	private $__id;

	/**
	 * Query logging flag
	 *
	 * @var boolean
	 */
	private $__is_query_logging;

	/**
	 * Query log
	 *
	 * @var array
	 */
	private $__log = [];

	/**
	 * Password
	 *
	 * @var string
	 */
	private $__password;

	/**
	 * PDO object
	 *
	 * @var \PDO
	 */
	private $__pdo;

	/**
	 * Timezone
	 *
	 * @var string
	 */
	private static $__tz;

	/**
	 * User
	 *
	 * @var string
	 */
	private $__user;


	/**
	 * Init
	 *
	 * @param mixed $id
	 * @param string $host
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @paran int $global_limit
	 * @param boolean $is_query_logging
	 */
	public function __construct($id, $host, $database, $user, $password, $global_limit,
		$is_query_logging)
	{
		$this->__id = $id;
		$this->__host = $host;
		$this->__database = $database;
		$this->__user = $user;
		$this->__password = $password;
		$this->__global_limit = $global_limit;
		$this->__is_query_logging = $is_query_logging;
	}

	/**
	 * Query logger
	 *
	 * @param string $query
	 * @param mixed $params
	 * @return void
	 */
	private function __logQuery($query, $params = null)
	{
		if($this->__is_query_logging)
		{
			if(count($this->__log) > self::QUERY_LOG_MAX_ENTRIES)
			{
				array_shift($this->__log);
			}

			$this->__log[] = [
				'query' => $query,
				'params' => $params
			];
		}
	}

	/**
	 * Close the connection
	 *
	 * @return void
	 */
	public function close()
	{
		$this->__pdo = null;
	}

	/**
	 * Change database
	 *
	 * @param string $name
	 * @return void
	 */
	public function database($name)
	{
		$this->getPdo()->exec("USE {$name};");
	}

	/**
	 * Query log getter
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->__log;
	}

	/**
	 * PDO getter
	 *
	 * @return \PDO
	 */
	public function getPdo()
	{
		if($this->__pdo === null)
		{
			try
			{
				$opts = [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
				if(self::$__tz)
				{
					$opts[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET time_zone=\'' . self::$__tz . '\';';
				}
				$this->__pdo = new \PDO("mysql:host={$this->__host};dbname={$this->__database}",
					$this->__user, $this->__password, $opts);
			}
			catch(\PDOException $ex)
			{
				System::error($ex->getMessage(), null, System::conf()->_eco->log->category_database);
			}
		}

		System::db()->connectionReset(); // reset to default ID

		return $this->__pdo;
	}

	/**
	 * SQL LIMIT clause exists in query flag getter
	 *
	 * @param string $query
	 * @return boolean
	 */
	public function hasSqlLimitClause($query)
	{
		return stripos($query, 'LIMIT') !== false
			&& preg_match('/LIMIT[\s]+[\d,\s]+(OFFSET[\s]+[\d]+)?;?$/i', rtrim(trim($query), ';'));
	}

	/**
	 * Query is SELECT query flag getter
	 *
	 * @param string $query
	 * @return boolean
	 */
	public function isSelectQuery($query)
	{
		return strcasecmp('SELECT', substr(trim($query), 0, 6)) === 0;
	}

	/**
	 * Execute query
	 *
	 * @param string $query
	 * @param mixed $params
	 * @param int $return_type
	 * @param boolean $is_reconnect
	 * @return mixed
	 */
	public function query($query, $params = null, $return_type = null, $is_reconnect = false)
	{
		if(( $return_type !== null && $return_type === self::QUERY_RETURN_TYPE_ROWS )
				|| preg_match('/^[\s\(]*(select|show|describe|optimize|pragma|repair)/i', $query))
		{
			$return_type = self::QUERY_RETURN_TYPE_ROWS;

			if($this->__global_limit && $this->isSelectQuery($query)
				&& !$this->hasSqlLimitClause($query))
			{
				$query .= ' LIMIT ' . $this->__global_limit;
			}
		}

		$this->__logQuery($query, $params);
		if(is_array($params))
		{
			foreach($params as $v)
			{
				if(!is_scalar($v) && $v !== null) // only allow scalar params
				{
					System::error(__METHOD__ . ': invalid param type \'' . gettype($v)
						. '\' for query \'' . trim(substr($query, 0, 1000)) . '\'',
						System::ERROR_SERVER, 'Eco');
				}
			}
		}

		try
		{
			$sh = $this->getPdo()->prepare($query);
			if(@$sh->execute( is_array($params) ? $params : null ))
			{
				// determine return type
				if($return_type === self::QUERY_RETURN_TYPE_ROWS)
				{
					return $sh->fetchAll(\PDO::FETCH_CLASS);
				}
				else if(( $return_type !== null && $return_type === self::QUERY_RETURN_TYPE_AFFECTED )
					|| preg_match('/^\s*(delete|insert|replace|update)/i', $query))
				{
					return $sh->rowCount();
				}
				else // other
				{
					return true;
				}
			}
		}
		catch(\PDOException $ex) // catch exception for server has gone away error
		{
			// auto handle server has gone away error, do not handle if multiple reconnect
			if(strpos($ex->getMessage(), 'server has gone away') !== false && !$is_reconnect)
			{
				$this->close(); // close for auto reconnect
				return $this->query($query, $params, $return_type, true); // try again
			}

			System::error($ex->getMessage(), null, System::conf()->_eco->log->category_database);
		}

		return false;
	}

	/**
	 * Timezone setter for connection init command
	 *
	 * @param string $timezone
	 * @return void
	 */
	public static function setTimezone($timezone)
	{
		self::$__tz = $timezone;
	}
}