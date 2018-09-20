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

/**
 * Benchmark
 *
 * @author Shay Anderson
 */
class Benchmark
{
	/**
	 * Points
	 *
	 * @var array
	 */
	protected static $point = [];

	/**
	 * Start memory usage
	 *
	 * @var string
	 */
	protected static $start_mem;

	/**
	 * Start time
	 *
	 * @var float
	 */
	protected static $start_time;

	/**
	 * Stop memory usage
	 *
	 * @var string
	 */
	protected static $stop_mem;

	/**
	 * Stop peak memory usage
	 *
	 * @var string
	 */
	protected static $stop_mem_peak;

	/**
	 * Stop time
	 *
	 * @var float
	 */
	protected static $stop_time;

	/**
	 * Format memory value
	 *
	 * @staticvar array $s
	 * @param int $value
	 * @return string
	 */
	protected static function formatMemory($value)
	{
		static $s = ['b', 'kb', 'mb', 'gb', 'tb'];
		$value = (float)$value;
		return $value ? round($value / pow(1024, ( $k = floor(log($value, 1024)) )), 2) . ' '
			. $s[$k] : 0;
	}

	/**
	 * Elapsed time getter (seconds)
	 *
	 * @return float (null on no start)
	 */
	public static function getElapsed()
	{
		if(!self::$start_time)
		{
			return null;
		}

		$stop = microtime(true);
		$elapsed = null;

		if(self::$stop_time)
		{
			$stop = self::$stop_time;
		}

		return $stop - self::$start_time;
	}

	/**
	 * Final memory usage getter
	 *
	 * @return array [start, stop, stop_peak]
	 */
	public static function getMemoryUsage()
	{
		return [
			'start' => self::formatMemory(self::$start_mem),
			'stop' => self::formatMemory(self::$stop_mem),
			'stop_peak' => self::formatMemory(self::$stop_mem_peak)
		];
	}

	/**
	 * Single point getter
	 *
	 * @param type $id
	 * @return array (null on invalid index)
	 */
	public static function getPoint($id)
	{
		foreach(self::$point as $v)
		{
			if($id == $v['id'])
			{
				return $v;
			}
		}

		return null;
	}

	/**
	 * Points getter
	 *
	 * @param bool $self_methods (include self::start() + self::stop() methods)
	 * @return array (empty on no stop)
	 * @throws \Exception (no start and/or stop points exist)
	 */
	public static function getPoints($self_methods = false)
	{
		if(!self::$start_time)
		{
			throw new \Exception(__METHOD__ . ': start() methods were never called');
		}

		if(!self::$stop_time)
		{
			self::stop(); // auto stop
		}

		if(!$self_methods)
		{
			return self::$point;
		}

		return [-1 => [
			'id' => __CLASS__ . '::start()',
			'start' => self::$start_time,
			'memory_usage' => self::formatMemory(self::$start_mem),
			'memory_diff' => 0,
			'elapsed' => 0,
			'elapsed_point' => 0
		]] + self::$point + [count(self::$point) => [
			'id' => __CLASS__ . '::stop()',
			'start' => self::$stop_time,
			'memory_usage' => self::formatMemory(self::$stop_mem),
			'memory_diff' => '+' . self::formatMemory(self::$stop_mem - self::$start_mem),
			'elapsed' => self::getElapsed(),
			'elapsed_point' => !count(self::$point) ? null
				: ( isset(self::$point[count(self::$point) - 1])
					? self::$stop_time - self::$point[count(self::$point) - 1]['start'] : null )
		]];
	}

	/**
	 * Check if a point exists
	 *
	 * @param string $id
	 * @return boolean
	 */
	public static function hasPoint($id)
	{
		foreach(self::$point as $v)
		{
			if($id == $v['id'])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Point setter
	 *
	 * @param string $id
	 * @param mixed $data (optional)
	 * @return void
	 * @throws \Exception (no start point or point already exists)
	 */
	public static function point($id, $data = null)
	{
		if(!self::$start_time)
		{
			throw new \Exception(__METHOD__ . ': ' . __CLASS__ . '::start() was never called');
		}

		if(self::hasPoint($id))
		{
			throw new \Exception(__METHOD__ . ': point ID \'' . $id . '\' already exists');
		}

		$p = [
			'id' => $id,
			'start' => microtime(true),
			'memory_usage' => memory_get_usage()
		];

		$p['memory_diff'] = '+' . self::formatMemory($p['memory_usage'] - self::$start_mem);
		$p['memory_usage'] = self::formatMemory($p['memory_usage']);
		$p['elapsed'] = $p['start'] - self::$start_time;
		$p['elapsed_point'] = 0;
		if(($c = count(self::$point)) && isset(self::$point[$c - 1]))
		{
			$p['elapsed_point'] = $p['start'] - self::$point[$c - 1]['start'];
		}

		if($data !== null)
		{
			$p['data'] = $data;
		}

		self::$point[] = $p;
	}

	/**
	 * Start point
	 *
	 * @return void
	 */
	public static function start()
	{
		self::reset();
		self::$start_time = microtime(true);
		self::$start_mem = memory_get_usage();
	}

	/**
	 * Stop point
	 *
	 * @return void
	 * @throws \Exception (stop already called)
	 */
	public static function stop()
	{
		if(self::$stop_time)
		{
			throw new \Exception(__METHOD__ . ': ' . __CLASS__ . '::stop() was already called,'
				. ' use the reset() method');
		}

		self::$stop_time = microtime(true);
		self::$stop_mem = memory_get_usage();
		self::$stop_mem_peak = memory_get_peak_usage();
	}

	/**
	 * Reset all points
	 *
	 * @return void
	 */
	public static function reset()
	{
		self::$point = [];
		self::$start_mem = null;
		self::$start_time = null;
		self::$stop_mem = null;
		self::$stop_mem_peak = null;
		self::$stop_time = null;
	}
}