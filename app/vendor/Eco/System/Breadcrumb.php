<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2018 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco\System;

/**
 * View breadcrumb helper
 *
 * @author Shay Anderson
 */
class Breadcrumb extends \Eco\Factory
{
	/**
	 * Item keys
	 */
	const KEY_TITLE = 0;
	const KEY_URL = 1;

	/**
	 * Base items
	 *
	 * @var array
	 */
	private $__base = [];

	/**
	 * Items
	 *
	 * @var array
	 */
	private $__items = [];

	/**
	 * Callable filter for all titles
	 *
	 * @var callable
	 */
	public $filter_title;

	/**
	 * Callable filter for all URLs
	 *
	 * @var callable
	 */
	public $filter_url;

	/**
	 * Breadcrumb item template
	 *
	 * @var string (ex: '<a href="{$url}">{$title}</a>')
	 */
	public $template = '<a href="{$url}">{$title}</a>';

	/**
	 * Active breadcrumb item template
	 *
	 * @var string (ex: '{$title}')
	 */
	public $template_active = '{$title}';

	/**
	 * Breadcrumb item separator
	 *
	 * @var string
	 */
	public $separator = ' &raquo; ';

	/**
	 * Breadcrumb wrapper after items
	 *
	 * @var string (ex: '</div>')
	 */
	public $wrapper_after;

	/**
	 * Breadcrumb wrapper before items
	 *
	 * @var string (ex: '<div class="breadcrumb'>')
	 */
	public $wrapper_before;

	/**
	 * Init
	 *
	 * @param mixed $breadcrumbs (optional, array ex: ['/url.htm' => 'Title', 'Current Page'])
	 */
	public function __construct($items = null)
	{
		if(is_array($items))
		{
			$this->add($items);
		}
	}

	/**
	 * Breadcrumb string getter
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}

	/**
	 * Add breadcrumb item
	 *
	 * @param mixed $title (string when setter or array for multiple add
	 *		ex: ['/url.htm' => 'Title', 'Current Page'])
	 * @param mixed $url (string when setter, null when no URL (active item))
	 * @return void
	 */
	public function add($title, $url = null)
	{
		if(is_array($title)) // add array of items
		{
			foreach($title as $k => $v)
			{
				$this->add($v, $k);
			}
			return;
		}

		if($url === null || is_int($url)) // auto active
		{
			$this->__items[] = [self::KEY_TITLE => $title];
		}
		else
		{
			$this->__items[] = [self::KEY_TITLE => $title, self::KEY_URL => $url];
		}
	}

	/**
	 * Base item(s) setter
	 *
	 * @param mixed $title (string when setter or array for multiple add
	 *		ex: ['/url.htm' => 'Title', 'Current Page'])
	 * @param string $url
	 */
	public function base($title, $url)
	{
		if(is_array($title))
		{
			$this->__base = $title;
		}
		else
		{
			$this->__base[] = [self::KEY_TITLE => $title, self::KEY_URL => $url];
		}
	}

	/**
	 * Breadcrumb string getter
	 *
	 * @param boolean $use_wrapper (use before/after wrapper in string)
	 * @return string
	 */
	public function get($use_wrapper = true)
	{
		$str = '';

		if(count($this->__items) > 0)
		{
			$i = 0;
			foreach(array_merge($this->__base, $this->__items) as $v)
			{
				if($i > 0)
				{
					$str .= $this->separator;
				}

				if(is_callable($this->filter_title))
				{
					$v[self::KEY_TITLE] = call_user_func($this->filter_title, $v[self::KEY_TITLE]);
				}

				if(isset($v[self::KEY_URL])) // non-active item
				{
					if(is_callable($this->filter_url))
					{
						$v[self::KEY_URL] = call_user_func($this->filter_url, $v[self::KEY_URL]);
					}

					$str .= str_replace('{$url}', $v[self::KEY_URL], str_replace('{$title}',
						$v[self::KEY_TITLE], $this->template));
				}
				else // active item
				{
					$str .= str_replace('{$title}', $v[self::KEY_TITLE], $this->template_active);
				}

				$i++;
			}

			return $use_wrapper ? $this->wrapper_before . $str . $this->wrapper_after : $str;
		}

		return '';
	}

	/**
	 * Item getter
	 *
	 * @param int $index (starts at zero)
	 * @return mixed (array [0 => title, (optional)1 => uri], null on does not exist)
	 */
	public function getItem($index)
	{
		if(isset($this->__items[$index]))
		{
			return $this->__items[$index];
		}

		return null;
	}

	/**
	 * Items getter
	 *
	 * @return array
	 */
	public function &getItems()
	{
		return $this->__items;
	}
}