<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Forms;

use Nix,
	Nix\Forms\Controls\FormControl;

/**
 * Rule class
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms
 */
class Rule extends Nix\Object
{

	/**
	 * Validation rules
	 * @var string
	 */
	const EQUAL = 'equal';
	const FILLED = 'filled';
	const INTEGER = 'integer';
	const FLOAT = 'float';
	const LENGTH = 'length';
	const RANGE = 'range';
	const URL = 'url';
	const EMAIL = 'email';
	const CALLBACK = 'callback';
	const REGEXP = 'regexp';
	/***/

	/** @var array - Default messages */
	public static $messages = array(
		'equal'		=> 'Value must be equal to "%s".',
		'filled'	=> 'Value is required.',
		'integer'	=> 'Value must be integer number.',
		'float'		=> 'Value must be float number.',
		'length'	=> 'Value must have length %d',
		'range'		=> 'Value must be in range %d - %d.',
		'url'		=> 'Value must be valid URL.',
		'email'		=> 'Value must be valid email.',
		'callback'	=> 'Value is not allowed.',
		'regexp'	=> 'Value must passed by regular expression (%s).',
		'!equal'	=> 'Value must not be equal to "%s".',
		'!filled'	=> 'Value must be empty.',
		'!integer'	=> 'Value must not be integer number.',
		'!float'	=> 'Value must not be float number.',
		'!length'	=> 'Value must have another length than %d',
		'!regexp'	=> 'Value must not passed by regular expression (%s).',
	);

	/** @var FormControl */
	public $control;

	/** @var mixed */
	public $arg;

	/** @var string */
	public $message;

	/** @var string */
	public $rule;

	/** @var bool */
	public $negative = false;

	/**
	 * Contructor
	 * 
	 * @param FormControl $control
	 * @param string $rule validating rule
	 * @param mixed $arg argument
	 * @param string $message error message
	 * @return Rule
	 */
	public function __construct($control, $rule, $arg = null, $message = null)
	{
		$negative = false;
		if(ord($rule[0]) > 127) {
			$rule = ~$rule;
			$negative = true;
		} elseif($rule[0] == '!') {
			$rule = substr($rule, 1);
			$negative = true;
		}

		$this->rule = $rule;
		$this->control = $control;
		$this->arg = $arg;
		$this->negative = $negative;
		$this->message = $message;
	}

	/**
	 * Returns true when is the rule valid
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		$valid = $this->validate($this->rule, $this->control->getValue(), $this->arg);
		if($this->negative) {
			$valid = !$valid;
		}

		if($valid) {
			return true;
		}

		$this->control->setError($this->getMessage());
		
		return false;
	}

	/**
	 * Method validate $value by $rule with $arg
	 * 
	 * @param string $rule validation rule
	 * @param mixed $value value for validation
	 * @param mixed $arg argument for validation
	 * @return bool
	 */
	public function validate($rule, $value, $arg = null)
	{
		if($arg instanceof FormControl) {
			$arg = $arg->getValue();
		}

		switch($rule) {
			case Rule::EQUAL:
				return $value == $arg;
			case Rule::FILLED:
				return ($value === '0') ? true : !empty($value);
			case Rule::INTEGER:
				return preg_match('#^\d+$#', $value);
			case Rule::FLOAT:
				return preg_match('#^\d+(\.\d+)?$#', $value);
			case Rule::LENGTH:
				$value = mb_strlen($value, 'UTF-8');
				if($arg[0] == '>') {
					return $value > substr($arg, 1);
				} elseif($arg[0] == '<') {
					return $value < substr($arg, 1);
				} else {
					if(!is_int($arg)) {
						$arg = substr($arg, 1);
					}

					return $value == $arg;
				}
			case Rule::RANGE:
				$value = (int) $value;
				if(is_array($arg) && count($arg) == 2) {
					return ($value >= $arg[0] && $value <= $arg[1]);
				} else {
					return $value == $arg;
				}
			case Rule::EMAIL:
				return preg_match('#^[^@\s]+@[^@\s]+\.[a-z]{2,10}$$#i', $value);
			case Rule::URL:
				return preg_match('#^.+\.[a-z]{2,6}(\\/.*)?$#i', $value);
			case Rule::CALLBACK:
				$cb = isset($arg['callback']) ? $arg['callback'] : $arg;
				if(is_callable($cb)) {
					$valid = call_user_func($cb, $value, @$arg['arg']);
					if(is_array($valid)) {
						$this->message = $valid['message'];
						return $valid['valid'];
					} else {
						return $valid;
					}
				}

				throw new \Exception('Validation callback is not callable.');
			case Rule::REGEXP:
				return preg_match($arg, $value);
			default: throw new \Exception("Unsupported validation rule $rule.");
		}
	}

	/**
	 * Prepares error message
	 * 
	 * @return string
	 */
	public function getMessage()
	{
		if(empty($this->message)) {
			$rule = ($this->negative ? '!' : '') . $this->rule;
			if(isset(self::$messages[$rule])) {
				$this->message = self::$messages[$rule];
			} else {
				$this->message = 'Undefined error message';
			}
		}

		if(is_array($this->arg)) {
			$arg = $this->arg;
			array_unshift($arg, $this->message);
			$this->message = call_user_func_array('sprintf', $arg);
		} elseif(!is_object($this->arg)) {
			$this->message = sprintf($this->message, $this->arg);
		}

		return $this->message;
	}
}
