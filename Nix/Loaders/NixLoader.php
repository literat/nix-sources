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
		'control'       => 'Nix/Application/Control.php',
		# loaders
		'robotloader'   => 'Nix/Loaders/RobotLoader.php',
		# libs
		'cache'         => 'Nix/Caching/Cache.php',
		'configurator'  => 'Nix/Config/Configurator.php',
		'cookie'        => 'Nix/Sessions/Cookie.php',
		'datagrid'      => 'Nix/data-grid.php',
		'debugger'      => 'Nix/Debugging/Debugger.php',
		'object'        => 'Nix/common/Object.php',
		'l10n'          => 'Nix/l10n.php',
		'session'       => 'Nix/Sessions/Session.php',
		#utils
		'tools'         => 'Nix/Utils/Tools.php',
		'paginator'     => 'Nix/Utils/Paginator.php',
		#http
		'http'          => 'Nix/Http/Http.php',
		'request'       => 'Nix/Http/Request.php',
		'response'      => 'Nix/Http/Response.php',
		# templating
		'itemplate'     => 'Nix/Templating/ITemplate.php',
		'template'      => 'Nix/Templating/Template.php',
		'filterhelper'  => 'Nix/Templating/Helpers/FilterHelper.php',
		'htmlhelper'    => 'Nix/Templating/Helpers/HtmlHelper.php',
		'jshelper'      => 'Nix/Templating/Helpers/JsHelper.php',
		'rsshelper'     => 'Nix/Templating/Helpers/RssHelper.php',
		# permissions
		'iidentity'			  => 'Nix/Permissions/IIdentity.php',
		'identity'			  => 'Nix/Permissions/Identity.php',
		'permission'		  => 'Nix/Permissions/Permission.php',
		'resource'			  => 'Nix/Permissions/Resource.php',
		'permissionrole'      => 'Nix/Permissions/PermissionRole.php',
		'permissionassertion' => 'Nix/Permissions/PermissionAssertion.php',
		'user'				  => 'Nix/Permissions/User.php',
		'iuserhandler'		  => 'Nix/Permissions/IUserHandler.php',
		# database
		'db'            	=> 'Nix/Database/Db.php',
		'structure'			=> 'Nix/Database/Structure.php',
		'table'      		=> 'Nix/Database/Table.php',
		'connection'		=> 'Nix/Database/Connection.php',
		'result'			=> 'Nix/Database/Result.php',
		'resultnode'		=> 'Nix/Database/ResultNode.php',
		'preparedresult'	=> 'Nix/Database/PreparedResult.php',
		## drivers
		'idriver'		=> 'Nix/Database/IDriver.php',
		'mysqlidriver'	=> 'Nix/Database/Drivers/MysqliDriver.php',
		# forms
		'form'          => 'Nix/Forms/Form.php',
		'html'          => 'Nix/Forms/Html.php',
		'condition'		=> 'Nix/Forms/Condition.php',
		'rule'			=> 'Nix/Forms/Rule.php',
		## controls
		'formcontrol'		=> 'Nix/Forms/Controls/FormControl.php',
		'text'				=> 'Nix/Forms/Controls/Text.php',
		'input'				=> 'Nix/Forms/Controls/Input.php',
		'hidden'			=> 'Nix/Forms/Controls/Hidden.php',
		'datepicker'		=> 'Nix/Forms/Controls/Datepicker.php',
		'password'			=> 'Nix/Forms/Controls/Password.php',
		'textarea'			=> 'Nix/Forms/Controls/Textarea.php',
		'radio'				=> 'Nix/Forms/Controls/Radio.php',
		'select'			=> 'Nix/Forms/Controls/Select.php',
		'checkbox'			=> 'Nix/Forms/Controls/Checkbox.php',
		'button'			=> 'Nix/Forms/Controls/Button.php',
		'submit'			=> 'Nix/Forms/Controls/Submit.php',
		'reset'				=> 'Nix/Forms/Controls/Reset.php',
		'multicheckbox'		=> 'Nix/Forms/Controls/MultiCheckbox.php',
		'multipleselect'	=> 'Nix/Forms/Controls/MultipleSelect.php',
		'datepicker'		=> 'Nix/Forms/Controls/Datepicker.php',
		'file'				=> 'Nix/Forms/Controls/File.php',
		'uploadedfile'		=> 'Nix/Forms/Controls/UploadedFile.php',
		## renderers
		'iformrenderer'		=> 'Nix/Forms/Renderers/IFormRenderer.php',
		'formrenderer'		=> 'Nix/Forms/Renderers/FormRenderer.php',
		'emptyrenderer'		=> 'Nix/Forms/Renderers/EmptyRenderer.php',
		'dlrenderer'		=> 'Nix/Forms/Renderers/DlRenderer.php',
		'divrenderer'		=> 'Nix/Forms/Renderers/DivRenderer.php',
		'tablerenderer'		=> 'Nix/Forms/Renderers/TableRenderer.php',
		### jsvalidators
		'ijsvalidator'		=> 'Nix/Forms/Renderers/JsValidators/IJsValidator.php'
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