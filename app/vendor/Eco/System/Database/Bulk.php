<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2020 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Database;

/**
 * Database bulk methods
 *
 * @author Shay Anderson
 */
class Bulk
{
	/**
	 * Database connection
	 *
	 * @var \Eco\System\Database\Connection
	 */
	private $connection;

	/**
	 * Init
	 *
	 * @param \Eco\System\Database\Connection $connection
	 */
	public function __construct(\Eco\System\Database\Connection $connection)
	{
		$this->connection = &$connection;
	}

	/**
	 * Insert
	 *
	 * @param string $table
	 * @return \Eco\System\Database\Bulk\Type\Insert
	 */
	public function insert($table)
	{
		return new Bulk\Type\Insert($this->connection, $table);
	}

	/**
	 * Insert ignore
	 *
	 * @param string $table
	 * @return \Eco\System\Database\Bulk\Type\Insert
	 */
	public function insertIgnore($table)
	{
		return new Bulk\Type\Insert($this->connection, $table, true);
	}

	/**
	 * Replace
	 *
	 * @param string $table
	 * @return \Eco\System\Database\Bulk\Type\Insert
	 */
	public function replace($table)
	{
		return new Bulk\Type\Insert($this->connection, $table, false, true);
	}
}