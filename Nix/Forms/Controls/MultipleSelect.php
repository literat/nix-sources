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

use Nix\Forms\Html,
	Nix\Forms\Controls\Select;

/**
 * MultipleSelect Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class MultipleSelect extends Select
{
	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		foreach((array) $value as $key) {
			if(!in_array($key, $this->values)) {
				return false;
			}
		}

		$this->value = $value;

		return true;
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl()->multiple(true);
		$control->name .= '[]';
		
		return $control;
	}

	/**
	 * Returns option control
	 * 
	 * @param string $name control name
	 * @param string $value control value
	 * @return Html
	 */
	protected function getOption($name, $value)
	{
		return Html::el(
			'option',
			$value,
			array(
				'value' => $name,
				'selected' => in_array(
					$name,
					(array) $this->getHtmlValue()
				)
			)
		);
	}
}
