<?php
/**
 * Eco is a PHP Framework for PHP 5.5+
 *
 * @package Eco
 * @copyright 2015-2021 Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/shayanderson/eco/blob/master/LICENSE>
 * @link <https://github.com/shayanderson/eco>
 */
namespace Eco;

use Eco\System\Validate;

/**
 * HTML form
 *
 * @author Shay Anderson
 */
class Form
{
	/**
	 * Session key for token
	 */
	const SESSION_KEY_TOKEN = '__ECO__.tok';

	/**
	 * Form field types
	 */
	const
		TYPE_CHECKBOX = 'checkbox',
		TYPE_EMAIL = 'email',
		TYPE_HIDDEN = 'hidden',
		TYPE_PASSWORD = 'password',
		TYPE_RADIO = 'radio',
		TYPE_SELECT = 1,
		TYPE_TEXT = 'text',
		TYPE_TEXTAREA = 2;

	/**
	 * Validation types
	 */
	const
		VALIDATE_EMAIL = 1,
		VALIDATE_LENGTH = 2,
		VALIDATE_MATCH = 3,
		VALIDATE_REGEX = 4,
		VALIDATE_REQUIRED = 5;

	/**
	 * Get|post data
	 *
	 * @var array
	 */
	private $__data;

	/**
	 * Form fields
	 *
	 * @var array
	 */
	private $__fields = [];

	/**
	 * Form ID
	 *
	 * @var string
	 */
	private $__form_id;

	/**
	 * Form IDs
	 *
	 * @var array
	 */
	private static $__form_ids = [];

	/**
	 * Active field ID
	 *
	 * @var string
	 */
	private $__id;

	/**
	 * Token fail callback used status
	 *
	 * @var bool
	 */
	private $__token_fail_callback_used = false;

	/**
	 * Global default attributes
	 *
	 * @var array (ex: ['class' => 'form-control'])
	 */
	public static
		$attributes_checkbox_radio, // checkbox + radio fields
		$attributes_field, // email, password, text fields
		$attributes_fields, // all fields
		$attributes_select,
		$attributes_textarea;

	/**
	 * Global decorators
	 *
	 * @var string (ex: '<div>{$field}</div>')
	 */
	public static
		$decorator_checkbox_radio, // checkbox + radio fields
		$decorator_default_validation_message = 'Enter valid value for field \'{$field}\'',
		$decorator_error,
		$decorator_errors,
		$decorator_errors_message, // individual messages inside of the decorator_errors decorator
		$decorator_field, // email, password, text fields
		$decorator_fields, // all fields
		$decorator_options, // all checkbox/radio options
		$decorator_select,
		$decorator_textarea;

	/**
	 * Callable filter function for default values (auto set by constructor if null)
	 *
	 * @var callable
	 */
	public static $default_value_filter;

	/**
	 * Callable used when token fail triggered, ex: callback(\Eco\Form $form)
	 *
	 * @var callable
	 */
	public static $token_fail_callback;

	/**
	 * Init
	 *
	 * @param array $data ($_GET|$_POST array)
	 * @param string $form_id (optional, when using form listener for multiple forms in scope)
	 */
	public function __construct(array &$data, $form_id = null)
	{
		$this->__data = &$data;

		if(self::$default_value_filter === null)
		{
			self::$default_value_filter =
				function($v) { return html_entity_decode($v, ENT_QUOTES); };
		}

		if($form_id !== null && strlen($form_id) > 0)
		{
			if(in_array($form_id, self::$__form_ids))
			{
				System::error(__METHOD__ . ': form ID \'' . $form_id
					. '\' already exists (must be unique)', null, 'Eco');
			}

			self::$__form_ids[] = $form_id;
			$this->__form_id = $form_id;
		}
	}

	/**
	 * Form field data getter (::getData() alias)
	 *
	 * @param string $name
	 * @return mixed (false on field does not exist in data)
	 */
	public function __get($name)
	{
		if($this->hasData($name))
		{
			return $this->getData($name);
		}

		return false;
	}

	/**
	 * Alias for getFormIdField() method
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getFormIdField();
	}

	/**
	 * Add form field
	 *
	 * @param mixed $type (int|string)
	 * @param string $id
	 * @param mixed $default_value
	 * @param mixed $options
	 * @return void
	 */
	private function __addField($type, $id, $default_value = null, $options = null)
	{
		if($this->isField($id))
		{
			System::error(__METHOD__ . ': field ID \'' . $id
				. '\' already exists in form (field ID must be unique)', null, 'Eco');
		}

		$this->__fields[$id] = ['type' => $type];

		if($this->isSubmitted() && $this->hasData($id)) // add data as value
		{
			if($type !== self::TYPE_PASSWORD)
			{
				$this->__fields[$id]['value'] = $this->__data[$id];
			}
		}
		else if($default_value !== null && is_scalar($default_value)
			|| $type === self::TYPE_CHECKBOX && is_array($default_value))
		{
			if(self::$default_value_filter !== null) // apply default value filter
			{
				$default_value = call_user_func(self::$default_value_filter, $default_value);
			}

			$this->__fields[$id]['value'] = $default_value;
		}

		if($options !== null && is_array($options))
		{
			$this->__fields[$id]['options'] = $options;
		}

		$this->__id = $id; // set active ID
	}

	/**
	 * Add field validation rule
	 *
	 * @staticvar int $callable_id
	 * @param mixed $rule (callable|int)
	 * @param string $error_message
	 * @param mixed $param
	 * @return void
	 */
	private function __addRule($rule, $error_message, $param = null)
	{
		static $callable_id = 0;

		$error_message = $error_message ?: self::__decorate($this->__id,
			self::$decorator_default_validation_message);

		if($this->isField($this->__id))
		{
			if(is_callable($rule))
			{
				$id = ++$callable_id;
				$this->__fields[$this->__id]['rule'][$id]['callable'] = $rule;
			}
			else
			{
				$this->__fields[$this->__id]['rule'][$rule] = []; // create
			}

			if(!empty($error_message))
			{
				$this->__fields[$this->__id]['rule'][isset($id) ? $id
					: $rule]['message'] = $error_message;
			}

			if(!isset($id) && $param !== null)
			{
				$this->__fields[$this->__id]['rule'][$rule]['param'] = $param;
			}
		}
	}

	/**
	 * Add field validation rule message
	 *
	 * @param int $rule
	 * @param string $error_message
	 * @return void
	 */
	private function __addRuleMessage($rule, $error_message)
	{
		if($this->isField($this->__id) && isset($this->__fields[$this->__id]['rule'][$rule]))
		{
			$this->__fields[$this->__id]['rule'][$rule]['message'] = $error_message;
		}
		else
		{
			System::error(__METHOD__ . ': adding validation message \'' . $error_message
				. '\' for rule that does not exist', null, 'Eco');
		}
	}

	/**
	 * Array of attributes to string
	 *
	 * @param array $attributes (or null for no attributes, ex: ['style' => 'color:#fff'])
	 * @return string (ex: ' style="color:#fff"')
	 */
	private static function &__attributes($attributes)
	{
		$html = '';

		if($attributes !== null)
		{
			foreach($attributes as $k => $v)
			{
				if(is_int($k)) // attribute with no value
				{
					$html .= ' ' . $v;
				}
				else // attribute + value
				{
					$html .= ' ' . $k . '="' . htmlentities($v) . '"';
				}
			}
		}

		return $html;
	}

	/**
	 * Apply global attributes
	 *
	 * @param array $attributes
	 * @param mixed $field_attribute
	 * @param mixed $fields_attributes
	 * @return void
	 */
	private static function __attributesGlobal(&$attributes, &$field_attribute,
		&$fields_attributes)
	{
		if(is_array($field_attribute))
		{
			$attributes += $field_attribute;
		}
		else if(is_array($fields_attributes))
		{
			$attributes += $fields_attributes;
		}

		$attributes = array_filter($attributes); // clear empty attributes
	}

	/**
	 * Decorator string method
	 *
	 * @param string $str
	 * @param mixed $decorator (string|null when no decorator)
	 * @param boolean $decorate
	 * @return string
	 */
	private static function __decorate($str, $decorator = null, $decorate = true)
	{
		if(!$decorate) // force no decorate
		{
			return $str;
		}

		if($decorator === null) // no decorator
		{
			return $str;
		}

		// pattern like {$something} detected, apply decorator pattern
		if(preg_match('/\{\$[a-z]*\}/i', $decorator))
		{
			preg_replace_callback('/\{\$[a-z]*\}/i', function($m) use(&$str, &$decorator)
			{
				$str = str_replace($m[0], $str, $decorator);
			}, $decorator);
		}
		else // if no decorator pattern like {$anything}, append decorator to str
		{
			$str .= $decorator;
		}

		return $str;
	}

	/**
	 * Form has form ID flag getter
	 *
	 * @return boolean
	 */
	private function __isFormId()
	{
		return $this->__form_id !== null;
	}

	/**
	 * Validate field value against validation rule
	 *
	 * @param mixed $rule (callable|int)
	 * @param mixed $value
	 * @param array $field
	 * @param array $rule_arr
	 * @param boolean $is_valid
	 * @return void
	 */
	private static function __validate($rule, $value, array &$field, array &$rule_arr, &$is_valid)
	{
		$valid = true;

		if(isset($rule_arr['callable']))
		{
			$f = $rule_arr['callable'];
			$valid = (bool)$f($value);
		}
		else
		{
			if($rule === 0) // force rule
			{
				$valid = false;
			}
			else
			{
				$validate = Validate::getInstance();

				switch($rule)
				{
					case self::VALIDATE_EMAIL:
						$valid = $validate->email($value);
						break;

					case self::VALIDATE_LENGTH:
						$valid = $validate->length($value,
							isset($rule_arr['param']['min'])
							? $rule_arr['param']['min'] : 0, isset($rule_arr['param']['max'])
							? $rule_arr['param']['max'] : 0);
						break;

					case self::VALIDATE_REGEX:
						$valid = $validate->regexPattern(isset($rule_arr['param'][0])
							? $rule_arr['param'][0] : null) && preg_match(
								isset($rule_arr['param'][0]) ? $rule_arr['param'][0] : null,
							$value) === 1;
						break;

					case self::VALIDATE_REQUIRED:
						$valid = $validate->required($value);
						break;
				}
			}
		}

		if(!$valid)
		{
			$is_valid = false;

			if(isset($rule_arr['message']))
			{
				$field['error'][$rule] = $rule_arr['message'];
			}
		}
	}

	/**
	 * Add checkbox field to form
	 *
	 * @param string $id
	 * @param array $options
	 * @param mixed $default_checked (int|string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &checkbox($id, array $options, $default_checked = null)
	{
		$this->__addField(self::TYPE_CHECKBOX, $id, $default_checked, $options);
		return $this;
	}

	/**
	 * Add email field (HTML5) to form
	 *
	 * @param string $id
	 * @param mixed $default_value (string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &email($id, $default_value = null)
	{
		$this->__addField(self::TYPE_EMAIL, $id, $default_value);
		return $this;
	}

	/**
	 * Manually set field as active
	 *
	 * @param string $id
	 * @return \Eco\Form
	 */
	public function field($id)
	{
		if(!$this->isField($id))
		{
			System::error(__METHOD__ . ': field with ID \'' . $id . '\' does not exist', null,
				'Eco');
		}

		$this->__id = $id; // set active ID
		return $this;
	}

	/**
	 * Force field error
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function forceError($error_message)
	{
		$this->__addRule(0, $error_message);
		$this->isValid(); // push forced error to error queue
		return $this;
	}

	/**
	 * Form field HTML string getter
	 *
	 * @param string $id
	 * @param mixed $attributes (array when setting, or null)
	 * @param boolean $use_global_decorators (true applies global decorators)
	 * @param mixed $options_decorator (string when setting, or null)
	 * @return string
	 */
	public function get($id, $attributes = null, $use_global_decorators = true,
		$options_decorator = null)
	{
		$html = '';

		if($this->isField($id))
		{
			if(!is_array($attributes))
			{
				$attributes = [];
			}

			$attributes = ['name' => $id] + $attributes;

			switch($this->__fields[$id]['type'])
			{
				case self::TYPE_CHECKBOX:
				case self::TYPE_RADIO:
					self::__attributesGlobal($attributes, self::$attributes_checkbox_radio,
						self::$attributes_fields);

					foreach($this->__fields[$id]['options'] as $k => $v)
					{
						$opt_attributes = $attributes;
						if(isset($opt_attributes['checked']))
						{
							if(is_array($opt_attributes['checked']))
							{
								if($this->__fields[$id]['type'] === self::TYPE_CHECKBOX
									&& in_array($k, $opt_attributes['checked']))
								{
									$checked = true;
								}
							}
							else if($opt_attributes['checked'] === $k)
							{
								$checked = true;
							}
							unset($opt_attributes['checked']);
						}
						else if(isset($this->__fields[$id]['value']))
						{
							if(is_array($this->__fields[$id]['value']))
							{
								if($this->__fields[$id]['type'] === self::TYPE_CHECKBOX
									&& in_array($k, $this->__fields[$id]['value']))
								{
									$checked = true;
								}
							}
							else if($this->__fields[$id]['value'] == $k)
							{
								$checked = true;
							}
						}

						$html .= self::__decorate('<input type="' . $this->__fields[$id]['type']
							. '"' . self::__attributes($opt_attributes) . ' value="' . $k . '"'
							. ( isset($checked) ? ' checked' : '' ) . '>' . $v,
								$options_decorator ?: ( $use_global_decorators
									? self::$decorator_options : '' ));

						unset($checked);
					}
					$html = self::__decorate($html, self::$decorator_checkbox_radio
						?: self::$decorator_fields, $use_global_decorators);
					break;

				case self::TYPE_EMAIL:
				case self::TYPE_HIDDEN:
				case self::TYPE_PASSWORD:
				case self::TYPE_TEXT:
					self::__attributesGlobal($attributes, self::$attributes_field,
						self::$attributes_fields);

					if(isset($this->__fields[$id]['value'])) // set default value
					{
						$attributes += ['value' => $this->__fields[$id]['value']];
					}

					$html = self::__decorate('<input type="' . $this->__fields[$id]['type'] . '"'
						. self::__attributes($attributes) . '>',
						$this->__fields[$id]['type'] !== self::TYPE_HIDDEN
							? ( self::$decorator_field ?: self::$decorator_fields )
							: null, $use_global_decorators);
					break;

				case self::TYPE_SELECT:
					self::__attributesGlobal($attributes, self::$attributes_select,
						self::$attributes_fields);

					if(isset($attributes['selected']))
					{
						$selected = $attributes['selected'];
						unset($attributes['selected']);
					}
					else if(isset($this->__fields[$id]['value']))
					{
						$selected = $this->__fields[$id]['value'];
					}

					$html = '<select' . self::__attributes($attributes) . '>';

					foreach($this->__fields[$id]['options'] as $k => $v)
					{
						$html .= '<option value="' . $k . '"' . ( isset($selected)
							&& strcmp($selected, $k) === 0 ? ' selected' : '' ) . '>'
								. $v	. '</option>';
					}

					$html = self::__decorate($html . '</select>', self::$decorator_select
						?: self::$decorator_fields, $use_global_decorators);
					break;

				case self::TYPE_TEXTAREA:
					self::__attributesGlobal($attributes, self::$attributes_textarea,
						self::$attributes_fields);

					$html = self::__decorate('<textarea' . self::__attributes($attributes) . '>'
						. ( isset($this->__fields[$id]['value']) ? $this->__fields[$id]['value']
						: '' ) . '</textarea>', self::$decorator_textarea
							?: self::$decorator_fields, $use_global_decorators);
					break;
			}
		}
		else
		{
			System::error(__METHOD__ . ': field \'' . $id . '\' does not exist', null, 'Eco');
		}

		return $html;
	}

	/**
	 * Form data getter
	 *
	 * @param mixed $fields (optional, get single field value: 'field1',
	 *		or specific fields ex: ['field1', 'field3'],
	 *		or mapped fields ex: ['field_name' => 'custom_name', ...])
	 * @param boolean $return_object
	 * @return array (or object)
	 */
	public function getData($fields = null, $return_object = true)
	{
		if(is_array($fields) && count($fields) > 0) // get multiple fields
		{
			$out = [];

			foreach($fields as $k => $v)
			{
				if(is_int($k)) // field
				{
					if($this->hasData($v))
					{
						$out[$v] = $this->__data[$v];
					}
				}
				else // map field
				{
					if($this->hasData($k))
					{
						$out[$v] = $this->__data[$k];
					}
				}

				if($return_object && isset($out[$v]) && is_array($out[$v]))
				{
					$out[$v] = (object)$out[$v];
				}
			}

			if($return_object)
			{
				$out = (object)$out;
			}

			return $out;
		}
		else if($fields === null) // get all
		{
			if(!$return_object)
			{
				return $this->__data;
			}

			$out = $this->__data;

			foreach($out as &$v)
			{
				if(is_array($v))
				{
					$v = (object)$v;
				}
			}

			return (object)$out;
		}
		else if($this->hasData($fields)) // get single value
		{
			return $this->__data[$fields];
		}
	}

	/**
	 * Field first error string getter
	 *
	 * @param string $id
	 * @param mixed $decorator (string when setting, or null)
	 * @return string
	 */
	public function getError($id, $decorator = null)
	{
		$this->isValid(); // set validation errors

		if(isset($this->__fields[$id]['error']))
		{
			return self::__decorate(array_values($this->__fields[$id]['error'])[0],
				$decorator ?: self::$decorator_error);
		}

		return '';
	}

	/**
	 * Form field HTML with field first error string getter
	 *
	 * @param string $id
	 * @param mixed $attributes (array when setting, or null)
	 * @param boolean $use_global_decorators
	 * @param mixed $options_decorator (string when setting, or null)
	 * @return string
	 */
	public function getErrorAndField($id, $attributes = null, $use_global_decorators = true,
		$options_decorator = null)
	{
		return $this->getError($id) . $this->get($id, $attributes, $use_global_decorators,
			$options_decorator);
	}

	/**
	 * Field errors as string (or array) getter
	 *
	 * @param string $id
	 * @param mixed $decorator (string when setting, or null)
	 * @param boolean $return_array
	 * @return mixed (array|string)
	 */
	public function getErrors($id, $decorator = null, $return_array = false)
	{
		$this->isValid(); // set validation errors

		if($id === null) // get all
		{
			if($return_array)
			{
				$ret_arr = [];

				foreach($this->__fields as $v)
				{
					if(isset($v['error']))
					{
						$ret_arr = array_merge($ret_arr, $v['error']);
					}
				}

				return $ret_arr;
			}

			$html = '';

			foreach($this->__fields as $k => $v)
			{
				$html .= $this->getErrors($k, $decorator);
			}

			return $html;
		}

		if(isset($this->__fields[$id]['error']))
		{
			if($return_array)
			{
				return $this->__fields[$id]['error'];
			}

			$html = '';

			foreach($this->__fields[$id]['error'] as $v)
			{
				$html .= self::__decorate($v, $decorator ?: self::$decorator_errors_message);
			}

			return self::__decorate($html, self::$decorator_errors);
		}
	}

	/**
	 * Form field HTML with field errors string getter
	 *
	 * @param string $id
	 * @param mixed $attributes (array when setting, or null)
	 * @param boolean $use_global_decorators
	 * @param mixed $options_decorator (string when setting, or null)
	 * @return string
	 */
	public function getErrorsAndField($id, $attributes = null, $use_global_decorators = true,
		$options_decorator = null)
	{
		return $this->getErrors($id) . $this->get($id, $attributes, $use_global_decorators,
			$options_decorator);
	}

	/**
	 * Form ID field (listener) HTML getter
	 *
	 * @return string
	 */
	public function getFormIdField()
	{
		if($this->__isFormId())
		{
			$h = '<input type="hidden" name="' . $this->__form_id . '">';

			if(System::conf()->_eco->request->form_tokens)
			{
				$token = null;
				if(System::session()->has(self::SESSION_KEY_TOKEN))
				{
					if(System::conf()->_eco->request->form_tokens_single_use) // re-gen
					{
						$token = null;
					}
					else
					{
						$token = System::session()->get(self::SESSION_KEY_TOKEN);
					}
				}

				if(!$token) // gen
				{
					$token = token(32); // gen
					System::session()->set(self::SESSION_KEY_TOKEN, $token);
				}

				$h .= '<input type="hidden" name="__tok__" value="' . $token . '">';
			}

			return $h;
		}

		return '';
	}

	/**
	 * Form ID getter
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->__form_id;
	}

	/**
	 * Field has data flag getter
	 *
	 * @param string $field (or array for multiple fields, ex: ['field1', 'field2', ...])
	 * @return boolean
	 */
	public function hasData($field)
	{
		if(is_array($field)) // multiple fields
		{
			foreach($field as $v)
			{
				if(!$this->hasData($v))
				{
					return false;
				}
			}

			return true;
		}

		return isset($this->__data[$field]);
	}

	/**
	 * Add hidden field to form
	 *
	 * @param string $id
	 * @param mixed $default_value (string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &hidden($id, $default_value = null)
	{
		$this->__addField(self::TYPE_HIDDEN, $id, $default_value);
		return $this;
	}

	/**
	 * Form field exists flag getter
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function isField($id)
	{
		return $id !== null && isset($this->__fields[$id]);
	}

	/**
	 * Form has been submitted flag getter
	 *
	 * @return boolean
	 */
	public function isSubmitted()
	{
		$is_sub = $this->__isFormId() ? $this->hasData($this->__form_id) : !empty($this->__data);

		if($is_sub && System::conf()->_eco->request->form_tokens)
		{
			if(!System::session()->has(self::SESSION_KEY_TOKEN)
				|| !System::validate()->hash(System::session()->get(self::SESSION_KEY_TOKEN),
					@$this->__data['__tok__']))
			{
				$is_sub = false;
				if(self::$token_fail_callback && !$this->__token_fail_callback_used)
				{
					$f = &self::$token_fail_callback;
					$f($this);
					$this->__token_fail_callback_used = true;
				}
			}
		}

		return $is_sub;
	}

	/**
	 * Form fields values are valid
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		if(!$this->isSubmitted()) // no data
		{
			return false;
		}

		$is_valid = true;

		foreach($this->__fields as $k => &$f)
		{
			if(isset($f['rule']))
			{
				foreach($f['rule'] as $r => $v)
				{
					if($r === self::VALIDATE_MATCH) // match field x with y
					{
						$validate = Validate::getInstance();

						// valid match value
						if(!$validate->match($this->getData($k), $this->getData($v['param'][0])))
						{
							$is_valid = false;
							$f['error'][$r] = $v['message'];
						}
					}
					else
					{
						self::__validate($r, $this->hasData($k) ? $this->__data[$k] : null, $f,
							$v, $is_valid);
					}
				}
			}
		}

		return $is_valid;
	}

	/**
	 * Add password field to form
	 *
	 * @param string $id
	 * @return \Eco\Form
	 */
	public function &password($id)
	{
		$this->__addField(self::TYPE_PASSWORD, $id);
		return $this;
	}

	/**
	 * Add radio button field to form
	 *
	 * @param string $id
	 * @param array $options
	 * @param mixed $default_checked (int|string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &radio($id, array $options, $default_checked = null)
	{
		$this->__addField(self::TYPE_RADIO, $id, $default_checked, $options);
		return $this;
	}

	/**
	 * Add select field to form
	 *
	 * @param string $id
	 * @param array $options
	 * @param mixed $default_selected (int|string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &select($id, array $options, $default_selected = null)
	{
		$this->__addField(self::TYPE_SELECT, $id, $default_selected, $options);
		return $this;
	}

	/**
	 * Field value setter
	 *
	 * @param string $id
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($id, $value)
	{
		if($this->isField($id))
		{
			$this->__data[$id] = $value;
		}
	}

	/**
	 * Add text field to form
	 *
	 * @param string $id
	 * @param mixed $default_value (string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &text($id, $default_value = null)
	{
		$this->__addField(self::TYPE_TEXT, $id, $default_value);
		return $this;
	}

	/**
	 * Add textarea field to form
	 *
	 * @param string $id
	 * @param mixed $default_value (string when setting, or null)
	 * @return \Eco\Form
	 */
	public function &textarea($id, $default_value = null)
	{
		$this->__addField(self::TYPE_TEXTAREA, $id, $default_value);
		return $this;
	}

	/**
	 * Add validation rule as callable to field
	 *
	 * @param callable $validation_func
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validate(callable $validation_func, $error_message = '')
	{
		$this->__addRule($validation_func, $error_message);
		return $this;
	}

	/**
	 * Add validate email rule to field
	 *
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validateEmail($error_message = '')
	{
		$this->__addRule(self::VALIDATE_EMAIL, $error_message);
		return $this;
	}

	/**
	 * Add validate email rule message to field
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function &validateEmailMessage($error_message)
	{
		$this->__addRuleMessage(self::VALIDATE_EMAIL, $error_message);
		return $this;
	}

	/**
	 * Add validate length rule to field
	 *
	 * @param int $min
	 * @param int $max
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validateLength($min = 0, $max = 0, $error_message = '')
	{
		$min = (int)$min;
		$max = (int)$max;

		if($min < 1 && $max < 1)
		{
			System::error(__METHOD__ . ': minimum length or maximum length must be'
				. ' greater than zero', null, 'Eco');
		}

		$this->__addRule(self::VALIDATE_LENGTH, $error_message, ['min' => $min, 'max' => $max]);
		return $this;
	}

	/**
	 * Add validate length rule message to field
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function &validateLengthMessage($error_message)
	{
		$this->__addRuleMessage(self::VALIDATE_LENGTH, $error_message);
		return $this;
	}

	/**
	 * Add validate match fields
	 *
	 * @param string $match_field (ex: 'field1')
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validateMatch($match_field, $error_message = '')
	{
		$this->__addRule(self::VALIDATE_MATCH, $error_message, [$match_field]);
		return $this;
	}

	/**
	 * Add validate match fields rule message to field
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function &validateMatchMessage($error_message)
	{
		$this->__addRuleMessage(self::VALIDATE_MATCH, $error_message);
		return $this;
	}

	/**
	 * Add validate regex rule to field
	 *
	 * @param string $regex_pattern
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validateRegex($regex_pattern, $error_message = '')
	{
		$this->__addRule(self::VALIDATE_REGEX, $error_message, [$regex_pattern]);
		return $this;
	}

	/**
	 * Add validate regex rule message to field
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function &validateRegexMessage($error_message)
	{
		$this->__addRuleMessage(self::VALIDATE_REGEX, $error_message);
		return $this;
	}

	/**
	 * Add validate required rule to field
	 *
	 * @param string $error_message (optional)
	 * @return \Eco\Form
	 */
	public function &validateRequired($error_message = '')
	{
		$this->__addRule(self::VALIDATE_REQUIRED, $error_message);
		return $this;
	}

	/**
	 * Add validate required rule message to field
	 *
	 * @param string $error_message
	 * @return \Eco\Form
	 */
	public function &validateRequiredMessage($error_message)
	{
		$this->__addRuleMessage(self::VALIDATE_REQUIRED, $error_message);
		return $this;
	}
}