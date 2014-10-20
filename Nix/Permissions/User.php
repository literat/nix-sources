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
	Nix\Permissions\IUserHandler,
	Nix\Sessions\Session;

/**
 * User
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Permissions
 */
class User extends Nix\Object
{
	/** Error code states */
	const INVALID_CREDENTIALS = false;
	const INVALID_USERNAME = 1;
	const INVALID_PASSWORD = 2;
	const UNAUTHORIZED_USER = 3;

	/** @var SessionNamespace of session */
	protected $session;

	/** @var IUserHandler user hadler */
	protected $userHandler;

	/** @var Permission access control list */
	protected $acl;

	/**
	 * Constructor
	 * 
	 * @param string|IUserHandler $userHandler
	 * @param Permission $acl
	 * @return User
	 */
	public function __construct($userHandler = null, Permission $acl = null)
	{
		if(!empty($userHandler)) {
			$this->setUserHandler($userHandler);
		}

		if(!empty($acl)) {
			$this->setAcl($acl);
		}

		$this->session = Session::getNamespace('auth.user');
		if(!$this->session->exists('authenticated')) {
			$this->session->authenticated = false;
			$this->session->id = null;
			$this->session->roles = array('guest');
			$this->session->data = (object) array();
		}
	}

	/**
	 * Returns true if user has rights for $action on $resource
	 * 
	 * @param string $res resource name
	 * @param string $action action name
	 * @throws LogicException
	 * @return bool
	 */
	public function isAllowed($res, $action = '*')
	{
		if(!isset($this->acl)) {
			throw new \LogicException('Permission class is not set in User.');
		}

		return $this->acl->isAllowed($this->getRoles(), $res, $action);
	}

	/**
	 * Returns true if user is authenticated
	 * 
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return $this->session->authenticated;
	}

	/**
	 * Sets user handler for authentication
	 * 
	 * @param mixed $handler user handler class name
	 * @return User
	 */
	public function setUserHandler($handler)
	{
		$this->userHandler = $handler;
		return $this;
	}

	/**
	 * Sets Permission object
	 * 
	 * @param Permission $acl
	 * @return User
	 */
	public function setAcl(Permission $acl)
	{
		$this->acl = $acl;
		return $this;
	}

	/**
	 * Returns Permisison object
	 * 
	 * @return Permission
	 */
	public function getAcl()
	{
		return $this->acl;
	}

	/**
	 * Authenticates by provided credentials
	 * 
	 * @param string $username
	 * @param string $password
	 * @param array $extra optional data
	 * @return bool
	 */
	public function authenticate($username, $password, $extra = array())
	{
		if(!is_object($this->userHandler)) {
			$handler = new $this->userHandler;
		}

		if(!($handler instanceof IUserHandler)) {
			throw new \LogicException('User handler have to implements interface IUserHandler.');
		}

		$args = func_get_args();
		$result = call_user_func_array(array($handler, 'authenticate'), $args);
		return $this->processIdentity($result);
	}

	/**
	 * Updates user indentity
	 * 
	 * @throws Exception
	 * @return bool
	 */
	public function updateIndentity()
	{
		if(empty($this->session->id)) {
			throw new \Exception('You can not update identity when user is not authenticated.');
		}

		$handler = new $this->userHandler;
		$result = $handler->updateIdentity($this->session->id);
		return $this->processIdentity($result);
	}

	/**
	 * Sets user authentication expiration time
	 * 
	 * @param int|string $time time expression
	 * @return User
	 */
	public function setExpiration($time)
	{
		$this->session->setExpiration($time);
		return $this;
	}

	/**
	 * Sings out logged user
	 * 
	 * @return User
	 */
	public function signOut()
	{
		if($this->isAuthenticated()) {
			$this->session->authenticated = false;
			$this->session->id = null;
			$this->session->roles = array('guest');
			$this->session->data = (object) array();
		}

		return $this;
	}

	/**
	 * Returns user roles
	 * 
	 * @return array
	 */
	public function getRoles()
	{
		return $this->session->roles;
	}

	/**
	 * Returns true if user is in role $name
	 * 
	 * @param string $name role name
	 * @return bool
	 */
	public function isInRole($name)
	{
		return in_array($name, $this->getRoles());
	}

	/**
	 * Setter
	 * 
	 * @param string $key property name
	 * @param mixed $val property value
	 * @return void
	 */
	public function __set($key, $val)
	{
		if($key == 'id' || $key == 'roles') {
			$this->session->$key = $val;
		} else {
			$this->session->data->$key = $val;
		}
	}

	/**
	 * Getter
	 * 
	 * @param string $key property name
	 * @return mixed
	 */
	public function __get($key)
	{
		if($key == 'id' || $key == 'roles') {
			return $this->session->$key;
		} elseif(isset($this->session->data->$key)) {
			return $this->session->data->$key;
		} else {
			return null;
		}
	}

	/**
	 * Isseter
	 * 
	 * @param string $key property name
	 * @return bool
	 */
	public function __isset($key)
	{
		if($key == 'id' || $key == 'roles') {
			return true;
		} else {
			return isset($this->session->data);
		}
	}

	/**
	 * Proccesses user indentity
	 * 
	 * @param mixed $identity user identity (User::INVALID_* or IIdentity)
	 * @throws Exception
	 * @return bool
	 */
	protected function processIdentity($identity)
	{
		if($identity === User::INVALID_CREDENTIALS
		 || $identity === User::INVALID_USERNAME
		 || $identity === User::INVALID_PASSWORD
		 || $identity === User::UNAUTHORIZED_USER
		) {
			return $identity;
		} elseif(!($identity instanceof IIdentity)) {
			throw new \Exception('Result of UserHandler::authenticate() must implements IIdentity.');
		}

		$this->session->authenticated = true;
		$this->session->id = $identity->getId();
		$this->session->roles = $identity->getRoles();
		$this->session->data = (object) $identity->getData();

		return true;
	}
}