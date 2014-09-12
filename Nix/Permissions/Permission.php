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
	Nix\Permissions\PermissionRole;

/**
 * Permission
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
class Permission extends Nix\Object
{
	/** @var array */
	private $roles = array();

	/** @var array */
	private $resources = array(
		'*' => array(),
	);

	/**
	 * Constructor
	 * 
	 * @return Permission
	 */
	public function __construct()
	{
		$this->addRole('guest');
	}

	/**
	 * Adds role
	 * 
	 * @param string $name role name
	 * @param array $parents role parents
	 * @return Permission
	 */
	public function addRole($name, $parents = array())
	{
		$this->roles[$name] = new PermissionRole($this, $name, (array) $parents);

		return $this;
	}

	/**
	 * Adds resource
	 * 
	 * @param string $name resource name
	 * @return Permission
	 */
	public function addResource($name)
	{
		$this->resources[$name] = true;

		return $this;
	}

	/**
	 * Checks whether role is in resource and action allowed
	 * 
	 * @param array|string $role roles
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isAllowed($role, $res, $action = null)
	{
		if(empty($action)) {
			$action = '*';
		}
		$resName = (string) $res;

		foreach((array) $role as $val) {
			if($this->roles[$val]->isDefined($resName, $action)) {
				return $this->roles[$val]->isAllowed($res, $action);
			} elseif($this->roles[$val]->hasParents()) {
				return $this->isAllowed($this->roles[$val]->getParents(), $res, $action);
			}
		}

		return false;
	}

	/**
	 * Allows roles in resources in actions
	 * 
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions allowed actions
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	public function allow($roles, $resources = null, $actions = null, PermissionAssertion $pAssertion = null)
	{
		return $this->setAccess(true, $roles, $resources, $actions, $pAssertion);
	}

	/**
	 * Denies roles in resources in actions
	 * 
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions denied actions
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	public function deny($roles, $resources = null, $actions = null, PermissionAssertion $pAssertion = null)
	{
		return $this->setAccess(false, $roles, $resources, $actions, $pAssertion);
	}

	/**
	 * Sets access for roles in resources in actions
	 * 
	 * @param bool $access
	 * @param array|string $roles
	 * @param array|string $resources
	 * @param array|string $actions
	 * @param PermissionAssertion $pAssertion
	 * @return Permission
	 */
	protected function setAccess($access, $roles, $resources, $actions, $pAssertion)
	{
		if(empty($actions)) {
			$actions = '*';
		}

		if($resources == '*' || empty($resources)) {
			$resources = array_keys($this->resources);
		}

		foreach((array) $roles as $role) {
			if(!isset($this->roles[$role])) {
				throw new Exception("Undefined role '$role'.");
			}

			foreach((array) $resources as $resource) {
				if(!isset($this->resources[$resource])) {
					throw new Exception("Undefined resource '$resource'.");
				}

				foreach((array) $actions as $action) {
					$this->roles[$role]->setAccess($access, $resource, $action, $pAssertion);
				}
			}
		}

		return $this;
	}
}