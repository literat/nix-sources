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

use Nix,
	Nix\Utils\Tools,
	Nix\Forms\Html,
	Nix\Forms\Form,
	Nix\Forms\Renderers\IFormRenderer,
	Nix\Config\Configurator;

/**
 * Form renderer
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms\Renderers
 */
abstract class FormRenderer extends Nix\Object implements IFormRenderer
{
	/** @var IFormJsValidator form js validator */
	public $javascript = null;

	/** @var array - Wrappers, set the tag name or Html object */	
	public $wrappers = array(
		'part' => null,
		'pair' => null,
		'label' => null,
		'control' => null,
		'button-separator' => null,
	);

	/** @var Form object */
	protected $form;

	/**
	 * Constructor
	 * 
	 * @return FormRenderer
	 */
	public function __construct()
	{
		$js = 'jquery';
		if(class_exists('Config', false)) {
			$js = Configurator::read('Forms.javascript.validator', 'jquery');
		}

		$this->setJsValidator($js);
	}

	/**
	 * Sets Form object
	 * 
	 * @param Form $form
	 * @return FormRenderer
	 */
	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	/**
	 * Sets javascript validator
	 * 
	 * @param string|IFormJsValidator $validator name (just type of renderer - jquery) or object
	 * @return  
	 */
	public function setJsValidator($validator)
	{
		if(is_object($validator)) {
			if(!($validator instanceof IFormJsValidator)) {
				throw new \Exception('Form JS validator must implement IFormJsValidator interface.');
			}

			$this->javascript = $validator;
		} else {
			$name = Tools::dash($validator);
			require_once dirname(__FILE__) . "/JsValidators/$name" . "JsValidator.php";
			$class= "Nix\Forms\JsValidators\\" . $validator . "JsValidator";
			$this->javascript = new $class();
		}

		return $this;
	}

	/**
	 * Renders form $part
	 * 
	 * @param string $part part of form
	 * @param mixed attributes and setting
	 * @return string
	 */
	public function render($part = null)
	{
		$attrs = func_get_args();
		array_shift($attrs);
		switch ($part) {
			case 'js':
			case 'javascript':
				return $this->renderJavascript();
			case 'start':
				return $this->renderStart();
			case 'end':
				return $this->renderEnd();
			case 'part':
				return $this->renderPart(array_shift($attrs), array_shift($attrs), array_shift($attrs));
			case 'pair':
				return $this->renderPair(array_shift($attrs));
			case 'form':
			default:
				return $this->renderForm();
		}
	}

	/**
	 * Renders form
	 * 
	 * @return string
	 */
	protected function renderForm()
	{
		return $this->renderStart()
		     . $this->renderPart()
		     . $this->renderEnd();
	}

	/**
	 * Renders form start tag
	 * 
	 * @return string
	 */
	protected function renderStart()
	{
		return $this->form->startTag();
	}

	/**
	 * Renders form end tag
	 * 
	 * @return string
	 */
	protected function renderEnd()
	{
		return $this->form->endTag() . "\n";
	}

	/**
	 * Renders body
	 * 
	 * @param array $controls controls for rendering
	 * @param string $heading
	 * @param array $attrs
	 * @return string
	 */
	protected function renderPart($controls = array(), $heading = '', $attrs = array())
	{
		$partW = $this->preparePart($this->getWrapper('part'), $heading);
		$partW->setAttrs($attrs);
		if($controls === null) {
			$controls = array();
		}

		foreach($this->form as $name => /** @var FormControl */$control) {
			if(empty($controls) && $control->isRendered()) {
				continue;
			}

			if(!empty($controls) && !in_array($control->name, $controls)) {
				continue;
			}

			if($control instanceof FormHiddenControl) {
				continue;
			}

			if($control instanceof FormButtonControl) {
				$controlW = $this->getWrapper('control');
				foreach($this->form as $control) {
					if($control instanceof FormButtonControl && ((in_array($control->name, $controls) && !empty($controls)) || empty($controls))) {
						$controlW->addHtml($control->control()->render())
						         ->addHtml($this->getWrapper('button-separator')->render());
					}
				}

				$pairW = $this->preparePair($this->getWrapper('pair'), $control);
				$pairW->addHtml($this->getWrapper('label'))
				      ->addHtml($controlW);

				$partW->addHtml($pairW);
			} else {
				$partW->addHtml($this->renderPair($name));
			}

			# creating js validation
			if($this->javascript instanceof IFormJsValidator) {
				foreach($control->getRules() as $rule) {
					$this->javascript->addRule($rule);
				}

				foreach($control->getConditions() as $condition) {
					$this->javascript->addCondition($condition);
				}
			}
		}

		if($this->javascript instanceof IFormJsValidator) {
			return $partW->render(0) . $this->javascript->getCode();
		}

		return $partW->render(0);
	}

	/**
	 * Renders block of control and label
	 * 
	 * @param string $name control name
	 * @return string
	 */
	protected function renderPair($name)
	{
		if(!isset($this->form[$name])) {
			throw new \Exception('Undefined form control in render-pair.');
		}

		$pairW = $this->preparePair($this->getWrapper('pair'), $this->form[$name]);
		$pairW->addHtml($this->renderLabel($name))
		      ->addHtml($this->renderControl($name));

		return $pairW->render(0);
	}

	/**
	 * Renders control
	 * 
	 * @param string $name control name
	 * @return string
	 */
	protected function renderControl($name)
	{
		$control = $this->form[$name];
		$controlW = $this->prepareControl($this->getWrapper('control'), $control);
		$controlW->addHtml($control->control()->render());
		$controlW->addHtml($control->error()->render());

		return $controlW->render(1);
	}

	/**
	 * Renders label
	 * 
	 * @param string $name control name
	 * @return string
	 */
	protected function renderLabel($name)
	{
		$labelW = $this->prepareLabel($this->getWrapper('label'), $this->form[$name]);

		$label = $this->form[$name]->label();
		if($label instanceof Nix\Forms\Html) {
			$labelW->addHtml($label->render());
		}

		return $labelW->render(1);
	}

	/**
	 * Renders javascript
	 * 
	 * @return string
	 */
	protected function renderJavascript()
	{
		if(!($this->javascript instanceof IFormJsValidator)) {
			return;
		}

		foreach($this->form as $control) {
			foreach($control->getRules() as $rule) {
				$this->javascript->addRule($rule);
			}

			foreach($control->getConditions() as $condition) {
				$this->javascript->addCondition($condition);
			}
		}

		return $this->javascript->getCode();
	}

	/**
	 * Returns wrapper object
	 * 
	 * @param string $type wrapper type
	 * @return Html
	 */
	protected function getWrapper($type)
	{
		if(!array_key_exists($type, $this->wrappers)) {
			throw new \Exception("Wrapper $type does not exists.");
		}

		return ($this->wrappers[$type] instanceof Nix\Forms\Html) ? clone $this->wrappers[$type] : Html::el($this->wrappers[$type]);
	}

	/**
	 * Prepares wrapperes
	 * 
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function preparePart($wrapper, $control)
	{
		return $wrapper;
	}

	/**
	 * Prepares wrapperes
	 * 
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function preparePair($wrapper, $control)
	{
		return $wrapper;
	}

	/**
	 * Prepares wrapperes
	 * 
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function prepareControl($wrapper, $control)
	{
			return $wrapper;
	}

	/**
	 * Prepares wrapperes
	 * 
	 * @param Html $wrapper
	 * @param FormControl $control
	 * @return Html
	 */
	protected function prepareLabel($wrapper, $control)
	{
		return $wrapper;
	}
	/***/
}