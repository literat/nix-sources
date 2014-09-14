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
	Nix\Forms\Controls\FormControl;

/**
 * Select Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class Select extends FormControl
{
	/** @var array */
	protected $options = array();

	/** @var array - Options without tree structure */
	protected $values = array();

	/**
	 * Constructor
	 * 
	 * @param Form $form
	 * @param string $name control name
	 * @param array $options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormSelectControl
	 */
	public function __construct($form, $name, $options, $label = null)
	{
		$this->options = $options;
		foreach($this->options as $key => $option) {
			if(is_array($option)) {
				foreach(array_keys($option) as $val) {
					$this->values[] = $val;
				}
			} else {
				$this->values[] = $key;
			}
		}

		parent::__construct($form, $name, $label);
	}

	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		if(!$this->isAllowedValue($value)) {
			return false;
		}

		parent::setValue($value);

		return true;
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->setTag('select')->onfocus("this.onmousewheel=function(){return false}");
	}

	/**
	 * Returns html control
	 * 
	 * @return Html
	 */
	protected function getControl()
	{
		return parent::getControl()->setHtml($this->getOptions());
	}

	/**
	 * Returns treu if is the value allowed
	 * 
	 * @param string $value
	 * @return bool
	 */
	protected function isAllowedValue($value)
	{
		if(!in_array($value, $this->values)) {
			return false;
		}

		return true;
	}

	/**
	 * Returns html options tags
	 * 
	 * @return string
	 */
	protected function getOptions()
	{
		$options = Html::el();
		if($this->emptyValue != '') {
			$options->addHtml($this->getOption('', $this->emptyValue));
		}

		foreach($this->options as $key => $val) {
			if(is_array($val)) {
				$optgroup = Html::el('optgroup');
				$optgroup->label($name);
				foreach($value as $subKey => $subVal) {
					$optgroup->addHtml($this->getOption($subKey, $subVal));
				}

				$options->addHtml($optgroup);
			} else {
				$options->addHtml($this->getOption($key, $val));
			}
		}

		return $options;
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
				'selected' => $this->getHtmlValue() == $name
			)
		);
	}
}