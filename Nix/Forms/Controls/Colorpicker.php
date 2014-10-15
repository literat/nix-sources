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
 * Colopicker Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class Colorpicker extends Input
{
	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		if(!empty($value)) {
			//$value = '000000';
		}

		parent::setValue($value);
	}

	/**
	 * Returns value for html tag
	 * 
	 * @return string
	 */
	public function getHtmlValue()
	{
		$value = parent::getHtmlValue();
		//$value = preg_replace('#(#)([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})#', '$2', $value);
		
		return $value;		
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
	
		return $control->class('colorPicker');
	}
}