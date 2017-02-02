<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2017 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

use Eco\System;
use Eco\System\Database\Connection;
use Eco\System\Database\Pagination;

/**
 * Database (MySQL/MariaDB)
 *
 * @author Shay Anderson
 */
class Database extends \Eco\Factory
{
	/**
	 * Active connection ID
	 *
	 * @var mixed
	 */
	private $__conn_id;

	/**
	 * Default connection ID
	 *
	 * @var mixed
	 */
	private $__default_conn_id;

	/**
	 * Connections
	 *
	 * @var array
	 */
	private static $__conns = [];

	/**
	 * Init connections
	 */
	protected function __construct()
	{
		if(isset(System::conf()->__eco__->database->connection))
		{
			foreach(System::conf()->__eco__->database->connection as $k => $v)
			{
				if(isset($v->host) && $v->host !== null) // register connection
				{
					$this->connectionRegister($k, $v->host, $v->database, $v->user, $v->password,
						isset($v->log) && $v->log);
				}
			}
		}
	}

	/**
	 * Create
	 *
	 * @param string $table
	 * @param mixed $data
	 * @param boolean $is_replace
	 * @param boolean $is_ignore
	 * @return int (affected)
	 */
	public function __add($table, $data, $is_replace, $is_ignore = false)
	{
		$params = [];
		$values = [];

		if(is_object($data)) // object
		{
			$arr = [];
			foreach(get_object_vars($data) as $k => $v)
			{
				$arr[$k] = $v;
			}
			$data = &$arr;
		}

		foreach($data as $k => $v)
		{
			if(is_array($v)) // SQL
			{
				if(isset($v[0]) && strlen($v[0]))
				{
					$values[] = $v[0];
				}
			}
			else // named param
			{
				$params[$k] = $v;
				$values[] = ':' . $k;
			}
		}

		return $this->__getConn()->query(( $is_replace ? 'REPLACE' : 'INSERT'
			. ( $is_ignore ? ' IGNORE' : null ) ) . ' INTO ' . $table . '('
			. implode(', ', array_keys($data)) . ') VALUES(' . implode(', ', $values) . ')',
			$params, Connection::QUERY_RETURN_TYPE_AFFECTED);
	}

	/**
	 * Call store procedure
	 *
	 * @param string $name
	 * @param array $params
	 * @param int $return_type
	 * @return mixed
	 */
	private function __callSp($name, $params, $return_type)
	{
		$p_str = '';
		if($params)
		{
			$p_str = rtrim(str_repeat('?,', count($params)), ',');
		}

		return $this->__getConn()->query('CALL ' . $name . '(' . $p_str . ')', $params,
			$return_type);
	}

	/**
	 * Connection getter
	 *
	 * @return \Eco\System\Database\Connection
	 */
	private function __getConn()
	{
		if($this->__hasConn($this->__conn_id))
		{
			return self::$__conns[$this->__conn_id];
		}
	}

	/**
	 * Connection exists flag getter
	 *
	 * @param mixed $connection_id
	 * @return boolean
	 * @throws \Exception (connection does not exist)
	 */
	private function __hasConn($connection_id)
	{
		if(!isset(self::$__conns[$connection_id]))
		{
			throw new \Exception(( count(self::$__conns)
				? 'Connection with ID \'' . $connection_id . '\' does not exist'
				: 'No database connections have been registered' ) . ' (' . __METHOD__ . ')');
		}

		return true;
	}

	/**
	 * Prepare params for query execution
	 *
	 * @param int $index
	 * @param array $params
	 * @return mixed (array|null)
	 */
	private function __prepParams($index, array $params)
	{
		if($index && $params && ( isset($params[$index]) || array_key_exists($index, $params) ))
		{
			if(is_array($params[$index])) // param in array
			{
				return $params[$index];
			}

			return array_slice($params, $index);
		}

		return null;
	}

	/**
	 * Prepare SQL for query execution
	 *
	 * @param string $sql
	 * @return string
	 */
	private function __prepSql($sql)
	{
		return $sql ? ' ' . rtrim(trim($sql), ';') : '';
	}

	/**
	 * Call store procedure
	 *
	 * @param string $name
	 * @param mixed $params
	 * @return boolean (false on error)
	 */
	public function call($name, $params = null)
	{
		return $this->__callSp($name, $this->__prepParams(1, func_get_args()),
			Connection::QUERY_RETURN_TYPE_BOOLEAN);
	}

	/**
	 * Call store procedure
	 *
	 * @param string $name
	 * @param mixed $params
	 * @return int (affected)
	 */
	public function callAffected($name, $params = null)
	{
		return $this->__callSp($name, $this->__prepParams(1, func_get_args()),
			Connection::QUERY_RETURN_TYPE_AFFECTED);
	}

	/**
	 * Call store procedure
	 *
	 * @param string $name
	 * @param mixed $params
	 * @return array
	 */
	public function callRows($name, $params = null)
	{
		return $this->__callSp($name, $this->__prepParams(1, func_get_args()),
			Connection::QUERY_RETURN_TYPE_ROWS);
	}

	/**
	 * Close the connection
	 *
	 * @return void
	 */
	public function close()
	{
		$this->__getConn()->close();
	}

	/**
	 * Commit a transaction
	 *
	 * @return boolean (false on fail)
	 */
	public function commit()
	{
		return $this->__getConn()->getPdo()->commit();
	}

	/**
	 * Current connection ID setter
	 *
	 * @param mixed $connection_id
	 * @return void
	 */
	public function connection($connection_id)
	{
		if($this->__hasConn($connection_id))
		{
			$this->__conn_id = $connection_id;
		}
	}

	/**
	 * Register connection
	 *
	 * @param mixed $connection_id
	 * @param string $host
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 * @param boolean $query_logging
	 * @return void
	 * @throws \Exception (connection already exists)
	 */
	public function connectionRegister($connection_id, $host, $database, $user, $password,
		$query_logging = true)
	{
		if(isset(self::$__conns[$connection_id]))
		{
			throw new \Exception('Connection with ID \'' . $connection_id . '\' already exists');
		}

		if($this->__default_conn_id === null) // set default ID
		{
			$this->__default_conn_id = $connection_id;
		}

		self::$__conns[$connection_id] = new Connection($connection_id, $host, $database, $user,
			$password, (int)System::conf()->__eco__->database->global_limit, $query_logging);

		System::log()->debug('Database connection \'' . $connection_id
			. '\' registered for host \'' . $host . '\'', 'Eco');

		if(count(self::$__conns) === 1) // auto set current ID
		{
			$this->connection($connection_id);
		}
	}

	/**
	 * Reset connection ID to default ID
	 *
	 * @return void
	 */
	public function connectionReset()
	{
		$this->connection($this->__default_conn_id);
	}

	/**
	 * Count getter
	 *
	 * @param string $table
	 * @param mixed $params
	 * @return int
	 */
	public function count($table, $params = null)
	{
		$r = $this->__getConn()->query('SELECT COUNT(1) AS c FROM ' . $this->__prepSql($table),
			$this->__prepParams(1, func_get_args()), Connection::QUERY_RETURN_TYPE_ROWS);

		$r = (array)$r[0];
		return (int)$r['c'];
	}

	/**
	 * Delete
	 *
	 * @param string $table
	 * @param mixed $params
	 * @return int (affected)
	 */
	public function delete($table, $params = null)
	{
		return $this->__getConn()->query('DELETE FROM ' . $this->__prepSql($table),
			$this->__prepParams(1, func_get_args()), Connection::QUERY_RETURN_TYPE_AFFECTED);
	}

	/**
	 * Single row getter
	 *
	 * @param string $table
	 * @param mixed $params
	 * @return \stdClass (or null for no row)
	 * @throws \Exception (LIMIT clause exists in query)
	 */
	public function get($table, $params = null)
	{
		$table = $this->__prepSql($table);

		if(!$this->__getConn()->isSelectQuery($table))
		{
			$table = 'SELECT * FROM ' . $table;
		}

		if($this->__getConn()->hasSqlLimitClause($table))
		{
			throw new \Exception('Failed to get row, LIMIT clause already exists in query'
				. ' (' . __METHOD__ . ')');
		}

		$r = $this->__getConn()->query($table . ' LIMIT 1',
			$this->__prepParams(1, func_get_args()), Connection::QUERY_RETURN_TYPE_ROWS);

		return isset($r[0]) ? $r[0] : null;
	}

	/**
	 * All rows getter
	 *
	 * @param string $table
	 * @return array
	 */
	public function getAll($table)
	{
		return $this->__getConn()->query('SELECT * FROM ' . $table,
			$this->__prepParams(1, func_get_args()), Connection::QUERY_RETURN_TYPE_ROWS);
	}

	/**
	 * Table column names getter
	 *
	 * @param string $table
	 * @return array
	 */
	public function getColumns($table)
	{
		$r = $this->__getConn()->query('SHOW COLUMNS FROM ' . $table, null,
			Connection::QUERY_RETURN_TYPE_ROWS);

		$c = [];

		foreach($r as $v)
		{
			$c[] = array_values((array)$v)[0];
		}

		return $c;
	}

	/**
	 * Default connection ID getter
	 *
	 * @return mixed
	 */
	public function getDefaultConnectionId()
	{
		return $this->__default_conn_id;
	}

	/**
	 * Database table names getter
	 *
	 * @return array
	 */
	public function getTables()
	{
		$r = $this->__getConn()->query('SHOW TABLES', null, Connection::QUERY_RETURN_TYPE_ROWS);

		$t = [];

		foreach($r as $v)
		{
			$t[] = array_values((array)$v)[0];
		}

		return $t;
	}

	/**
	 * Row(s) exists flag getter
	 *
	 * @param string $table
	 * @param mixed $params
	 * @return boolean
	 */
	public function has($table, $params = null)
	{
		$r = $this->__getConn()->query('SELECT EXISTS(SELECT 1 FROM'
			. $this->__prepSql($table) . ') AS h', $this->__prepParams(1, func_get_args()),
			Connection::QUERY_RETURN_TYPE_ROWS);

		$r = (array)$r[0];
		return (int)$r['h'] > 0;
	}

	/**
	 * Insert ID getter
	 *
	 * @return mixed (int|string)
	 */
	public function id()
	{
		$id = $this->__getConn()->getPdo()->lastInsertId();

		return is_numeric($id) ? (int)$id : $id;
	}

	/**
	 * Create
	 *
	 * @param string $table
	 * @param mixed $data
	 * @param boolean $ignore
	 * @return int (affected)
	 */
	public function insert($table, $data, $ignore = false)
	{
		return $this->__add($table, $data, false, $ignore);
	}

	/**
	 * Query log getter
	 *
	 * @return array
	 */
	public function log()
	{
		return $this->__getConn()->getLog();
	}

	/**
	 * Execute query with pagination
	 *
	 * @staticvar \stdClass $conf
	 * @param string $query
	 * @param mixed $params
	 * @return \Eco\System\Database\Pagination
	 * @throws \Exception (LIMIT clause exists in query, or invalid settings)
	 */
	public function pagination($query, $params = null)
	{
		if($this->__getConn()->hasSqlLimitClause($query))
		{
			throw new \Exception('Failed to apply pagination to query,'
				. ' LIMIT clause already exists in query');
		}

		static $conf;
		if(!$conf) // init conf
		{
			$conf = System::conf()->__eco__->database->pagination;
			$conf->rpp = isset($conf->rpp) ? (int)$conf->rpp : 30;
		}

		static $page;
		if(!$page)
		{
			if(isset($conf->page->get_var) && !empty($conf->page->get_var))
			{
				if(isset($_GET[$conf->page->get_var]))
				{
					$page = $conf->page->encode
						? (int)System::format()->base64UrlDecode($_GET[$conf->page->get_var])
						: (int)$_GET[$conf->page->get_var];
				}
				else
				{
					$page = 1;
				}
			}
			else
			{
				throw new \Exception('Failed to initialize pagination, no page \'get_var\' has'
					. ' been set');
			}
		}

		$query = $this->__prepSql($query) . ' LIMIT '
			. (($page - 1) * $conf->rpp) . ',' . ( $conf->rpp + 1 );

		return new Pagination($this->__getConn()->query($query,
			$this->__prepParams(1, func_get_args()), Connection::QUERY_RETURN_TYPE_ROWS), $page,
			$conf);
	}

	/**
	 * Execute query
	 *
	 * @param string $query
	 * @param mixed $params
	 * @return mixed
	 */
	public function query($query, $params = null)
	{
		return $this->__getConn()->query($query, $this->__prepParams(1, func_get_args()));
	}

	/**
	 * Replace
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return int (affected)
	 */
	public function replace($table, $data)
	{
		return $this->__add($table, $data, true);
	}

	/**
	 * Roll back a transaction
	 *
	 * @return boolean (false on fail)
	 */
	public function rollback()
	{
		return $this->__getConn()->getPdo()->rollBack();
	}

	/**
	 * Begin transaction
	 *
	 * @return boolean (false on fail)
	 */
	public function transaction()
	{
		return $this->__getConn()->getPdo()->beginTransaction();
	}

	/**
	 * Truncate table
	 *
	 * @param string $table
	 * @return boolean (false on error)
	 */
	public function truncate($table)
	{
		return $this->__getConn()->query('TRUNCATE ' . $table, null,
			Connection::QUERY_RETURN_TYPE_BOOLEAN);
	}

	/**
	 * Update
	 *
	 * @param string $table
	 * @param array $params
	 * @return int (affected)
	 */
	public function update($table, array $params = null)
	{
		$p = [];
		$values = [];
		$args = [];

		foreach($params as $k => $v)
		{
			$is_arg = $k[0] === ':';
			$k = ltrim($k, ':');

			if(is_array($v)) // SQL
			{
				if(!$is_arg && isset($v[0]) && strlen($v[0]))
				{
					$values[] = $k . ' = ' . $v[0];
				}
			}
			else
			{
				$p[$k] = $v;
				if(!$is_arg)
				{
					$values[] = $k . ' = :' . $k;
				}
			}
		}

		$sql = '';
		if(($pos = strpos($table, ' ')) !== false) // SQL
		{
			$sql = $this->__prepSql(substr($table, $pos, strlen($table)));
			$table = substr($table, 0, $pos);
		}

		return $this->__getConn()->query('UPDATE ' . $table . ' SET ' . implode(', ', $values) .
			$sql, $p, Connection::QUERY_RETURN_TYPE_AFFECTED);
	}

	/**
	 * Single column value getter
	 *
	 * @param string $query
	 * @param mixed $params
	 * @return mixed
	 * @throws \Exception (LIMIT clause exists in query)
	 */
	public function value($query, $params = null)
	{
		$query = $this->__prepSql($query);

		if($this->__getConn()->hasSqlLimitClause($query))
		{
			throw new \Exception('Failed to get row value, LIMIT clause already exists in query'
				. ' (' . __METHOD__ . ')');
		}

		$query .= ' LIMIT 1';

		$r = $this->__getConn()->query($query, $this->__prepParams(1, func_get_args()),
			Connection::QUERY_RETURN_TYPE_ROWS);

		return isset($r[0]) ? current($r[0]) : null;
	}
}