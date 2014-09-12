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
 * PermissionAssertion
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
abstract class PermissionAssertion extends Nix\Object
{
	/** @var Resource */
	protected $resource;

	/**
	 * Sets resource
	 * 
	 * @param Resource $resource
	 * @return PermissionAssertion
	 */
	public function setResource(Resource $resource)
	{
		$this->resource = $resource;

		return $this;
	}

	/**
	 * Dynamic assertion
	 * 
	 * @param Permission $acl
	 * @param string $resource
	 * @param string $action
	 * @return bool
	 */
	abstract public function assert(Permission $acl, $resource, $action);
}