<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Database\Bulk\Type;

use Eco\System;
use Eco\System\Database\Connection;

/**
 * Database bulk insert
 *
 * @author Shay Anderson
 */
class Insert extends \Eco\System\Database\Bulk\Type
{
	/**
	 * Bind values
	 *
	 * @var array
	 */
	private $bind_values = [];

	/**
	 * Table columns
	 *
	 * @var array
	 */
	private $columns;

	/**
	 * Data index
	 *
	 * @var int
	 */
	private $index = 0;

	/**
	 * Use ignore keyword
	 *
	 * @var bool
	 */
	private $is_ignore;

	/**
	 * Replace instead of insert
	 *
	 * @var bool
	 */
	private $is_replace;

	/**
	 * SQL strings
	 *
	 * @var array
	 */
	private $sql = [];

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table;

	/**
	 * Init
	 *
	 * @param \Eco\System\Database\Connection $connection
	 * @param string $table
	 * @param bool $is_ignore
	 * @param bool $is_replace
	 */
	public function __construct(\Eco\System\Database\Connection &$connection, $table,
		$is_ignore = false, $is_replace = false)
	{
		parent::__construct($connection);
		$this->table = $table;
		$this->is_ignore = $is_ignore;
		$this->is_replace = $is_replace;
	}

	/**
	 * Add data to bulk insert
	 *
	 * @param array|object $data
	 * @return void
	 */
	public function add($data)
	{
		if(is_object($data)) // object
		{
			$arr = [];
			foreach(get_object_vars($data) as $k => $v)
			{
				$arr[$k] = $v;
			}
			$data = &$arr;
		}

		$cols = array_keys($data);
		if(!$this->columns) // init columns
		{
			$this->columns = $cols;
		}

		if(count($this->columns) != count($data))
		{
			System::error(__METHOD__ . ': column count (' . count($this->columns)
				. ') does not match data count (' . count($data) . ')', null, 'Eco');
		}

		foreach($this->columns as $col) // ensure required columns exist
		{
			if(!in_array($col, $cols))
			{
				System::error(__METHOD__ . ': missing column in data \'' . $col . '\'', null, 'Eco');
			}
		}

		$params = [];
		foreach($data as $k => $v)
		{
			$k = str_replace('`', '', $k); // strip '`' from column names like `col`

			if(is_array($v)) // SQL
			{
				if(isset($v[0]) && strlen($v[0]))
				{
					$params[] = $v[0];
				}
			}
			else // named param
			{
				$params[] = ':' . $k . $this->index;
				$this->bind_values[$k . $this->index] = $v;
			}
		}

		$this->sql[] = '(' . implode(', ', $params) . ')';

		$this->index++;
	}

	/**
	 * Add array of data to bulk insert
	 *
	 * @param array $data
	 * @return void
	 */
	public function addGroup(array $data)
	{
		foreach($data as $v)
		{
			$this->add($v);
		}
	}

	/**
	 * Execute bulk insert query
	 *
	 * @return int (affected)
	 */
	public function execute()
	{
		if(!$this->bind_values)
		{
			System::error(__METHOD__ . ': no data has been added', null, 'Eco');
		}

		return $this->connection->query(( $this->is_replace ? 'REPLACE' : 'INSERT' )
			. ( $this->is_ignore ? ' IGNORE' : null ) . ' INTO ' . $this->table
			. '(' . implode(',', $this->columns) . ') VALUES ' . implode(',', $this->sql),
			$this->bind_values, Connection::QUERY_RETURN_TYPE_AFFECTED);
	}
}