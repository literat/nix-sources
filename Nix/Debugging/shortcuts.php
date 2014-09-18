<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

if(!function_exists('dump')) {
	/**
	 * Wrapper for Debugger::dump()
	 *
	 * @see Debugger::dump();
	 */
	function dump($var)
	{
		foreach (func_get_args() as $arg) {
			Nix\Debugging\Debugger::dump($arg);
		}

		return $var;
	}
}