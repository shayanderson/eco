<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2018 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

use Eco\System\Database\Model as DatabaseModel;

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
	 * Model object
	 *
	 * @var \Eco\System\Database\Model
	 */
	protected $db;

	/**
	 * Init
	 *
	 * @throws \Exception (no model name)
	 */
	final public function __construct()
	{
		if(!static::NAME)
		{
			throw new \Exception(__METHOD__ . ': model name must be set using class constant'
				. ' \'NAME\'');
		}

		$this->db = new DatabaseModel(static::NAME, static::PK, static::CONNECTION_ID);

		if(method_exists($this, '__init')) // child construct method
		{
			$this->__init();
		}
	}

	/**
	 * Count getter
	 *
	 * @return int
	 */
	final public function countRows()
	{
		return $this->db->count();
	}

	/**
	 * Single row getter
	 *
	 * @param int $id
	 * @return \stdClass (or null for no row or invalid $id)
	 */
	final public function getRow($id)
	{
		if(is_numeric($id))
		{
			return $this->db->get($id);
		}

		return null;
	}

	/**
	 * All rows getter
	 *
	 * @return type
	 */
	final public function getRows()
	{
		return $this->db->getAll();
	}

	/**
	 * Row exists flag getter
	 *
	 * @param mixed $id
	 * @return boolean (or null for invalid $id)
	 */
	final public function hasRow($id)
	{
		if(is_numeric($id))
		{
			return $this->db->has($id);
		}

		return null;
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
	 * Name with database name getter
	 *
	 * @return string
	 */
	final public function nameAndDatabase()
	{
		return $this->db->getDatabaseName() . '.' . static::NAME;
	}
}