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
	Nix\Forms\Controls\Input;

/**
 * MultiCheckbox Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class MultiCheckbox extends Input
{
	/** @var string - Control separator */
	public $listSeparator = '<br />';

	/** @var array */
	protected $options = array();

	/**
	 * Constructor
	 * 
	 * @param Form $form
	 * @param string $name control name
	 * @param array $options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormMultiCheckboxControl
	 */
	public function __construct($form, $name, $options, $label)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
	}

	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		if(is_string($value)) {
			$value = explode(',', $value);
		}

		foreach((array) $value as $key) {
			if(!isset($this->options[$key])) {
				return false;
			}
		}

		$this->value = $value;
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		$control = parent::getHtmlControl()->type('checkbox')->class('checkbox');
		$control->name .= '[]';

		return $control;
	}

	/**
	 * Returns html control
	 * 
	 * @param mixed $key key name of requested checkbox
	 * @return Html
	 */
	public function getControl($key = null)
	{
		$label = Html::el('label');
		$control = parent::getControl();
		if($key === null) {
			$container = Html::el('div')->id($control->id)->class('multi-inputs');
		} elseif(!isset($this->options[$key])) {
			return null;
		}


		$id = $control->id;
		foreach($this->options as $name => $val) {
			if($key !== null && $key != $name) {
				continue;
			}

			$control->id = $id . '-' . $name;
			$control->value = $name;
			$control->checked = in_array($name, (array) $this->getHtmlValue());

			if($key !== null) {
				return $control;
			}

			$label->for = $id . '-' . $name;
			$label->setText($val);
			$container->addHtml($control->render() . $label->render() . $this->listSeparator);
		}

		return $container;
	}
}