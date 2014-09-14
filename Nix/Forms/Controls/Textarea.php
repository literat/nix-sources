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

use Nix\Forms,
	Nix\Forms\Controls\FormControl;

/**
 * Textarea Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class Textarea extends FormControl
{
	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();

		return $control->setTag('textarea');
	}

	/**
	 * Returns html control
	 * 
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getHtmlValue());
	}
}