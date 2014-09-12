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

/**
 * IIdentity
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
interface IIdentity
{
	/**
	 * Returns user primary key
	 * 
	 * @return mixed
	 */
	public function getId();

	/**
	 * Returns user roles
	 * 
	 * @return array
	*/
	public function getRoles();

	/**
	 * Returns user data
	 * 
	 * @return array
	 */
	public function getData();
}