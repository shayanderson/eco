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
	public $db;

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
	 * Name getter
	 *
	 * @return string
	 */
	final public function name()
	{
		return static::NAME;
	}
}