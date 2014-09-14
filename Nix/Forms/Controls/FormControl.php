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

use Nix,
	Nix\Forms\Form,
	Nix\Forms\Html,
	Nix\Forms\Rule,
	Nix\Forms\Condition;

/**
 * Form Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Controls
 */
abstract class FormControl extends Nix\Object
{
	/** @var string */
	protected $name;

	/** @var Form */
	protected $form;

	/** @var Html */
	protected $control;

	/** @var Html|bool */
	protected $label = false;

	/** @var mixed */
	protected $value;

	/** @var mixed */
	protected $emptyValue;

	/** @var array */
	protected $filters = array();

	/** @var string */
	protected $error;

	/** @var array */
	protected $rules = array();

	/** @var array */
	protected $conditions = array();

	/**
	 * HTML elements 
	 * @var Html
	 */
	protected $htmlControl;
	protected $htmlLabel;
	protected $htmlError;
	protected $htmlErrorLabel;
	/***/

	/** @var bool - Is HTML control rendered? */
	protected $isRendered = false;

	/** @var array */
	protected $htmlRequired = false;

	/**
	 * Constructor
	 * 
	 * @param Form $form
	 * @param string $name control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return FormControl
	 */
	public function __construct(Nix\Forms\Form $form, $name, $label = null)
	{
		$this->form = $form;
		$this->name = $name;

		$this->htmlControl      = $this->getHtmlControl();
		$this->htmlLabel        = $this->getHtmlLabel($label);
		$this->htmlError        = $this->getHtmlError();
		$this->htmlErrorLabel   = $this->getHtmlErrorLabel();
	}

	/**
	 * Returns Html object of form control
	 * 
	 * @return Html
	 */
	protected function getHtmlControl()
	{
		return Html::el(null, null, array(
			'name' => $this->form->name . '[' . $this->name . ']',
			'id' => $this->form->name . '-' . $this->name
		));
	}

	/**
	 * Returns Html object of control label
	 * 
	 * @param Html|string|false $label
	 * @return Html|false
	 */
	protected function getHtmlLabel($label)
	{
		if($label === false) {
			return false;
		}

		if(!($label instanceof Html)) {
			$label = Html::el('label', is_null($label) ? ucfirst($this->name) : $label);
		}

		$label->for($this->form->name . '-' . $this->name)
		      ->id($this->form->name . '-' . $this->name . '-label');

		return $label;
	}

	/**
	 * Returns Html object container of control error
	 * 
	 * @return Html
	 */
	protected function getHtmlError()
	{
		return Html::el('div', null, array(
			'id' => $this->form->name . '-' . $this->name . '-error',
			'class' => 'control-error'
		));
	}

	/**
	 * Returns Html object of control error
	 * 
	 * @return Html
	 */
	protected function getHtmlErrorLabel()
	{
		return Html::el('label', null, array(
			'for' => $this->form->name . '-' . $this->name			
		));
	}

	/**
	 * Set the control value
	 * 
	 * @param mixed $value new value
	 * @return bool
	 */
	public function setValue($value)
	{
		$this->value = $this->filter($value);
	}

	/**
	 * Return value
	 * 
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Adds rule for the actual control
	 * 
	 * @param string $rule validation rule
	 * @param mixed $arg validation argument
	 * @param string $message error message
	 * @return Condition
	 */
	public function addRule($rule, $arg = null, $message = null)
	{
		if($rule == Rule::FILLED || ($rule == Rule::LENGTH && $arg > 0)) {
			$this->htmlRequired = true;
		}

		$this->rules[] = new Rule($this, $rule, $arg, $message);

		return $this;
	}

	/**
	 * Adds condition to input element
	 * 
	 * @param string $rule validation rule
	 * @param mixed $arg validation argument
	 * @return Condition
	 */
	public function addCondition($rule, $arg = null)
	{
		return $this->conditions[] = new Condition($this, $rule, $arg);
	}

	/**
	 * Checks if control value is valid
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		# chech conditions and add theirs rules
		foreach($this->conditions as $condition) {
			if($condition->isValid()) {
				foreach($condition->rules as $rule) {
					$this->rules[] = $rule;
				}
			}
		}

		# validate rules
		foreach($this->rules as $rule) {
			if(!$rule->isValid()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns true when control was rendered
	 * 
	 * @param bool $set set redendered as true
	 * @return bool
	 */
	public function isRendered($set = false)
	{
		if($set) {
			$this->isRendered = true;
		}

		return $this->isRendered;
	}

	/**
	 * Returns Html object of error label
	 * 
	 * @return Html
	 */
	public function getErrorLabel()
	{
		/** @var Html */
		$label = clone $this->htmlLabel;
		$label->setText($this->error);

		return $label;
	}

	/**
	 * Renders label tag
	 * 
	 * @return Html
	 */
	public function label()
	{
		return $this->getLabel();
	}

	/**
	 * Renders html control tag
	 * 
	 * @return Html
	 */
	public function control()
	{
		return $this->getControl();
	}

	/**
	 * Returns html control error block
	 * 
	 * @return Html
	 */
	public function error()
	{
		if($this->hasError()) {
			$this->htmlErrorLabel->setText($this->error);
			$this->htmlError->setHtml($this->htmlErrorLabel);
		}
		
		return $this->htmlError;
	}

	/**
	 * Sets error
	 * 
	 * @param string $text error text
	 * @return FormControl
	 */
	public function setError($text)
	{
		$this->error = $text;
		return $this;
	}

	/**
	 * Checks whether control has errors
	 * 
	 * @return bool
	 */
	public function hasError()
	{
		return !empty($this->error);
	}

	/**
	 * Returns html label
	 * 
	 * @return Html
	 */
	protected function getLabel()
	{
		return $this->htmlLabel;
	}

	/**
	 * Returns html control
	 * 
	 * @return Html
	 */
	protected function getControl()
	{
		$this->isRendered = true;

		return $this->htmlControl;
	}

	/**
	 * Returns form tag
	 * 
	 * @return Html
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Returns form name
	 * 
	 * @param bool $fullName return full name with form?
	 * @return string
	 */
	public function getName($fullName = false)
	{
		if($fullName) {
			return $this->form->name . '-' . $this->name;
		}

		return $this->name;
	}

	/**
	 * Returns if control is required
	 * 
	 * @return bool
	 */
	public function getHtmlRequired()
	{
		return $this->htmlRequired;
	}

	/**
	 * Returns empty value
	 * 
	 * @return mixed
	 */
	public function getEmptyValue()
	{
		return $this->emptyValue;
	}

	/**
	 * Returns rules
	 * 
	 * @return array
	 */
	public function getRules()
	{
		return $this->rules;
	}

	/**
	 * Returns conditions
	 * 
	 * @return array
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * Sets empty value
	 * 
	 * @param mixed $value empty value
	 * @return FormControl
	 */
	public function setEmptyValue($value)
	{
		$this->emptyValue = $value;
		
		return $this;
	}

	/**
	 * Magic method
	 */
	public function __get($key)
	{
		if(in_array($key, array('label', 'control', 'error'))) {
			return $this->{$key}();
		} else {
			return parent::__get($key);
		}
	}

	/**
	 * Returns value for html tag
	 * 
	 * @return string
	 */
	protected function getHtmlValue()
	{
		if($this->value === '0' || !empty($this->value)) {
			return $this->value;
		} else {
			return $this->emptyValue;
		}
	}

	/**
	 * Filters value by filters and null them if value is equall to emptyValue
	 * 
	 * @return mixed
	 */
	protected function filter($value)
	{
		foreach($this->filters as $filter) {
			$value = (string) call_user_func($filter, $value);
		}

		if($this->emptyValue == $value) {
			$value = '';
		}

		return $value;
	}
}