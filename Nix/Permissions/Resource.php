<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Permissions;

use Nix;

/**
 * Resource
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
abstract class Resource extends Nix\Object
{
	/** @var string - Resource name */
	protected $name;

	/**
	 * Constructor
	 * 
	 * @throws Exception
	 * @return Resource
	 */
	public function __construct()
	{
		if(empty($this->name)) {
			throw new Exception('Rousource name must be defined.');
		}
	}

	/**
	 * toString interface
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}
}