<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Templating;

use Nix,
	Nix\Templating\Template,
	Nix\Application\Application,
	Nix\Caching\Cache,
	Nix\Http\Http;

/**
 * AppTemplate
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Templating
 */
class AppTemplate extends Template
{
	/** Application */
	protected $application;

	/**
	 * Constrctor
	 *
	 * @param string $file template file
	 * @param Cache $cache
	 * @return AppTemplate
	 */
	public function __construct($file = null, Cache $cache = null)
	{
		parent::__construct($file, $cache);
		$this->tplFunctions['link'] = '$controller->url';
		$this->tplTriggers['extends'] = array($this, 'cbExtendsTrigger');

		$this->application = Application::get();
		$this->getHelper('html');
		$this->getHelper('filter');
		$this->setVar('base', Http::$baseURL);
	}

	/**
	 * Includes templatefile
	 *
	 * @param   string    filename
	 * @return  string
	 */
	public function subTemplate($file)
	{
		$file = $this->application->path . "/templates/$file."
		      . $this->application->controller->routing->ext;
		return parent::subTemplate($file);
	}

	/**
	 * Callback for extending template
	 *
	 * @param string $expression
	 * @return string
	 */
	protected function cbExtendsTrigger($expression)
	{
		$expression = $this->application->path . '/templates/'
			. substr($expression, 1, -1) . '.'
			. $this->application->controller->routing->ext;
		return parent::cbExtendsTrigger("'$expression'");
	}
}