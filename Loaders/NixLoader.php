<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Loaders;

use Nix,
	Nix\Application\Application;

/**
 * NixLoader
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Loaders
 */
class NixLoader extends AutoLoader
{
	/** @var array - Available framework classes */
	protected $classes = array(
		# application
		'application'   => 'Nix/Application/Application.php',
		'router'        => 'Nix/Routers/Router.php',
		'apptemplate'   => 'Nix/Templating/AppTemplate.php',
		'controller'    => 'App/Controllers/Controller.php',
		# libs
		'cache'         => 'Nix/Caching/Cache.php',
		'configurator'  => 'Nix/Config/Configurator.php',
		'control'       => 'Nix/Application/Control.php',
		'cookie'        => 'Nix/cookie.php',
		'datagrid'      => 'Nix/data-grid.php',
		'debugger'      => 'Nix/Debugging/Debugger.php',
		'object'        => 'Nix/common/Object.php',
		'form'          => 'Nix/form.php',
		'html'          => 'Nix/html.php',
		'http'          => 'Nix/Http/Http.php',
		'l10n'          => 'Nix/l10n.php',
		'paginator'     => 'Nix/Utils/Paginator.php',
		'session'       => 'Nix/Sessions/Session.php',
		'tools'         => 'Nix/Utils/Tools.php',
		# templates
		'itemplate'     => 'Nix/Templating/ITemplate.php',
		'template'      => 'Nix/Templating/Template.php',
		'filterhelper'  => 'Nix/Templating/Helpers/FilterHelper.php',
		'htmlhelper'    => 'Nix/Templating/Helpers/HtmlHelper.php',
		'jshelper'      => 'Nix/Templating/Helpers/JsHelper.php',
		'rsshelper'     => 'Nix/Templating/Helpers/RssHelper.php',
		# user
		'iidentity'     => 'Nix/iidentity.php',
		'identity'      => 'Nix/identity.php',
		'permission'    => 'Nix/permission.php',
		'resource'      => 'Nix/permission.php',
		'permissionassertion'    => 'Nix/permission.php',
		'user'          => 'Nix/user.php',
		'iuserhandler'  => 'Nix/user.php',
		# database
		'db'            => 'Nix/db.php',
		'dbstructure'   => 'Nix/db-structure.php',
		'dbtable'       => 'Nix/db-table.php',
		# loaders
		'robotloader'   => 'Nix/Loaders/RobotLoader.php',
	);

	/**
	 * Loads file for required class
	 *
	 * @param string $class class name
	 * @return CodeplexLoader
	 */
	public function load($class)
	{
		//list($fw, $app, $c) = explode('\\', $class);
		$class = basename($class);
		if($class !== 'Controller') {
			$class = strtolower($class);
		}

		if(strpos($class, 'controller') !== false) {
			return Application::get()->loadControllerClass($class);
		}

		if(isset($this->classes[$class])) {
			require_once dirname(__FILE__) . '/../../' . $this->classes[$class];
		}
	}

	/**
	 * Registers loader
	 *
	 * @param void
	 * @return AutoLoader
	 */
	public function register()
	{
		parent::registerCallback(array($this, 'load'));
		return $this;
	}
}