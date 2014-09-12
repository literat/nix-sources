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
 * IUserHandler
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
interface IUserHandler
{
	/**
	 * Returns user indentity or User::* constants
	 * 
	 * @param string $username
	 * @param string $password
	 * @return IIdentity|User::***
	 */
	public function authenticate($username, $password);

	/**
	 * Returns updated users identity
	 * 
	 * @param mixed $id user primary key
	 * @return IIdentity
	 */
	public function updateIdentity($id);
}