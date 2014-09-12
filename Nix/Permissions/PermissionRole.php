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
 * PermissionRole
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
class PermissionRole extends Nix\Object
{
	/** @var Permission */
	private $permission;

	/** @var string - Role name*/
	private $name;

	/** @var array */
	private $resources = array();

	/** @var array */
	private $parents = array();

	/**
	 * Constructor
	 * 
	 * @param Permission $permission
	 * @param string $name role name
	 * @param array $parents
	 * @return PermissionRole
	 */
	public function __construct(Permission $permission, $name, $parents)
	{
		$this->name = $name;
		$this->parents = (array) $parents;
		$this->permission = $permission;
	}

	/**
	 * Checks whether action is allowed on resource
	 * 
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isAllowed($res, $action)
	{
		$resName = (string) $res;
		if($action == '*') {
			foreach(array_keys($this->resources[$resName]) as $act) {
				$result = $this->isAllowedWithAssertion($res, $act);
				if($result) {
					return true;
				}
			}
		} else {
			if(isset($this->resources[$resName][$action])) {
				return $this->isAllowedWithAssertion($res, $action);
			} elseif(isset($this->resources[$resName]['*'])) {
				return $this->isAllowedWithAssertion($res, '*');
			} elseif(isset($this->resources['*'][$action])) {
				return $this->resources['*'][$action][0];
			} elseif(isset($this->resources['*']['*'])) {
				return $this->resources['*']['*'][0];
			}
		}

		return false;
	}

	/**
	 * Checks if is access allowed with dynamic permission
	 * 
	 * @param string|Resource $res
	 * @param string $action
	 * @return bool
	 */
	protected function isAllowedWithAssertion($res, $action)
	{
		$resName = (string) $res;
		$result = $this->resources[$resName][$action][0];

		if(!$result) {
			return false;
		}

		if(empty($this->resources[$resName][$action][1])) {
			return $result;
		}

		$pAssertion = $this->resources[$resName][$action][1];

		return $pAssertion->setResource($res)->assert($this->permission, $resName, $action);
	}

	/**
	 * Sets access for action on role
	 * 
	 * @param bool $access
	 * @param string $res resource name
	 * @param string $action action name
	 * @param PermissionAssertion $pAssertion
	 * @return PermissionRole
	 */
	public function setAccess($access, $res, $action, $pAssertion)
	{
		$this->resources[$res][$action][0] = $access;
		$this->resources[$res][$action][1] = $pAssertion;

		return $this;
	}

	/**
	 * Returns parents
	 * 
	 * @return array
	 */
	public function getParents()
	{
		return $this->parents;
	}

	/**
	 * Returns true if role has any parents
	 * 
	 * @return bool
	 */
	public function hasParents()
	{
		return count($this->parents) > 0;
	}

	/**
	 * Checks whether is set resource and action
	 * 
	 * @param string $res resource name
	 * @param string $action action name
	 * @return bool
	 */
	public function isDefined($res, $action)
	{
		if($action == '*') {
			return isset($this->resources[$res]);
		} else {
			return (isset($this->resources[$res][$action]) || isset($this->resources[$res]['*']))
			    || (isset($this->resources['*'][$action]) || isset($this->resources['*']['*']));
		}
	}
}