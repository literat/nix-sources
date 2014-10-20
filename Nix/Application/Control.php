<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Application;

use Nix,
	Nix\Object,
	Nix\Sessions\Session;

/**
 * Control
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Application
 */
class Control extends Object
{

	/** @var SessionNamespace flash session */
	private $flashSession;

	/**
	 * Contructor
	 *
	 * @return Control
	 */
	public function __construct()
	{
		if(empty($this->flashSession)) {
			$this->flashSession = Session::getNamespace('flash-messages.' . $this->getClass());
		}
	}

	/**
	 * Adds flash message and returns message class
	 *
	 * @param string $message
	 * @param string $type
	 * @return stdClass
	 */
	public function addFlash($message, $type = 'info')
	{
		$messages = $this->flashSession->messages;
		$messages[] = $flash = (object) array(
			'message' => $message,
			'type' => $type,
		);

		$this->flashSession->set('messages', $messages, time() + 3);
		return $flash;
	}

	/**
	 * Returns flash messages
	 *
	 * @param void
	 * @return array
	 */
	public function getFlashes()
	{
		$messages = $this->flashSession->messages;
		if(empty($messages)) {
			return array();
		} else {
			return $messages;
		}
	}

	/**
	 * Returns template instance
	 *
	 * @param void
	 * @return ITemplate
	 */
	protected function getTemplateInstace()
	{
		$template = new Template();
		$template->flashes = $this->getFlashes();

		return $template;
	}
}