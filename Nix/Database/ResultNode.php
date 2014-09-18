<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Database;

/**
 * Result Node class
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
class ResultNode implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @return DbResultNode
	 */
	public function __construct($data)
	{
		foreach((array) $data as $key => $val) {
			$this->$key = $val;
		}
	}

	/**
	 * Magic method
	 * 
	 * @throws Exception
	 */
	public function __get($name)
	{
		throw new \Exception("Undefined resultset field '$name'.");
	}

	/**
	 * Array-access interface
	 */
	public function offsetSet($key, $value)
	{
		$this->$key = $value;
	}

	/**
	 * Array-access interface
	 * 
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		if(!property_exists($this, $key)) {
			throw new \Exception("Undefined key '$key'.");
		}

		return $this->$key;
	}

	/**
	 * Array-access interface
	 */
	public function offsetUnset($key)
	{
		unset($this->$key);
	}

	/**
	 * Array-access interface
	 * 
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->$key);
	}

	/**
	 * IteratorAggregate interface
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator((array) get_object_vars($this));
	}
}