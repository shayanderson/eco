<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Database\Bulk;

/**
 * Database bulk type
 *
 * @author Shay Anderson
 */
abstract class Type
{
	/**
	 * Database connection
	 *
	 * @var \Eco\System\Database\Connection
	 */
	protected $connection;

	/**
	 * Init
	 *
	 * @param \Eco\System\Database\Connection $connection
	 */
	public function __construct(\Eco\System\Database\Connection &$connection)
	{
		$this->connection = &$connection;
	}

	/**
	 * Execute query
	 */
	abstract public function execute();
}