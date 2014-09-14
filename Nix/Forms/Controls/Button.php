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

use Nix\Forms\Form,
	Nix\Forms\Controls\FormControl;

/**
 * Button Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
abstract class Button extends FormControl
{
	/**
	 * Constructor
	 * 
	 * @param Form $form
	 * @param string $name control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormButtonControl
	 */
	public function __construct($form, $name, $label)
	{
		parent::__construct($form, $name, false);
		$this->setValue($label);
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