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
	Nix\Obejct,
	Nix\Config,
	Nix\Debugging,
	Nix\Router,
	Nix\Caching,
	Nix\Utils;

require_once dirname(__FILE__) . '/../Utils/Tools.php';
require_once dirname(__FILE__) . '/../common/Object.php';
require_once dirname(__FILE__) . '/../Http/Http.php';
require_once dirname(__FILE__) . '/../Debugging/Debugger.php';
require_once dirname(__FILE__) . '/../Caching/Cache.php';
require_once dirname(__FILE__) . '/../Config/Configurator.php';
require_once dirname(__FILE__) . '/../Routers/Router.php';

/**
 * Application
 *
 * Drives web application
 *
 * @property-read Router $router
 * @property-read Controller $controller
 * @property-read Cache $cache
 * @property-read string $path
 * @property-read string $corePath
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Application
 */
class Application extends Object
{
	/** @var bool Error mode */
	public static $error = false;

	/** @var Application */
	private static $self;

	/**
	 * Returns instance of Application
	 *
	 * @param void
	 * @return  Application
	 */
	public static function get()
	{
		if(empty(self::$self)) {
			throw new Exception ('Application hasn\'t been alerady created.');
		}

		return self::$self;
	}

	/**
	 * Returns framework version and logo
	 *
	 * @param bool $image image version?
	 * @return string
	 */
	public static function getFrameworkInfo($image = true)
	{

	}

	/** @var string - Error controller name */
	public $errorController = 'AppController';

	/** @var string */
	private $path;

	/** @var string */
	private $corePath;

	/** @var Router */
	private $router;

	/** @var Cache */
	private $cache;

	/** @var CustomController */
	private $controller;

	/**
	 * Constructor
	 *
	 * @param string $path application path
	 * @param string|false $config configuration file|don't load default
	 * @return Application
	 */
	public function __construct($path = '/app', $config = '/config.yml')
	{
		if(!empty(self::$self)) {
			throw new Exception('You can not create more then 1 instance of Application class.');
		}

		self::$self = & $this;
		header('X-Powered-By: Nix Framework');
		$this->path = rtrim(dirname($_SERVER['SCRIPT_FILENAME']) . $path, '/');
		$this->corePath = rtrim(dirname(dirname(dirname(__FILE__))), '/');

		if($config !== false) {
			Nix\Config\Config::multiWrite(Nix\Config\Configurator::parseFile($this->path . $config));
		}

		if(Nix\Config\Configurator::read('cache.storage.relative', true)) {
			$cachePath = $this->path . Nix\Config\Configurator::read('cache.storage.path', '/temp/');
		} else {
			$cachePath = Nix\Config\Configurator::read('cache.storage.path');
		}

		$this->router = new Nix\Routers\Router();
		$this->cache = new Nix\Caching\Cache(true, $cachePath);
		$this->initConfig();
	}

	/**
	 * Inits application configuraction
	 *
	 * @param void
	 */
	public function initConfig()
	{
		$this->cache->enabled = (bool) Nix\Config\Configurator::read('cache.enabled', true);
		Nix\Debugging\Debugger::$logFile = Nix\Config\Configurator::read('debug.log', $this->path . '/../temp/log/errors.log');
		switch(Nix\Config\Configurator::read('core.debug', 0)) {
			case 0:
				error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
				break;
			case 1:
				error_reporting(E_ALL ^ E_NOTICE);
				break;
			case 2:
				error_reporting(E_ALL);
				break;
			default:
				error_reporting(E_ALL);
				break;
		}
	}

	/**
	 * Loads applications file - from appliacation path or framework core path
	 *
	 * @param string $file filename
	 * @throws ApplicationException
	 */
	public function loadFile($file)
	{
		$file1 = $this->path . "/$file";
		$file2 = $this->corePath . "/app/$file";
		
		
		if(file_exists($file1)) {
			require_once $file1;
			return;
		} elseif(file_exists($file2)) {
			require_once $file2;
			return;
		} else {
			throw new ApplicationException('missing-file', $file);
		}
	}

	/**
	 * Loads application class
	 *
	 * @param string $class class name
	 * @throws Exception|ApplicationException
	 */
	public function loadControllerClass($class)
	{
		$class = basename($class);
		//$file = str_replace(array('_-', '_'), array('/', '/'), Nix\Utils\Tools::dash($class));
		$this->loadFile("Controllers/$class.php");

		if(!class_exists('App\Controllers\\'.$class, false)) {
			throw new ApplicationException('missing-controller', $class);
		}
	}

	/**
	 * Activates autoload for "/app/extends" and others
	 *
	 * @param array $dirs directories for autoload
	 * @return AutoLoader
	 */
	public function autoload($dirs = array())
	{
		$autoload = new AutoLoader($this->cache);
		$autoload->exts = Config::read('autoloader.exts', $autoload->exts);
		$autoload->autoRebuild = Config::read('core.debug') > 1;

		$dirs = (array) $dirs;
		array_unshift($dirs, "{$this->path}/extends/");
		foreach($dirs as $dir) {
			$autoload->addDir($dir);
		}

		return $autoload->register();
	}

	/**
	 * Runs the application
	 *
	 * @param void
	 * @throws ApplicationException
	 */
	public function run()
	{
		$this->loadAppControllerClass();
		$routing = $this->router->getRouting();

		if($this->router->routed === false || empty($routing['controller'])) {
			throw new ApplicationException('routing');
		}


		$module = implode('_', $routing['module']);
		$class = 'App\Controllers\\' . $routing['controller'] . 'Controller';
		if(!empty($module)) {
			$class = $module . '_' . $class;
		}

		$this->loadControllerClass($class);
		$this->controller = new $class();
		echo $this->controller->render();
	}

	/**
	 * Proccess application exceptions and renders error page/message
	 *
	 * @param Exception
	 */
	public function processException(Exception $exception)
	{
		if(isset($this->contorller) && $this->controller->routing->ajax) {
			Http::$response->error(500);
			if(Config::read('core.debug') == 0) {
				echo json_encode(array('response' => 'Internal server error.'));
			} else {
				echo json_encode(array('response' => $exception->getMessage()));
			}

			exit(1);
		}

		# show details when exception is no ApplicationException and debug level is not 0
		if(!($exception instanceof ApplicationException) && Config::read('core.debug') > 0) {
			Debug::showException($exception);
			exit(1);
		}

		self::$error = true;
		$class = $this->errorController;
		$this->loadAppControllerClass();
		/** @var $class Controller */
		$this->controller = new $class();
		$this->controller->init();
		$this->controller->setupLayout();
		$this->controller->loadLayoutTemplate();

		if($exception instanceof Nix\Application\ApplicationException) {
			if(Config::read('core.debug') > 0) {
				Http::$response->error(404);
				$this->controller->setErrorTemplate($exception->errorFile);
				$this->controller->template->variable = $exception->variable;
			} else {
				Debug::log($exception->getMessage());
				Http::$response->error(500);
				$this->controller->setErrorTemplate('500');
			}
		} else {
			$this->controller->setErrorTemplate('404');
		}

		ob_clean();
		echo $this->controller->template->render();
	}

	/**
	 * Returns application controller object
	 *
	 * @param void
	 * @return Controller
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Returns application path
	 *
	 * @param void
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Returns framework core path
	 *
	 * @param void
	 * @return string
	 */
	public function getCorePath()
	{
		return $this->corePath;
	}

	/**
	 * Returns application Router object
	 *
	 * @param void
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Returns application Cache object
	 *
	 * @param void
	 * @return Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}

	/**
	 * Loads or creates AppController
	 *
	 * @param void
	 */
	private function loadAppControllerClass()
	{
		if(class_exists('AppController', false)) {
			return;
		}

		try {
			$this->loadControllerClass('AppController');
		} catch (ApplicationException $e) {
			var_dump($e);
		//	eval('class AppController extends Controller {}');
		}
	}
}

class ApplicationException extends \Exception
{
	/** @var string - Error template name */
	public $errorFile;

	/** @var mixed */
	public $variable;

	/**
	 * Constructor
	 * @param string $variable error type
	 * @param string $errorType template variable
	 * @return ApplicationException
	 */
	public function __construct($errorType, $variable = null)
	{
		static $errors = array('routing', 'missing-controller', 'missing-method', 'missing-template', 'missing-helper', 'missing-file');
		if(!in_array($errorType, $errors)) {
			throw new Exception("Unsupported ApplicationException type '$error'.");
		}

		$this->errorFile = $errorType;
		$this->variable = $variable;
		parent::__construct(ucfirst(str_replace('-', ' ', $errorType)) . " '$variable'.");
	}
}

class ApplicationError extends \Exception
{
	/** @var string - Error template name */
	public $errorFile;

	/**
	 * Constructor
	 * @param string $template template name
	 * @param bool $debug is exception debuggable?
	 * @param int $errorCode http error code
	 */
	public function __construct($template, $debug = false, $errorCode = 404)
	{
		Application::$error = true;

		if($debug === true && Configurator::read('Core.debug') == 0) {
			$template = '404';
		}

		if($errorCode !== null) {
			Http::$response->error($errorCode);
		}

		$this->errorFile = $template;
		parent::__construct("Application error: $template.");
	}
}