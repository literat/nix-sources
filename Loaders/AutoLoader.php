<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Loaders;

use Nix,
	Nix\Object;

/**
 * AutoLoader
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Loaders
 */
abstract class AutoLoader extends Object
{
	/** @var array */
	private $callbacks = array();

	/**
	 * Autoload handler
	 *
	 * @param  string $class class name
	 */
	public function autoloadHandler($class)
	{
		foreach($this->callbacks as $cb) {
			call_user_func($cb, $class);
			if(class_exists($class, false)) {
				break;
			}
		}
	}

	/**
	 * Register callback for loader handler
	 *
	 * @param mixed $callback
	 */
	protected function registerCallback($callback)
	{
		if(!is_callable($callback)) {
			throw new Exception('Loader callback is not callable');
		}

		if(empty($this->callbacks)) {
			spl_autoload_register(array($this, 'autoloadHandler'));
		}

		$this->callbacks[] = $callback;
	}
}