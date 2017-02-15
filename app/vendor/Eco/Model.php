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
			throw new \Exception('Model name must be set using class constant \'NAME\' ('
				. __METHOD__ . ')');
		}

		$this->db = new DatabaseModel(static::NAME, static::PK, static::CONNECTION_ID);
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
	 * Delete by PK value
	 *
	 * @param int $id
	 * @return inf (affected)
	 */
	final public function deleteRow($id)
	{
		if(is_numeric($id))
		{
			return $this->db->delete($id);
		}
	}

	/**
	 * Single row getter
	 *
	 * @param int $id
	 * @return \stdClass (or null for no row)
	 */
	final public function getRow($id)
	{
		if(is_numeric($id))
		{
			return $this->db->get($id);
		}
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
	 * @return boolean
	 */
	final public function hasRow($id)
	{
		if(is_numeric($id))
		{
			return $this->db->has($id);
		}
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
}