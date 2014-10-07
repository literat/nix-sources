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

use Nix\Application\Application,
	Nix\Config\Configurator,
	Nix\Http\Http;

/**
 * Application Error
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Application
 */
class ApplicationError extends \Exception
{
	/** @var string - Error template name */
	public $errorFile;

	/**
	 * Constructor
	 * @param string $template template name
	 * @param bool $debug is exception debuggable?
	 * @param int $errorCode http error code
	 */
	public function __construct($template, $debug = false, $errorCode = 404)
	{
		Application::$error = true;

		if($debug === true && Configurator::read('Core.debug') == 0) {
			$template = '404';
		}

		if($errorCode !== null) {
			Http::$response->error($errorCode);
		}

		$this->errorFile = $template;
		parent::__construct("Application error: $template.");
	}
}