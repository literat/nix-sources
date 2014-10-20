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
	Nix\Forms\Html,
	Nix\Forms\Controls\Input;

/**
 * Radio Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
class Radio extends Input
{
	/** @var string - Control separator */
	public $listSeparator = '<br />';

	/** @var array - Options */
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
	 * @return FormRadioControl
	 */
	public function __construct(Form $form, $name, $options, $label = null)
	{
		parent::__construct($form, $name, $label);
		$this->options = $options;
		$this->values = array_keys($options);
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return parent::getHtmlControl()->type('radio');
	}

	/**
	 * Returns html control
	 * 
	 * @param mixed $key key name of requested radio
	 * @return Html
	 */
	public function getControl($key = null)
	{
		$label = Html::el('label');
		$radio = parent::getControl();
		if($key === null) {
			$container = Html::el('div')->id($radio->id)->class('multi-inputs');
		} elseif(!isset($this->options[$key])) {
			return null;
		}

		$id = $radio->id;
		foreach($this->options as $name => $val) {
			if($key !== null && $key != $name) {
				continue;
			}

			$radio->id = $id . $name;
			$radio->value = $name;
			$radio->checked = (string) $name === $this->getHtmlValue();

			if($key !== null) {
				return $radio;
			}

			$label->for = $id . $name;
			if($val instanceof Html) {
				$label->setHtml($val);
			} else {
				$label->setText($val);
			}

			$container->addHtml($radio->render() . $label->render() . $this->listSeparator);
		}

		return $container;
	}

	/**
	 * Returns html label
	 * 
	 * @return Html
	 */
	protected function getLabel()
	{
		return parent::getLabel()->for(null);
	}
}