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

use Eco\Factory\Database;

/**
 * Model
 *
 * @author Shay Anderson
 */
abstract class Model
{
	/**
	 * Connection ID
	 */
	const CONNECTION_ID = null;

	/**
	 * Model name
	 */
	const NAME = null;

	/**
	 * Model primary key name
	 */
	const PK = null;

	/**
	 * Connection ID
	 *
	 * @var mixed
	 */
	private $__conn_id;

	/**
	 * Connection ID is default flag
	 *
	 * @var boolean
	 */
	private $__is_default_conn_id;

	/**
	 * PK column name
	 *
	 * @var string
	 */
	private $__pk;

	/**
	 * Init
	 *
	 * @throws \Exception (no model name)
	 */
	final public function __construct()
	{
		if(!static::NAME)
		{
			throw new \Exception('Model name must be set using class constant \'NAME\' ('
				. __METHOD__ . ')');
		}

		$d_id = Database::getInstance()->getDefaultConnectionId();
		$this->__conn_id = static::CONNECTION_ID ? static::CONNECTION_ID : $d_id;
		$this->__pk = static::PK ? static::PK : 'id';
		$this->__is_default_conn_id = $this->__conn_id == $d_id;
	}

	/**
	 * Database getter
	 *
	 * @return \Eco\Factory\Database
	 */
	private function __db()
	{
		return System::db( $this->__is_default_conn_id ? null : $this->__conn_id );
	}

	/**
	 * Delete
	 *
	 * @param string $sql
	 * @param mixed $params
	 * @return int (affected)
	 */
	final protected function _delete($sql = null, $params = null)
	{
		return call_user_func_array([$this->__db(), 'delete'],
			['n' => static::NAME] + func_get_args());
	}

	/**
	 * Insert ID getter
	 *
	 * @return mixed (int|string)
	 */
	final protected function _id()
	{
		return $this->__db()->id();
	}

	/**
	 * Create
	 *
	 * @param mixed $data
	 * @param boolean $ignore
	 * @return int (affected)
	 */
	final protected function _insert($data, $ignore = false)
	{
		return $this->__db()->insert(static::NAME, $data, $ignore);
	}

	/**
	 * Replace
	 *
	 * @param mixed $data
	 * @return int (affected)
	 */
	final protected function _replace($data)
	{
		return $this->__db()->replace(static::NAME, $data);
	}

	/**
	 * Truncate table
	 *
	 * @return boolean (false on error)
	 */
	final protected function _truncate()
	{
		return $this->__db()->truncate(static::NAME);
	}

	/**
	 * Update
	 *
	 * @param string $sql
	 * @param array $params
	 * @return int (affected)
	 */
	final protected function _update($sql, array $params)
	{
		return $this->__db()->update(static::NAME, $sql, $params);
	}

	/**
	 * Count getter
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->__db()->count(static::NAME);
	}

	/**
	 * Delete by PK value
	 *
	 * @param int $id
	 * @return int (affected)
	 */
	public function delete($id)
	{
		return $this->__db()->delete(static::NAME, 'WHERE ' . $this->__pk . ' = ?', (int)$id);
	}

	/**
	 * Single record getter by PK value
	 *
	 * @param int $id
	 * @return \stdClass (or null for no record)
	 */
	public function get($id)
	{
		return $this->__db()->get(static::NAME . ' WHERE ' . $this->__pk . ' = ?', (int)$id);
	}

	/**
	 * All records getter
	 *
	 * @return array
	 */
	public function getAll()
	{
		return $this->__db()->getAll(static::NAME);
	}

	/**
	 * Record exists flag getter
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function has($id)
	{
		return $this->__db()->has(static::NAME, 'WHERE ' . $this->__pk . ' = ?', (int)$id);
	}

	/**
	 * Name getter
	 *
	 * @return string
	 */
	final public function name()
	{
		return static::NAME;
	}

	/**
	 * Single column value getter
	 *
	 * @param int $id
	 * @param string $column
	 * @return mixed
	 */
	public function value($id, $column)
	{
		return $this->__db()->value('SELECT ' . $column . ' FROM ' . static::NAME
			. ' WHERE ' . $this->__pk . ' = ?', (int)$id);
	}
}