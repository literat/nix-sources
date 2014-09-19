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

use Nix\Forms\Rule,
	Nix\Forms\Condition;

/**
 * Javascript validator interface
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\JsValidators
 */
interface IJsValidator
{
	/**
	 * Adds rule
	 * 
	 * @param   Rule  $rule
	 * @return  IFormJsValidator
	 */
	public function addRule(Rule $rule);

	/**
	 * Adds condition
	 * 
	 * @param   Condition  $condition
	 * @return  IFormJsValidator
	 */
	public function addCondition(Condition $condition);

	/**
	 * Returns raw javascript code
	 * 
	 * @return  string
	 */
	public function getCode();
}