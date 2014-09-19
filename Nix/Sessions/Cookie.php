<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Sessions;

use Nix,
	Nix\Sessions\SessionNamespace,
	Nix\Config\Configurator;

/**
 * Cookie
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Sessions
 */
class Cookie
{
	/**
	 * Contructor
	 */
	private function __contruct()
	{
	}

	/**
	 * Gets cookie variable
	 * 
	 * @param string $var var name
	 * @param mixed $default default value
	 * @return mixed
	 */
	public static function get($var, $default = null)
	{
		if(isset($_COOKIE[$var])) {
			return $_COOKIE[$var];
		}

		return $default;
	}

	/**
	 * Sets cooke variable
	 * 
	 * @param string $var var name
	 * @param mixed $val
	 * @param string $path
	 * @param string $domain
	 * @param int $expires
	 */
	public static function set($var, $val, $path = null, $domain = null, $expires = null)
	{
		if($expires === null) {
			$expires = self::$defaultExpires;
			if(class_exists('Config', false)) {
				$expires = Config::read('cookie.lite-time', $expires);
			}

			$expires += time();
		}

		setcookie($var, $val, $expires, $path, $domain);
	}

	/**
	 * Check if variable exists
	 * 
	 * @param string $var var name
	 * @return bool
	 */
	public static function exists($var)
	{
		return isset($_COOKIE[$var]);
	}

	/**
	 * Deletes cookie variable
	 * 
	 * @param string $var var name
	 * @param string $path
	 * @param string $domain
	 */
	public static function delete($var, $path = null, $domain = null)
	{
		setcookie($var, false, time() - 60000, $path, $domain);
	}
}