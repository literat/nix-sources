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

use Nix\Forms\Rule,
	Nix\Forms\Controls\FormControl;

/**
 * Condition class
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms
 */
class Condition extends Rule
{
	/** @var array of rules */
	public $rules = array();

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

		return $valid;
	}

	/**
	 * Add rule for actual control (control of condition)
	 * 
	 * @param string $rule validation rule name or callback
	 * @param mixed $arg validation argument
	 * @param string $message error message
	 * @return Condition
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		return $this->addRuleOn($this->control, $rule, $arg, $message);
	}

	/**
	 * Add rule for $control
	 * 
	 * @param FormControl $control
	 * @param string $rule validation rule name or callback
	 * @param mixed $arg validation argument
	 * @param string $message error message
	 * @return Condition
	 */
	public function addRuleOn(FormControl $control, $rule, $arg = null, $message = null)
	{
		$this->rules[] = new Rule($control, $rule, $arg, $message);

		return $this;
	}
}