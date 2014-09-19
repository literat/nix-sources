<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Forms\JsValidators;

use Nix,
	Nix\Forms\Rule,
	Nix\Forms\Condition,
	Nix\Forms\JsValidators\IJsValidator;

/**
 * jQuery javascript validator
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\JsValidators
 */
class JqueryJsValidator extends Nix\Object implements IJsValidator
{
	/** @var string - Form name */
	protected $name;

	/** @var array */
	protected $rules;

	/** @var array */
	protected $conditions;

	/**
	 * Adds rule
	 * 
	 * @param   Rule $rule
	 * @return  JqueryFormJsValidator
	 */
	public function addRule(Rule $rule)
	{
		$this->rules[] = $this->getRule($rule, true);
		
		return $this;
	}

	/**
	 * Adds condition
	 * 
	 * @param   Condition $condition
	 * @return  JqueryFormJsValidator
	 */
	public function addCondition(Condition $condition)
	{
		$c = $this->getRule($condition);
		foreach($condition->rules as $rule) {
			$c['rules'][] = $this->getRule($rule, true);
		}

		$this->conditions[] = $c;

		return $this;
	}

	/**
	 * Returns raw js code
	 * 
	 * @return  string
	 */
	public function getCode()
	{
		if(empty($this->rules) && empty($this->conditions)) {
			return '';
		}

		$name  = str_replace('-', '_', $this->name);
		$code  = "var {$name}Rules = " . json_encode($this->rules) . ";\n";
		$code .= "var {$name}Conditions = " . json_encode($this->conditions) . ";\n";
		$code .= "$('#{$this->name}').validate({$name}Rules, {$name}Conditions);\n";

		$this->rules = $this->conditions = array();
		
		return "<script type=\"text/javascript\">\n/* <![CDATA[ */\n$(document).ready(function(){\n" . $code . "});\n/* ]]> */\n</script>";
	}

	/**
	 * Transforms rule to array
	 * 
	 * @param   Rule  $rule
	 * @param   bool  add message?
	 * @return  array
	 */
	protected function getRule(Rule $rule, $withMessage = false)
	{
		if(empty($this->name)) {
			$this->name = $rule->control->form->name;
		}

		$r = array();
		$r['control'] = $rule->control->getName();
		$r['rule'] = $rule->rule;

		if($rule->negative) {
			$r['negative'] = $rule->negative;
		}

		$empty = $rule->control->getEmptyValue();
		if(!empty($empty)) {
			$r['empty'] = $empty;
		}

		if(!empty($rule->arg)) {
			$r['arg'] = ($rule->arg instanceof FormControl) ? array('control' => $rule->arg->getName()) : $rule->arg;
			if($r['rule'] == 'callback') {
				unset($r['arg']['callback']);
			}
		}

		if($withMessage) {
			$r['message'] = $rule->getMessage();
		}

		return $r;
	}
}