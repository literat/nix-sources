<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix;

use Nix;

/**
 * Object
 *
 * Nix\Object is the ultimate ancestor of all instantiable classes.
 *
 * @author Tomas Litera <tomaslitera@hotmail.com>
 * @package     Nix
 */ 
abstract class Object
{
	/** @var array */
	private static $extendMethods = array();

	/**
	 * Extends class by $method
	 *
	 * @param string $method
	 * @param string $function callback
	 */
	public static function extendMethod($method, $function)
	{
		if(strpos($method, '::') !== false) {
			list($class, $method) = explode('::', $method);
		} else {
			$class = get_called_class();
		}

		self::$extendMethods[strtolower($class)][strtolower($method)] = $function;
	}

	/**
	 * Returns class name
	 *
	 * @return string
	 */
	public function getClass()
	{
		return get_class($this);
	}

	/**
	 * Returns class ancestors
	 *
	 * @return array
	 */
	public function getAncestors()
	{
		$class = $this;
		$classes = array($this);
		while($class = get_parent_class($class))
			$classes[] = $class;

		return $classes;
	}

	/**
	 * Magic getter method
	 *
	 * @throws OutOfBoundsException
	 * @return mixed
	 */
	public function __get($key)
	{
		if(substr($key, 0, 2) == 'is' && method_exists($this, $key)) {
			return $this->{$key}();
		} elseif(method_exists($this, "get$key")) {
			return $this->{"get$key"}();
		} else {
			throw new \OutOfBoundsException("Undefined variable " . $this->getClass() . "::$$key.");
		}
	}

	/**
	 * Magic setter method
	 *
	 * @throws OutOfBoundsException
	 * @return mixed
	 */
	public function __set($key, $value)
	{
		if(method_exists($this, "set$key")) {
			return $this->{"set$key"}($value);
		} else {
			if(method_exists($this, "get$key")) {
				throw new \OutOfBoundsException("Variable " . $this->getClass() . "::$$key is read-only.");
			} else {
				throw new \OutOfBoundsException("Undefined variable " . $this->getClass() . "::$$key.");
			}
		}
	}

	/**
	 * Interface __call()
	 *
	 * @param mixed $method method name
	 * @param mixed $args
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if(empty($method)) {
			throw new \Exception("Method name can not be empty.");
		}

		$method = strtolower($method);
		$classes = $this->getAncestors($this);

		foreach($classes as $class) {
			$class = strtolower(get_class($class));

			if(isset(self::$extendMethods[$class][$method])) {
				array_unshift($args, $this);
				
				return call_user_func_array(self::$extendMethods[$class][$method], $args);
			}
		}

		throw new \BadMethodCallException('Undefined method ' . get_class($this) . "::$method().");
	}
}