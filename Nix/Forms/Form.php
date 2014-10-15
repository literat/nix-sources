<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Forms;

use Nix,
	Nix\Http\Http,
	Nix\Utils\Tools,
	Nix\Forms\Html,
	Nix\Forms\Controls\Text,
	Nix\Forms\Controls\Textarea,
	Nix\Forms\Controls\Radio,
	Nix\Forms\Controls\Select,
	Nix\Forms\Controls\MultipleSelect,
	Nix\Forms\Controls\Password,
	Nix\Forms\Controls\Checkbox,
	Nix\Forms\Controls\MultiCheckbox,
	Nix\Forms\Controls\Submit,
	Nix\Forms\Controls\Datepicker,
	Nix\Forms\Controls\DateTimepicker,
	Nix\Forms\Controls\Colorpicker,
	Nix\Forms\Controls\File,
	Nix\Forms\Controls\UploadedFile,
	Nix\Forms\Controls\Hidden,
	Nix\Forms\Renderers,
	Nix\Debugging\Debugger;

/**
 * Form class
 * Creates, observes, renders html forms
 * 
 * @property-read IFormRenderer $renderer
 * 
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Forms
 */
class Form extends Nix\Object implements \ArrayAccess,\IteratorAggregate
{
	/** @var string - Name of control with hash */
	public static $SECURITY_CONTROL = 'csrf_protection';

	/** @var array - Submitted data */
	public $data = array();

	/** @var string - Form name */
	public $name;

	/** @var Html */
	private $form;

	/** @var bool|string - Submit button name */
	private $submitBy = false;

	/** @var array */
	private $controls = array();

	/** @var bool - Is form CSRF protected? */
	private $protected = false;

	/** @var FormRenderer */
	private $renderer;

	/**
	 * Constructor
	 * 
	 * @param string $url
	 * @param string $name form name
	 * @param string $method form method
	 * @return Form
	 */
	public function __construct($url = null, $name = null, $method = 'post')
	{
		# application url proccesing
		if(class_exists('Application', false) && !empty($url)) {
			$url = call_user_func(array(Controller::get(), 'url'), $url);
		}

		if(empty($name)) {
			$name = 'form';
		}

		static $counter = 0;
		if($name == 'form' && $counter++ == 0) {
			$this->name = 'form';
		} elseif ($name == 'form') {
			$this->name = 'form' . $counter++;
		} else {
			$this->name = $name;
		}

		$this->form = Html::el('form', null, array(
			'id' => $this->name,
			'method' => $method,
			'action' => $url
		));
	}

	/* ========== Controls ========== */

	/**
	 * Adds CSRF protection
	 * 
	 * @param string $errorMessage
	 * @return Form
	 */
	public function addProtection($errorMessage = 'Security token did not match - possible CSRF attack!')
	{
		if(!class_exists('Session')) {
			throw new \Exception('Form protection needs loaded Session class.');
		}

		$this->protected = true;
		$this->controls[self::$SECURITY_CONTROL] = new Hidden($this, self::$SECURITY_CONTROL);

		$session = Session::getNamespace('Form.csrf-protection');
		$key = $this->name;

		if($session->exists($key)) {
			$hash = $session->get($key);
		} else {
			$hash = md5(Session::getName());
			$session->set($key, $hash);
		}

		$this->controls[self::$SECURITY_CONTROL]->setValue($hash);
		$this->controls[self::$SECURITY_CONTROL]->addRule(Rule::EQUAL, $hash, $errorMessage);
		
		return $this;
	}

	/**
	 * Adds text input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addText($control, $label = null)
	{
		$this[$control] = new Text($this, $control, $label);

		return $this;
	}

	/**
	 * Adds textarea input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addTextarea($control, $label = null)
	{
		$this[$control] = new Textarea($this, $control, $label);

		return $this;
	}

	/**
	 * Adds password input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addPassword($control, $label = null)
	{
		$this[$control] = new Password($this, $control, $label);

		return $this;
	}

	/**
	 * Adds datepicker input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addDatepicker($control, $label = null)
	{
		$this[$control] = new Datepicker($this, $control, $label);

		return $this;
	}

	/**
	 * Adds datetimepicker input
	 *
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addDateTimepicker($control, $label = null)
	{
		$this[$control] = new DateTimepicker($this, $control, $label);

		return $this;
	}

	/**
	 * Adds colorpicker input
	 *
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addColorpicker($control, $label = null)
	{
		$this[$control] = new Colorpicker($this, $control, $label);

		return $this;
	}

	/**
	 * Adds file input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addFile($control, $label = null)
	{
		$this->form->enctype = 'multipart/form-data';
		$this[$control] = new File($this, $control, $label);

		return $this;
	}

	/**
	 * Adds select input
	 * 
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addSelect($control, $options, $label = null)
	{
		$this[$control] = new Select($this, $control, $options, $label);

		return $this;
	}

	/**
	 * Adds checkbox input
	 * 
	 * @param string $control control name
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addCheckbox($control, $label = null)
	{
		$this[$control] = new Checkbox($this, $control, $label);

		return $this;
	}

	/**
	 * Adds radio inputs
	 * 
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addRadio($control, $options, $label = null)
	{
		$this[$control] = new Radio($this, $control, $options, $label);

		return $this;
	}

	/**
	 * Adds hidden input
	 * 
	 * @param string $control control name
	 * @return Form
	 */
	public function addHidden($control)
	{
		$this[$control] = new Hidden($this, $control);

		return $this;
	}

	/* ========== Multi Controls ========== */

	/**
	 * Adds multiple select input
	 * 
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addMultiSelect($control, $options, $label = null)
	{
		$this[$control] = new MultipleSelect($this, $control, $options, $label);

		return $this;
	}

	/**
	 * Adds multi checkbox inputs
	 * 
	 * @param string $control control name
	 * @param   array   options
	 * @param mixed $label label (null = from name, false = no label)
	 * @return Form
	 */
	public function addMultiCheckbox($control, $options, $label = null)
	{
		$this[$control] = new MultiCheckbox($this, $control, $options, $label);

		return $this;
	}

	/* ========== Button Controls ========== */

	/**
	 * Adds submit button
	 * 
	 * @param string $control control name
	 * @param string $control control  label
	 * @return Form
	 */
	public function addSubmit($control = 'submit', $label = null)
	{
		$this[$control] = new Submit($this, $control, $label);

		return $this;
	}

	/**
	 * Adds image submit button
	 * 
	 * @param string $control control name
	 * @param   string  image src
	 * @return Form
	 */
	public function addImageSubmit($control = 'submit', $src = null)
	{
		$this[$control] = new ImageSubmit($this, $control, $src);

		return $this;
	}

	/**
	 * Adds reset button
	 * 
	 * @param string $control control name
	 * @param string $control control  label
	 * @return Form
	 */
	public function addReset($control = 'reset', $label = null)
	{
		$this[$control] = new Reset($this, $control, $label);

		return $this;
	}

	/* ========== Methods ========== */

	/**
	 * Renders form html start tag
	 * 
	 * @param array $attrs attributes
	 * @return string
	 */
	public function startTag($attrs = array())
	{
		return $this->form->setAttrs($attrs)->startTag();
	}

	/**
	 * Renders form end tag with hidden inputs
	 * 
	 * @return string
	 */
	public function endTag()
	{
		$render = '';
		foreach($this->controls as /** @var FormControl */$control) {
			if($control instanceof Hidden && !$control->isRendered()) {
				$render .= $control->control() . $control->error();
			}
		}

		$render .= $this->form->endTag();

		return $render;
	}

	/**
	 * Returns true/false if the form has been submitted
	 * 
	 * Arguments: no submit button name = check only if form has been submitted
	 *            buton name/names = check if form has been submitted by button/buttons
	 *            
	 * @param string $name button name
	 * @return bool
	 */
	public function isSubmit()
	{
			if(empty($this->data)) {
				$this->loadData();
			}

		$buttons = func_get_args();

		if(empty($buttons)) {
			return (bool) $this->submitBy;
		} else {
			return in_array($this->submitBy, $buttons);
		}
	}

	/**
	 * Returns true if the form is valid
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		$valid = true;
		foreach($this->controls as $control) {
			if(!$control->isValid()) {
				$valid = false;
			}
		}

		return $valid;
	}

	/**
	 * Checks whether form has errors
	 * 
	 * @return bool
	 */
	public function hasErrors()
	{
		foreach($this->controls as $control) {
			if($control->hasError()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sets default values for controls (only if the form is not submitted)
	 * 
	 * @param array $defaults default values - format is array with $controlName => $value
	 * @param bool $checkSubmitted shoul this method chech if form is submitted?
	 * @return Form
	 */
	public function setDefaults($defaults, $checkSubmitted = true)
	{
		if($checkSubmitted && $this->isSubmit()) {
			return $this;
		}

		foreach((array) $defaults as $id => $value) {
			if(isset($this->controls[$id])) {
				$this->controls[$id]->setValue($value);
			}
		}

		return $this;
	}

	/**
	 * Sets / creates renderer instance
	 * 
	 * @param IFormRenderer|string $renderer renderer name
	 * @throws Exception
	 * @return Form
	 */
	public function setRenderer($renderer)
	{
		if(is_object($renderer)) {
			if(!($renderer instanceof IFormRenderer)) {
				throw new Exception('Renderer must be instance of IFormRenderer.');
			}

			$this->renderer = $renderer;
		} else {
			$renderer = ucfirst($renderer);
			$name = Tools::dash($renderer);
			require_once dirname(__FILE__) . "/Renderers/" . $name . "Renderer.php";
			$class= "Nix\Forms\Renderers\\" . $renderer . "Renderer";
			$this->renderer = new $class();
		}

		$this->renderer->setForm($this);

		return $this;
	}

	/**
	 * Returns form
	 * 
	 * @return Html
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Returns Renderer
	 * 
	 * @return IFormRenderer
	 */
	public function getRenderer()
	{
		if(!($this->renderer instanceof Nix\Forms\Renderers\IFormRenderer)) {
			$this->setRenderer('table');
		}

		return $this->renderer;
	}

	/**
	 * Array-access interface
	 */
	public function offsetSet($id, $value)
	{
		$this->controls[$id] = $value;
	}

	/**
	 * Array-access interface
	 * 
	 * @return FormControl
	 */
	public function offsetGet($id)
	{
		if(isset($this->controls[$id])) {
			return $this->controls[$id];
		}

		throw new \Exception("Undefined form control with name '$id'.");
	}

	/**
	 * Array-access interface
	 * 
	 * @throws Exception
	 */
	public function offsetUnset($id)
	{
		if(isset($this->controls[$id])) {
			unset($this->controls[$id]);
		}

		throw new \Exception("Undefined form control with name '$id'.");
	}

	/**
	 * Array-access interface
	 * 
	 * @return bool
	 */
	public function offsetExists($id)
	{
		return isset($this->controls[$id]);
	}

	/**
	 * ArrayIterator interface
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->controls);
	}

	/**
	 * toString interface
	 * 
	 * @return string
	 */
	public function __toString()
	{
		try {
			if(!($this->renderer instanceof IFormRenderer)) {
				$this->getRenderer();
			}

			$render = $this->renderer->render();
		} catch (\Exception $e) {
			return $e->getMessage();
		}

		return $render;
	}

	/**
	 * Loads submited data into the form
	 * 
	 * @return Form
	 */
	private function loadData()
	{
		// we must have existing Http object first
		if(!isset(Http::$request)) {
			$Http = new Http();
		}

		// then we can get data
		$data = Http::$request->getForm();

		if(isset($data[$this->name])) {
			$data = $data[$this->name];
		} else {
			return $this;
		}

		foreach($this->controls as $id => $control) {
			if(!isset($data[$id])) {
				if($control instanceof MultiCheckbox || $control instanceof MultipleSelect) {
					$data[$id] = array();
				} else {
					continue;
				}
			}

			if($control instanceof File) {
				$this->data[$id] = new UploadedFile($control, $data[$id]);
				$control->setValue($this->data[$id]->name);
			} elseif ($control instanceof Submit) {
				$this->submitBy = $id;
			} else {
				$control->setValue($data[$id]);
				$this->data[$id] = $control->getValue();
			}
		}

		if($this->protected) {
			unset($this->data[self::$SECURITY_CONTROL]);
			$session = Session::getNamespace('Form.csrf-protection');
			$session->delete($this->name);
		}

		return $this;
	}
}