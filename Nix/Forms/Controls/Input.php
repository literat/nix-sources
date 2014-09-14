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

use Nix\Forms\Controls\FormControl;

/**
 * Input Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
abstract class Input extends FormControl
{
	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('input');
	}

	/**
	 * Returns html control
	 * 
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->value($this->getHtmlValue());
	}
}