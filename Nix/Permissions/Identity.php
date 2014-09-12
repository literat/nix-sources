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

use Nix,
	Nix\Permissions\IIdentity;

/**
 * Identity
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
class Identity extends Nix\Object implements IIdentity
{
	/** @var mixed - User primary key */
	protected $id;

	/** @var array */
	protected $roles = array();

	/** @var array - User data */
	protected $data = array();

	/**
	 * Constructor
	 * 
	 * @param mixed user primary key
	 * @param array|string user roles
	 * @param array optional user data
	 * @return Indentity
	 */
	public function __construct($id, $roles = array('guest'), $data = array())
	{
		$this->id = $id;
		$this->roles = (array) $roles;
		$this->data = (array) $data;
	}

	/**
	 * Returns user primary key
	 * 
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns user roles
	 * 
	 * @return  array
	*/
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * Returns user data
	 * 
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}