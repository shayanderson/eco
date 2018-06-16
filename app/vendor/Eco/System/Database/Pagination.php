<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2018 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System\Database;

use Eco\System;

/**
 * Database pagination
 *
 * @author Shay Anderson
 */
class Pagination
{
	/**
	 * Conf settings
	 *
	 * @var \stdClass
	 */
	private $__conf;

	/**
	 * Next page
	 *
	 * @var int
	 */
	private $__next;

	/**
	 * Current page
	 *
	 * @var int
	 */
	private $__page;

	/**
	 * Prev page
	 *
	 * @var int
	 */
	private $__prev;

	/**
	 * Has rows flag
	 *
	 * @var boolean
	 */
	public $has = false;

	/**
	 * Next page number
	 *
	 * @var int
	 */
	public $next;

	/**
	 * Current page number
	 *
	 * @var int
	 */
	public $page;

	/**
	 * Prev page
	 *
	 * @var int
	 */
	public $prev;

	/**
	 * Rows
	 *
	 * @var array
	 */
	public $rows;

	/**
	 * Init
	 *
	 * @param array $rows
	 * @param int $page
	 * @param \stdClass $conf
	 */
	public function __construct(array $rows, $page, \stdClass &$conf)
	{
		$this->__conf = &$conf;

		// set page
		$this->page = $page;

		// records per page
		$this->rpp = $conf->rpp;

		// prep rows + object
		if($rows)
		{
			$this->has = true;

			if(count($rows) > $this->rpp)
			{
				array_pop($rows); // rm last row (more rows)
				$this->__next = $this->page + 1;
			}

			$this->rows = $rows;
			unset($rows);

			if($this->page > 1)
			{
				$this->__prev = $this->page - 1;
			}
		}

		$this->next = $this->__next;
		$this->prev = $this->__prev;

		$this->__page = $this->page;
	}

	/**
	 * URI getter
	 *
	 * @param int $page
	 * @return string
	 */
	private function __getUri($page)
	{
		if($page && $this->__conf->page->encode)
		{
			$page = System::format()->base64UrlEncode($page);
		}

		$url = parse_url(@$_SERVER['REQUEST_URI']);

		if(isset($url['path']))
		{
			if(isset($url['query'])) // query string
			{
				parse_str($url['query'], $qs);

				if(isset($this->__conf->page->get_var)) // rm current page
				{
					unset($qs[$this->__conf->page->get_var]);
				}

				if($page) // append
				{
					$qs[$this->__conf->page->get_var] = $page;
				}

				if(count($qs))
				{
					$url['path'] .= '?' . http_build_query($qs);
				}
			}
			else if($page) // add page
			{
				$url['path'] .= '?' . http_build_query([$this->__conf->page->get_var => $page]);
			}

			return $url['path'];
		}

		return null;
	}

	/**
	 * HTML printer
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}

	/**
	 * HTML getter
	 *
	 * @return string
	 */
	public function get()
	{
		$html = '';

		if($this->__prev && isset($this->__conf->wrapper->prev))
		{
			$html = str_replace('{$uri}', $this->__getUri($this->__prev),
				$this->__conf->wrapper->prev);
		}

		// page range
		if($this->__prev > 1 && isset($this->__conf->page->range_count,
			$this->__conf->wrapper->range, $this->__conf->wrapper->range_active))
		{
			$range_count = (int)$this->__conf->page->range_count;
			if($range_count)
			{
				foreach(array_slice(range(1, $this->__prev), -($range_count-1)) as $v)
				{
					$html .= str_replace('{$uri}', $this->__getUri( $v === 1 ? null : $v ),
						str_replace('{$page}', $v, $this->__conf->wrapper->range));
				}

				// add active page
				$html .= str_replace('{$page}', $this->__page,
					$this->__conf->wrapper->range_active);
			}
		}

		if($this->__next && isset($this->__conf->wrapper->next))
		{
			$html .= str_replace('{$uri}', $this->__getUri($this->__next),
				$this->__conf->wrapper->next);
		}

		if(!$html)
		{
			return '';
		}

		return isset($this->__conf->wrapper->group)
			? str_replace('{$group}', $html, $this->__conf->wrapper->group) : $html;
	}
}