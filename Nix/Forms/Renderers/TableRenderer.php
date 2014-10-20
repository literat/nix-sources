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

use Nix\Forms\Html,
	Nix\Forms\Renderers\FormRenderer;

/**
 * Form table renderer
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Renderers
 */
class TableRenderer extends FormRenderer
{
	/** @var array - Wrappers */
	public $wrappers = array(
		'part' => 'table',
		'pair' => 'tr',
		'label' => 'th',
		'control' => 'td',
		'button-separator' => null,
	);

	/**
	 * Prepares part
	 * 
	 * @param Html $wrapper
	 * @param Html $heading
	 * @return Html
	 */
	protected function preparePart($wrapper, $heading)
	{
		if(empty($heading)) {
			return $wrapper;
		}

		$heading = Html::el('h3', $heading);
		
		return $wrapper->prepend($heading->render(0));
	}

	/**
	 * Prepares pair
	 * 
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function preparePair($wrapper, $control)
	{
		static $i = 0;
		if($i++ % 2) {
			$wrapper->class('odd');
		}

		if($control->htmlRequired) {
			$wrapper->class('required');
		}

		return $wrapper;
	}

	/**
	 * Prepares label
	 * 
	 * @param 	Html 		$wrapper 	wrapper
	 * @param 	FormControl $control 	form control
	 * @return 	Html
	 */
	protected function prepareLabel($wrapper, $control)
	{
		if($control->htmlRequired) {
			$wrapper->class('required');
		}

		return $wrapper;
	}
}