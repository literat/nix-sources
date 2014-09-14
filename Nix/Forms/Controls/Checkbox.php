<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Forms\Controls;

use Nix\Forms\Controls\Input;

/**
 * Checkbox Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class Checkbox extends Input
{
	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		$this->value = (bool) $value;
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('checkbox')->class('checkbox');
	}

	/**
	 * Returns html control
	 * 
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->value(null)->checked($this->value);
	}
}