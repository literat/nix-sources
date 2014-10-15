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

use Nix;
use Nix\Http\Http;

require_once __DIR__ . '/Control.php';

/**
 * @property-read Template $template
 * @property-read Application $application
 * @property-read Router $router
 * @property-read Cache $cache
 * @property-read Routing $routing
 */
abstract class Controller extends Nix\Application\Control
{
	/**
	 * Returns self instance
	 *
	 * @return  Controller
	 */
	public static function get()
	{
		return Nix\Application\Application::get()->controller;
	}

	/** @var bool - Allow include templates which are not situated in module */
	protected $templatePathReduction = true;

	/** @var array */
	protected $services = array(
		'rss' => array(
			'layout' => 'rss-layout',
			'helpers' => 'rss',
		),
		'xml' => array(
			'layout' => 'xml-layout',
		),
	);

	/** @var Application */
	private $application;

	/** @var Template */
	private $template;

	/** @var stdClass */
	private $routing;

	/**
	 * Constructor
	 *
	 * @return Controller
	 */
	public function __construct()
	{
		parent::__construct();
		$this->application = Nix\Application\Application::get();
		$this->routing = (object) $this->application->router->getRouting(false);
		$this->routing->template = '';
		$this->routing->layout = '';
		$this->routing->ajax = Nix\Http\Http::$request->isAjax;
		$this->routing->ext = 'tpl';

		$this->template = $this->getTemplateInstace();
		if (!($this->template instanceof Nix\Templating\ITemplate))
			throw new \LogicException('You have to return template class which implements ITemplate interface.');
	}

	/**
	 * Returns template class instance
	 *
	 * @return AppTemplate
	 */
	protected function getTemplateInstace()
	{
		$template = new Nix\Templating\AppTemplate(null, $this->application->cache);
		$template->flashes = $this->getFlashes();
		return $template;
	}

	/**
	 * Returns Application instance
	 *
	 * @return Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * Returns application Router
	 *
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->application->getRouter();
	}

	/**
	 * Returns applicatino Cache
	 *
	 * @return Cache
	 */
	public function getCache()
	{
		return $this->application->getCache();
	}

	/**
	 * Returns template instance
	 *
	 * @return ITemplate
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Returns Routing instance
	 *
	 * @return Routing
	 */
	public function getRouting()
	{
		return $this->routing;
	}

	/**
	 * Controller callbacks
	 */
	public function init() {}
	public function beforeAction() {}
	public function afterAction() {}
	public function prepareTemplate() {}
	/***/

	/**
	 * Jumps out of application and display error $template
	 *
	 * @param string $template error template name
	 * @param bool $debug is error page only for debug mode?
	 * @param int|null $errorCode
	 * @throws ApplicationError
	 * @return void
	 */
	public function error($template = '404', $debug = false, $errorCode = 404)
	{
		throw new ApplicationError($template, $debug, $errorCode);
	}

	/**
	 * Redirects to new $url and terminate application when $exit = true
	 *
	 * @param string $url internal relative url
	 * @param bool $exit terminate application?
	 * @return Controller
	 */
	public function redirect($url, $exit = true)
	{
		$url = $this->url($url, array(), array(), true);
		Http::$response->redirect($url, 303);
		if ($exit) exit;
		return $this;
	}

	/**
	 * Creates application internal url
	 *
	 * @param string $url
	 * @param array $args rewrite args
	 * @param array|false $params rewrite params
	 * @param bool $absolute create absolute url?
	 * @return string
	 */
	public function url($url, $args = array(), $params = false, $absolute = false)
	{
		$url = $this->getRouter()->url($url, $args, $params);
		$url = Http::$baseURL . '/' . ltrim($url, '/');

		if ($absolute)
			$url = Http::$serverURL . $url;

		return $url;
	}

	/**
	 * Runs action call
	 *
	 * @return void
	 */
	public function render()
	{
		try {
			# INITS
			call_user_func(array($this, 'init'));
			if ($this->routing->template !== false)
				$this->routing->template = Nix\Utils\Tools::dash($this->routing->action);
			if (empty($this->routing->layout))
				$this->setupLayout();

			# METHOD
			$method = Nix\Utils\Tools::camelize($this->routing->action);
			if ($this->routing->ajax && method_exists($this, $method . 'AjaxAction'))
				$method .= 'AjaxAction';
			elseif (method_exists($this, $method . 'Action'))
				$method .= 'Action';
			else
				throw new Nix\Application\ApplicationException('missing-method', $method . 'Action');

			# CALL
			call_user_func(array($this, 'beforeAction'));
			$return = call_user_func_array(array($this, $method), $this->getRouter()->getArgs());
			call_user_func(array($this, 'afterAction'));
			if($this->routing->ajax && $return !== null) {
				$this->proccessAjaxResponse($return);
			}

			$this->template->setFile($this->getTemplateFile());

		} catch (ApplicationError $exception) {
			$template = '404';
			if (Configurator::read('core.debug') > 0)
				$template = $exception->errorFile;

			$this->template->setFile($this->getErrorTemplateFile($template));
			if (empty($this->routing->layout))
				$this->setupLayout();
		}

		$this->loadLayoutTemplate();
		call_user_func(array($this, 'prepareTemplate'));

		return $this->template->render();
	}

	/**
	 * Sets error template
	 *
	 * @param Exception $exception
	 * @return Controller
	 */
	public function setErrorTemplate($template)
	{
		$this->template->setFile($this->getErrorTemplateFile($template));
		return $this;
	}

	/**
	 * Loads layout template
	 *
	 * @return Controller
	 */
	public function loadLayoutTemplate()
	{
		$this->template->setExtendsFile($this->getLayoutTemplateFile());
		return $this;
	}

	/**
	 * Proccess method result - output result fox ajax
	 *
	 * @param mixes $return method result
	 */
	protected function proccessAjaxResponse($return)
	{
		if (!is_array($return)) {
			if (is_object($return))
				$return = (array) $return;
			else
				$return = array('response' => $return);
		}

		ob_clean();
		echo json_encode($return);
		exit;
	}

	/**
	 * Returns template relative path
	 *
	 * @param array $modules
	 * @param string $controller
	 * @param string $template
	 * @param string $service
	 * @param string $ext
	 * @return string
	 */
	protected function constructTemplatePath($modules, $controller, $template, $service, $ext)
	{
		$module = null;
		if (!empty($modules))
			$module = implode('-module/', $modules) . '-module/';
		if (!empty($service))
			$service = ".$service";

		return "/templates/$module$controller/$template$service.$ext";
	}

	/**
	 * Returns error template relative path
	 *
	 * @param string $errorTemplate
	 * @param string $ext
	 * @return string
	 */
	protected function constructErrorTemplatePath($errorTemplate, $ext)
	{
		return "/templates/_errors/$errorTemplate.$ext";
	}

	/**
	 * Returns layout template relative path
	 *
	 * @param array $modules
	 * @param string $layout
	 * @param string $ext
	 */
	protected function constructLayoutTemplatePath($modules, $layout, $ext)
	{
		$module = null;
		if (!empty($modules))
			$module = implode('-module/', $modules) . '-module/';

		return "/Templates/$module$layout.$ext";
	}

	/**
	 * Returns template file path
	 *
	 * @throws ApplicationException
	 * @return string
	 */
	private function getTemplateFile()
	{
		$routing = $this->routing;
		$app = $this->application->path;
		$core = $this->application->corePath . '/application//';

		$file = $this->constructTemplatePath($routing->module, $routing->controller, $routing->template, $routing->service, $routing->ext);

		if(file_exists($app . $file)) {
			return $app . $file;
		}
		elseif (file_exists($core . $file)) {
			return $core . $file;
		}

		$file1 = $this->constructTemplatePath(array(), $routing->controller,
			$routing->template, $routing->service, $routing->ext);
		var_dump($app . $file1);
		if ($this->templatePathReduction && file_exists($app . $file1))
			return $app . $file1;

		if (file_exists($core . $file1))
			return $core . $file1;

		throw new Nix\Application\ApplicationException('missing-template', $file);
	}

	/**
	 * Returns error template file path
	 *
	 * @param string $errorTemplate
	 * @throws RuntimeException
	 * @return string
	 */
	private function getErrorTemplateFile($errorTemplate)
	{
		$app = $this->application->path;
		$core = $this->application->corePath . '/application';

		$file = $this->constructErrorTemplatePath($errorTemplate, $this->routing->ext);
		if (file_exists($app . $file))
			return $app . $file;
		elseif (file_exists($core . $file))
			return $core . $file;

		throw new RuntimeException("Missing error template '$file'.");
	}

	/**
	 * Returns layout template file path
	 *
	 * @return string
	 */
	private function getLayoutTemplateFile()
	{
		$routing = $this->routing;
		$app = $this->application->path;
		$core = $this->application->corePath . '/../app';

		$file = $this->constructLayoutTemplatePath($routing->module, $routing->layout, $routing->ext);
		if (file_exists($app . $file))
			return $app . $file;
		elseif (file_exists($core . $file))
			return $core . $file;

		if ($this->templatePathReduction) {
			$file1 = $this->constructLayoutTemplatePath(array(), $routing->layout, $routing->ext);
			if (file_exists($app . $file1))
				return $app . $file1;
			elseif (file_exists($core . $file1))
				return $core . $file1;
		}

		return $core . '/templates/layout.tpl';
	}

	/**
	 * Setups layout for services
	 */
	public function setupLayout()
	{
		$layout = 'layout';
		if (isset($this->services[$this->routing->service])) {
			$service = $this->services[$this->routing->service];
			if (isset($service['layout']))
				$layout = $service['layout'];
			
			if (isset($service['helpers'])) {
				foreach ((array) $service['helpers'] as $helper)
					$this->template->getHelper($helper);
			}
		}

		$this->routing->layout = $layout;
	}
}