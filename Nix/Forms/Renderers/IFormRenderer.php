<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Forms\Renderers;

use Nix\Forms\Form;

/**
 * Form renderer interface
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Renderers
 */
interface IFormRenderer
{
	/**
	 * Sets Form
	 * 
	 * @param Form $form
	 * @return Form
	 */
	public function setForm(Form $form);

	/**
	 * Renders form (or part of form)
	 * 
	 * @param string $part
	 * @return string
	 */
	public function render($part = null);
}