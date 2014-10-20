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
	Nix\Object;

/**
 * SessionNamespace
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Sessions
 */
class SessionNamespace extends Object
{
	/** @var string namespace */
	protected $namespace;

	/**
	 * Constructor
	 *
	 * @param string $namespace namepsace name
	 * @return SessionNamespace
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
		$this->deleteExpired();
	}

	/**
	 * Clears and deletes variables which are expired
	 *
	 * @return SessionNamespace
	 */
	protected function deleteExpired()
	{
		if(!isset($_SESSION['__M'][$this->namespace])) {
			return;
		}

		# expired namespace
		$namespace = $_SESSION['__M'][$this->namespace];
		if(isset($namespace['__E'])) {
			if($namespace['__E'] <= time()) {
				unset($_SESSION[$this->namespace]['__D']);
				unset($_SESSION[$this->namespace]['__M']);
			}
		}

		if(!isset($namespace['__V'])) {
			return;
		}

		# expired variables
		foreach($namespace['__V'] as $var => $time) {
			if($time <= time()) {
				unset($_SESSION['__D'][$this->namespace][$var]);
				unset($_SESSION['__M'][$this->namespace]['__V'][$var]);
			}
		}

		if(empty($_SESSION['__M'][$this->namespace]['__V'])) {
			unset($_SESSION['__M'][$this->namespace]);
		}
	}

	/**
	 * Reads variable
	 *
	 * @param string $name variable name
	 * @param mixed $default default value (when variable is not set)
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		if(isset($_SESSION['__D'][$this->namespace][$name])) {
			return $_SESSION['__D'][$this->namespace][$name];
		} else {
			return $default;
		}
	}

	/**
	 * Writes variable value
	 *
	 * @param string $name variable name
	 * @param mixed $value variable value
	 * @param int|string $expiration expiration expression, null = no expiration
	 * @return SessionNamespace
	 */
	public function set($name, $value, $expiration = null)
	{
		if(is_string($expiration)) {
			$expiration = strtotime($expiration);
		}

		$_SESSION['__D'][$this->namespace][$name] = $value;

		if($expiration != null) {
			$_SESSION['__M'][$this->namespace]['__V'][$name] = $expiration;
		}

		return $this;
	}

	/**
	 * Checks if variable exists
	 *
	 * @param string $name variable name
	 * @return bool
	 */
	public function exists($name)
	{
		return isset($_SESSION['__D'][$this->namespace][$name]);
	}

	/**
	 * Deletes variable
	 *
	 * @param string $name variable name
	 * @return SessionNamespace
	 */
	public function delete($name)
	{
		unset($_SESSION['__D'][$this->namespace][$name]);
		unset($_SESSION['__M'][$this->namespace]['__V'][$name]);

		return $this;
	}

	/**
	 * Sets namespace expiration time
	 *
	 * @param int|string $time time expression
	 * @return SessionNamespace
	 */
	public function setExpiration($time)
	{
		if($time === 0) {
			unset($_SESSION['__M'][$this->namespace]['__E']);
		} else {
			if(is_string($time)) {
				$time = strtotime($time);
			}

			$_SESSION['__M'][$this->namespace]['__E'] = $time;
		}

		return $this;
	}

	/**
	 * Setter
	 *
	 * @param string $key key name
	 * @param mixed $val value
	 * @return void
	 */
	public function __set($key, $val)
	{
		$this->set($key, $val);
	}

	/**
	 * Getter
	 *
	 * @param string $key key name
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Isseter
	 *
	 * @param string $key key name
	 * @return bool
	 */
	public function __isset($key)
	{
		return $this->exists($key);
	}

	/**
	 * Unsetter
	 *
	 * @param string $key key name
	 * @return void
	 */
	public function __unset($key)
	{
		$this->delete($key);
	}
}