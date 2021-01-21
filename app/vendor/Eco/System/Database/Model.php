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
use Eco\System\Database;

/**
 * Database model
 *
 * @author Shay Anderson
 */
class Model
{
	/**
	 * Connection ID
	 *
	 * @var mixed
	 */
	private $__conn_id;

	/**
	 * Model name
	 *
	 * @var string
	 */
	private $__name;

	/**
	 * PK column name
	 *
	 * @var string
	 */
	private $__pk;

	/**
	 * Init
	 *
	 * @param string $name
	 * @param string $pk
	 * @param mixed $connection_id
	 */
	public function __construct($name, $pk, $connection_id)
	{
		$this->__name = $name;
		$this->__pk = $pk === null ? 'id' : $pk;

		if($connection_id && $connection_id != Database::getInstance()->getDefaultConnectionId())
		{
			$this->__conn_id = $connection_id;
		}
		else
		{
			$this->__conn_id = Database::getInstance()->getDefaultConnectionId();
		}
	}

	/**
	 * Add WHERE keyword to SQL if does not exist
	 *
	 * @param string $sql
	 * @return string
	 */
	private function __addWhereKeyword($sql)
	{
		$sql = trim($sql);
		if(strcasecmp(substr($sql, 0, 5), 'WHERE') !== 0)
		{
			$sql = 'WHERE ' . $sql;
		}

		return $sql;
	}

	/**
	 * Database getter
	 *
	 * @return \Eco\System\Database
	 */
	private function __db()
	{
		return System::db($this->__conn_id);
	}

	/**
	 * Extract columns from SQL
	 *
	 * @param string $sql
	 * @return array (['cols' => x, 'sql' => y])
	 */
	private function &__getSqlColumns($sql)
	{
		$arr = ['cols' => null, 'sql' => $sql];

		$sql = trim($sql);
		if($sql[0] === '(') // columns
		{
			$arr['cols'] = substr($sql, 1, strpos($sql, ')') - 1);
			$arr['sql'] = substr($sql, strpos($sql, ')') + 1, strlen($sql));
		}

		return $arr;
	}

	/**
	 * Count getter
	 *
	 * @param mixed $sql (Ex: "x = ?" or "WHERE x = ?")
	 * @return int
	 */
	public function count($sql = null)
	{
		if($sql === null)
		{
			return $this->__db()->count($this->__name);
		}

		return call_user_func_array([$this->__db(), 'count'], ['n' => $this->__name . ' '
			. $this->__addWhereKeyword($sql)] + array_slice(func_get_args(), 1));
	}

	/**
	 * Delete by PK value or SQL
	 *
	 * @param mixed $id_or_sql (Ex: "x = ?" or "WHERE x = ?")
	 * @return int (affected)
	 */
	public function delete($id_or_sql)
	{
		if(is_numeric($id_or_sql))
		{
			return $this->__db()->delete($this->__name . ' WHERE ' . $this->__pk . ' = ?',
				(int)$id_or_sql);
		}

		return call_user_func_array([$this->__db(), 'delete'], ['n' => $this->__name . ' '
			. $this->__addWhereKeyword($id_or_sql)] + array_slice(func_get_args(), 1));
	}

	/**
	 * Single row getter
	 *
	 * @param mixed $id_or_sql (Ex: "x = ?" or "WHERE x = ?" or "(col,col2) x = ?"
	 *		or "(col,col2) WHERE x = ?")
	 * @return \stdClass (or null for no row)
	 */
	public function get($id_or_sql)
	{
		if(is_numeric($id_or_sql))
		{
			return $this->__db()->get($this->__name . ' WHERE ' . $this->__pk . ' = ?',
				(int)$id_or_sql);
		}

		$sql = &$this->__getSqlColumns($id_or_sql);

		if($sql['cols']) // use columns
		{
			return call_user_func_array([$this->__db(), 'get'], ['n' => 'SELECT ' . $sql['cols']
				. ' FROM ' . $this->__name . ' ' . $this->__addWhereKeyword($sql['sql'])]
				+ array_slice(func_get_args(), 1));
		}

		return call_user_func_array([$this->__db(), 'get'], ['n' => $this->__name . ' '
			. $this->__addWhereKeyword($id_or_sql)] + array_slice(func_get_args(), 1));
	}

	/**
	 * All rows getter
	 *
	 * @param string $sql (Ex: "ORDER BY x" or "WHERE x = ?" or "(col,col2) WHERE x = ?")
	 * @return array
	 */
	public function getAll($sql = null)
	{
		if($sql === null)
		{
			return $this->__db()->getAll($this->__name);
		}

		$s = &$this->__getSqlColumns($sql);

		if($s['cols']) // use columns
		{
			return call_user_func_array([$this->__db(), 'query'], ['n' => 'SELECT ' . $s['cols']
				. ' FROM ' . $this->__name . ' ' . $s['sql']] + array_slice(func_get_args(), 1));
		}

		return call_user_func_array([$this->__db(), 'query'], ['n' => 'SELECT * FROM '
			. $this->__name . ' ' . $sql] + array_slice(func_get_args(), 1));
	}

	/**
	 * Row exists flag getter
	 *
	 * @param mixed $id_or_sql (Ex: "x = ?" or "WHERE x = ?")
	 * @return boolean
	 */
	public function has($id_or_sql)
	{
		if(is_numeric($id_or_sql))
		{
			return $this->__db()->has($this->__name . ' WHERE ' . $this->__pk . ' = ?',
				(int)$id_or_sql);
		}

		return call_user_func_array([$this->__db(), 'has'], ['n' => $this->__name . ' '
			. $this->__addWhereKeyword($id_or_sql)] + array_slice(func_get_args(), 1));
	}

	/**
	 * Insert ID getter
	 *
	 * @return mixed (int|string)
	 */
	public function id()
	{
		return $this->__db()->id();
	}

	/**
	 * Index (pagination object) getter
	 *
	 * @param string $sql (Ex: "ORDER BY x" or "WHERE x = ?" or "(col,col2) WHERE x = ?")
	 * @return \Eco\System\Database\Pagination
	 */
	public function index($sql = null)
	{
		if($sql === null)
		{
			return $this->__db()->pagination('SELECT * FROM ' . $this->__name);
		}

		$s = &$this->__getSqlColumns($sql);

		if($s['cols']) // use columns
		{
			return call_user_func_array([$this->__db(), 'pagination'], ['n' => 'SELECT '
				. $s['cols'] . ' FROM ' . $this->__name . ' '
				. $s['sql']] + array_slice(func_get_args(), 1));
		}

		return call_user_func_array([$this->__db(), 'pagination'], ['n' => 'SELECT * FROM '
			. $this->__name . ' ' . $sql] + array_slice(func_get_args(), 1));
	}

	/**
	 * Create
	 *
	 * @param mixed $data
	 * @return int (affected)
	 */
	public function insert($data)
	{
		return $this->__db()->insert($this->__name, $data);
	}

	/**
	 * Create and return insert ID
	 *
	 * @param mixed $data
	 * @return mixed (int|string|null)
	 */
	public function insertId($data)
	{
		return $this->__db()->insertId($this->__name, $data);
	}

	/**
	 * Create with ignore
	 *
	 * @param mixed $data
	 * @return int (affected)
	 */
	public function insertIgnore($data)
	{
		return $this->__db()->insertIgnore($this->__name, $data);
	}

	/**
	 * Create with ignore and return insert ID
	 *
	 * @param mixed $data
	 * @return mixed (int|string|null)
	 */
	public function insertIgnoreId($data)
	{
		return $this->__db()->insertIgnoreId($this->__name, $data);
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
		return $this->__db()->query($query, array_slice(func_get_args(), 1));
	}

	/**
	 * Replace
	 *
	 * @param mixed $data
	 * @return int (affected)
	 */
	public function replace($data)
	{
		return $this->__db()->replace($this->__name, $data);
	}

	/**
	 * Truncate table
	 *
	 * @return boolean (false on error)
	 */
	public function truncate()
	{
		return $this->__db()->truncate($this->__name);
	}

	/**
	 * Update
	 *
	 * @param mixed $id_sql_params (Ex: "x = :x" or "WHERE x = :x")
	 * @param mixed $params
	 * @param bool $is_ignore
	 * @return int (affected)
	 */
	public function update($id_sql_params, $params = null, $is_ignore = false)
	{
		if(is_object($params)) // object
		{
			$arr = [];
			foreach(get_object_vars($params) as $k => $v)
			{
				$arr[$k] = $v;
			}
			$params = &$arr;
		}

		if(is_numeric($id_sql_params))
		{
			return $this->__db()->update($this->__name . ' WHERE ' . $this->__pk . ' = :pkid',
				$params + [':pkid' => (int)$id_sql_params], $is_ignore);
		}
		else if(is_array($id_sql_params))
		{
			return $this->__db()->update($this->__name, $id_sql_params, $is_ignore);
		}

		return $this->__db()->update($this->__name . ' ' . $this->__addWhereKeyword($id_sql_params),
			$params, $is_ignore);
	}

	/**
	 * Update with ignore
	 *
	 * @param mixed $id_sql_params (Ex: "x = :x" or "WHERE x = :x")
	 * @param mixed $params
	 * @return int (affected)
	 */
	public function updateIgnore($id_sql_params, $params = null)
	{
		return $this->update($id_sql_params, $params, true);
	}

	/**
	 * Single column value getter
	 *
	 * @param string $column_and_sql (Ex: "col x = ?" or "col WHERE x = ?")
	 * @return mixed
	 */
	public function value($column_and_sql)
	{
		$col = substr($column_and_sql, 0, strpos($column_and_sql, ' '));
		$column_and_sql = substr($column_and_sql, strpos($column_and_sql, ' '));

		return call_user_func_array([$this->__db(), 'value'], ['n' => 'SELECT ' . $col . ' FROM '
			. $this->__name . ' ' . $this->__addWhereKeyword($column_and_sql)]
			+ array_slice(func_get_args(), 1));
	}
}