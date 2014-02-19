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

use Nix;

require_once dirname(__FILE__) . '/AutoLoader.php';

/**
 * NixLoader
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Codeplex
 * @subpackage  Loaders
 */
class NixLoader extends AutoLoader
{
	/** @var array - Available framework classes */
	protected $classes = array(
		# application
		'application'   => 'application/libs/application.php',
		'router'        => 'Nix/Routers/Router.php',
		'apptemplate'   => 'application/libs/app-template.php',
		# libs
		'cache'         => 'libs/cache.php',
		'config'        => 'libs/config.php',
		'control'       => 'libs/control.php',
		'cookie'        => 'libs/cookie.php',
		'datagrid'      => 'libs/data-grid.php',
		'debug'         => 'libs/debug.php',
		'object'        => 'libs/object.php',
		'form'          => 'libs/form.php',
		'html'          => 'libs/html.php',
		'http'          => 'libs/http.php',
		'l10n'          => 'libs/l10n.php',
		'paginator'     => 'libs/paginator.php',
		'session'       => 'libs/session.php',
		'tools'         => 'libs/tools.php',
		# templates
		'itemplate'     => 'libs/itemplate.php',
		'template'      => 'libs/template.php',
		'filterhelper'  => 'libs/template/filter-helper.php',
		'htmlhelper'    => 'libs/template/html-helper.php',
		'jshelper'      => 'libs/template/js-helper.php',
		'rsshelper'     => 'libs/template/rss-helper.php',
		# user
		'iidentity'     => 'libs/iidentity.php',
		'identity'      => 'libs/identity.php',
		'permission'    => 'libs/permission.php',
		'resource'     => 'libs/permission.php',
		'permissionassertion'    => 'libs/permission.php',
		'user'          => 'libs/user.php',
		'iuserhandler'  => 'libs/user.php',
		# database
		'db'            => 'libs/db.php',
		'dbstructure'   => 'libs/db-structure.php',
		'dbtable'       => 'libs/db-table.php',
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
		$c = strtolower($class);
		if(strpos($c, 'controller') !== false) {
			return Application::get()->loadControllerClass($class);
		}

		if(isset($this->classes[$c])) {
			require_once dirname(__FILE__) . '/../../' . $this->classes[$c];
		}
	}

	/**
	 * Registers loader
	 *
	 * @return AutoLoader
	 */
	public function register()
	{
		parent::registerCallback(array($this, 'load'));
		return $this;
	}
}