<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Application;

/**
 * Application Exception
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Application
 */
class ApplicationException extends \Exception
{
	/** @var string error template name */
	public $errorFile;

	/** @var mixed error variable name */
	public $variable;

	/**
	 * Constructor
	 * @param string $variable error type
	 * @param string $errorType template variable
	 * @return ApplicationException
	 */
	public function __construct($errorType, $variable = null)
	{
		static $errors = array('routing', 'missing-controller', 'missing-method', 'missing-template', 'missing-helper', 'missing-file');
		if(!in_array($errorType, $errors)) {
			throw new \Exception("Unsupported ApplicationException type '$error'.");
		}

		$this->errorFile = $errorType;
		$this->variable = $variable;
		parent::__construct(ucfirst(str_replace('-', ' ', $errorType)) . " '$variable'.");
	}
}