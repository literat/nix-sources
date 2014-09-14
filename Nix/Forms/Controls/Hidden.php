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
 * Hidden field Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Loaders
 */
class Hidden extends Input
{
	/**
	 * Constructor
	 * 
	 * @param Form $form
	 * @param string $name control name
	 * @return FormHiddenControl
	 */
	public function __construct(Form $form, $name)
	{
		parent::__construct($form, $name, false);
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl();
		
		return $control->type('hidden');
	}
}