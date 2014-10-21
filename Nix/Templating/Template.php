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

require_once dirname(__FILE__) . '/../common/Object.php';
require_once dirname(__FILE__) . '/../Caching/Cache.php';
require_once dirname(__FILE__) . '/ITemplate.php';
require_once dirname(__FILE__) . '/Helpers/FilterHelper.php';

use Nix,
	Nix\Templating\Template,
	Nix\Templating\Helpers,
	Nix\Templating\Helpers\FilterHelper,
	Nix\Object,
	Nix\Templating\ITemplate;

/**
 * Template class provides powerful templating system
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Templating
 *
 * @property string $file
 * @property string $temp
 * @property string $vars
 */
class Template extends Object implements ITemplate
{
	/** @var array of default template keywords */
	public static $defaultTplKeywords = array(
		'{if %%}' => '<?php if (\1): ?>',
		'{elseif %%}' => '<?php ; elseif (\1): ?>',
		'{for %%}' => '<?php for (\1): ?>',
		'{foreach %%}' => '<?php foreach (\1): ?>',
		'{while %%}' => '<?php while (\1): ?>',
		'{/if}' => '<?php endif; ?>',
		'{/for}' => '<?php endfor; ?>',
		'{/foreach}' => '<?php endforeach; ?>',
		'{/while}' => '<?php endwhile; ?>',
		'{else}' => '<?php ; else: ?>',
		'{continue}' => '<?php continue; ?>',
		'{break}' => '<?php break; ?>',
	);

	/** @var array of default template triggers */
	public static $defaultTplTriggers = array(
		'php' => array('Nix\Templating\Template', 'cbPhpTrigger'),
		'extends' => array('Nix\Templating\Template', 'cbExtendsTrigger'),
		'assign' => array('Nix\Templating\Template', 'cbAssignTrigger'),
		'noescape' => array('Nix\Templating\Template', 'cbNoEscapeTrigger'),
	);

	/** @var array of default template functions */
	public static $defaultTplFunctions = array(
		'include' => '$template->subTemplate',
		'mimetype' => '$template->setMimetype',
	);

	/** @var array of default template filters */
	public static $defaultTplFilters = array();

	/** @var array of registered blocks */
	protected static $registeredBlocks = array();

	/** @var array of template keywords */
	public $tplKeywords = array();

	/** @var array of template triggers */
	public $tplTriggers = array();

	/** @var array template functions */
	public $tplFunctions = array();

	/** @var array of template filters */
	public $tplFilters = array();

	/** @var Cache cache */
	protected $cache;

	/** @var string file name */
	protected $file;

	/** @var string extends file */
	protected $extendsFile;

	/** @var array of variables */
	protected $vars = array();

	/** @var array of helpers */
	protected $helpers = array();

	/** @var array of do not escape */
	protected $dontEscape = array();

	/** @var bool if has extends */
	private $__hasExtends = false;

	/** @var bool if has blocks */
	private $__hasBlocks = false;

	/** @var bool if is included */
	private $isIncluded = false;

	/**
	 * Constructor
	 *
	 * @param  string  $file        template filename
	 * @param  string  $cache       path for cache templates
	 * @param  bool    $isIncluded  is template child of another template?
	 * @return Template
	 */
	public function __construct($file = null, Nix\Caching\Cache $cache = null, $isIncluded = false)
	{
		$this->tplKeywords = self::$defaultTplKeywords;
		$this->tplTriggers = self::$defaultTplTriggers;
		$this->tplFunctions = self::$defaultTplFunctions;
		$this->tplFilters = self::$defaultTplFilters;

		if(!($cache instanceof Nix\Caching\Cache)) {
			$cache = new Nix\Caching\Cache();
		}

		if($file !== null) {
			$this->setFile($file);
		}

		$this->cache = $cache;
		$this->getHelper('filter');
		$this->isIncluded = $isIncluded;
	}

	/**
	 * Sets template file name
	 *
	 * @param string $file template filename
	 * @throws RuntimeException
	 * @return Template
	 */
	public function setFile($file)
	{
		if(!file_exists($file)) {
			throw new \RuntimeException("Template file '$file' was not found.");
		}

		$this->file = $file;
	}

	/**
	 * Returns template file name
	 *
	 * @param string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Sets variable
	 *
	 * @param string $key variable name
	 * @param mixed $val content
	 * @throws BadMethodCallException
	 * @return Template
	 */
	public function setVar($key, $val)
	{
		if(empty($key)) {
			throw new \BadMethodCallException('Key must not be empty.');
		}

		$this->vars[$key] = $val;
		return $this;
	}

	/**
	 * Returns variable value
	 *
	 * @param string $key variable name
	 * @throws BadMethodCallException
	 * @return mixed
	 */
	public function getVar($key)
	{
		if(empty($key)) {
			throw new \BadMethodCallException('Key must not be empty.');
		}

		if(isset($this->vars[$key])) {
			return $this->vars[$key];
		}
		
		return null;
	}

	/**
	 * Sets multi variables values
	 *
	 * @param array $vars associative array of variables
	 * @return Template
	 */
	public function setVars($vars)
	{
		foreach($vars as $key => $val) {
			$this->setVar($key, $val);
		}

		return $this;
	}

	/**
	 * Returns variables
	 *
	 * @return array
	 */
	public function getVars()
	{
		return $this->vars;
	}

	/**
	 * Checks if the variable is set
	 *
	 * @param string $key variable name
	 * @return bool
	 */
	public function __isset($key)
	{
		return isset($this->vars[$key]);
	}

	/**
	 * Unsets variable value
	 *
	 * @param string $key variable name
	 */
	public function __unset($key)
	{
		unset($this->vars[$key]);
	}

	/**
	 * Sets variable value
	 *
	 * @param string $key variable name
	 * @param mixed $value value
	 */
	public function __set($key, $value)
	{
		$this->setVar($key, $value);
	}

	/**
	 * Returns variable value
	 *
	 * @param string $key variable name
	 * @return mixed
	 */
	public function __get($key)
	{
		if(array_key_exists($key, $this->vars)) {
			return $this->vars[$key];
		} else {
			parent::__get($key);
		}
	}

	/**
	 * Sets extending template filename
	 *
	 * @param string $file
	 * @return Template
	 */
	public function setExtendsFile($file = null)
	{
		if(empty($file)) {
			$file = null;
			$this->__hasExtends = false;
		} else {
			$this->__hasExtends = true;
		}

		$this->extendsFile = $file;
		
		return $this;
	}

	/**
	 * Returns extending template filename
	 *
	 * @return string|null
	 */
	public function getExtendsFile()
	{
		return $this->extendsFile;
	}

	/**
	 * Returns true if template has extending template
	 *
	 * @return bool
	 */
	public function hasExtendsFile()
	{
		return $this->extendsFile != null;
	}

	/**
	 * Returns true if template has extending template or is included
	 *
	 * @param null|bool $set
	 * @return Template|bool
	 */
	public function isInRelation($set = null)
	{
		if($set !== null) {
			$this->isIncluded = true;
			return $this;
		}

		return $this->hasExtendsFile() || $this->isIncluded;
	}

	/**
	 * Returns clone of template
	 *
	 * @return Template
	 */
	public function getClone()
	{
		$clone = clone $this;
		return $clone->setExtendsFile();
	}

	/**
	 * Loads helper
	 *
	 * @param string $name helper name
	 * @param string $var variable name in which will be helper instance
	 * @return Helper
	 */
	public function getHelper($name, $var = null)
	{
		static $pairs = array();

		if(!array_key_exists($name, $pairs) || $pairs[$name] != $var) {
			if(empty($var)) {
				$var = strtolower($name);
			}

			$class = 'Nix\Templating\Helpers\\' . ucfirst(strtolower($name)) . 'Helper';
			$pairs[$name] = $var;
			$this->helpers[$var] = new $class($this, $var);
			$this->dontEscape[$var] = true;
		}

		return $this->helpers[$var];
	}

	/**
	 * Includes sub-template file
	 *
	 * @param string $file template filename
	 * @return string
	 */
	public function subTemplate($file)
	{
		$template = $this->getClone();
		$template->isInRelation(true);
		$template->setFile($file);
		return $template->render();
	}

	/**
	 * Sends mimetype header
	 *
	 * @param string $mimetype
	 */
	public function setMimetype($mimetype = 'text/html')
	{
		header("Content-type: $mimetype");
	}

	/**
	 * Register functions block
	 *
	 * @param string $id
	 * @param string $function function name
	 * @param string $mode mode - append / prepend
	 * @return Template
	 */
	public function registerBlock($id, $function, $mode = '')
	{
		if(!isset(self::$registeredBlocks[$id])) {
			self::$registeredBlocks[$id] = array(
				'prepend' => array(),
				'append' => array(),
				'' => array(),
			);
		}

		self::$registeredBlocks[$id][$mode][] = $function;
		return $this;
	}

	/**
	 * Renders functions blocks and append / preppend content
	 *
	 * @param string $id function name
	 * @param array $vars defined variables
	 * @return string
	 */
	public function getFilterBlock($id, $vars)
	{
		if(!isset(self::$registeredBlocks[$id])) {
			return;
		}

		$render = '';
		$blocks = self::$registeredBlocks[$id];
		foreach(array_reverse($blocks['prepend']) as $func) {
			$render .= call_user_func($func, $vars);
		}

		$function = array_pop($blocks['']);
		
		if(count($blocks['']) > 0 && $function == '_filter_block_' . $id . '_' . substr(md5($this->file), 0, 10)) {
			$function = array_pop($blocks['']);
		}

		$render .= call_user_func($function, $vars);

		foreach(array_reverse($blocks['append']) as $func) {
			$render .= call_user_func($func, $vars);
		}

		return $render;
	}

	/**
	 * Renders template a return content
	 *
	 * @return string
	 */
	public function render()
	{
		if(!file_exists($this->file)) {
			throw new \Exception("Template file '{$this->file}' was not found.");
		}

		extract($this->vars);
		extract($this->helpers);

		$template = $this;
		if(class_exists('Application', false)) {
			$controller = Controller::get();
			$application = Nix\Application\Application::get();
		}

		$___pre = ob_get_contents();
		$___cacheFile = 'template_' . md5($this->file);

		if(!$this->cache->isCached($___cacheFile)) {
			$___result = $this->createTemplateTemp($___cacheFile);
			ob_clean();

			if($___result === true) {
				include $this->cache->getFilename($___cacheFile);
			} else {
				eval('?>' . $___result);
			}
		} else {
			ob_clean();
			include $this->cache->getFilename($___cacheFile);
		}

		$return = ob_get_contents();
		ob_clean();

		if($this->hasExtendsFile()) {
			$clone = $this->getClone();
			$clone->setFile($this->getExtendsFile());
			$return = $clone->render() . $return;
		}

		if(!preg_match('#^\s+$#', $___pre)) {
			echo $___pre;
		}

		return $return;
	}

	/**
	 * Creates php template file from pseudo template style
	 *
	 * @param  string $cacheFile cache file name
	 * @return bool
	 */
	protected function createTemplateTemp($cacheFile)
	{
		$file = file_get_contents($this->file);
		if($file === false) {
			throw new \RuntimeException('File templates cant be read.');
		}

		# comments
		$file = preg_replace('#\{\*.+\*\}(\r?\n)?#s', '', $file);

		# keywords
		$keywords_k = $keywords_v = array();
		foreach($this->tplKeywords as $key => $val) {
			$keywords_k[] = '#' . str_replace('%%', '(.+)',
				preg_quote($key, '#')) . '#U';
			$keywords_v[] = $val;
		}

		$file = preg_replace($keywords_k, $keywords_v, $file);

		# triggers
		$triggers = implode('|', array_keys($this->tplTriggers));
		$file = preg_replace_callback('#\{(' . $triggers .')\s+(.+)?\}#Ui',
			array($this, '__cbTriggers'), $file);


		# variables
		$file = preg_replace_callback('#\{(?:(?:=([^|\}]+?))|(\$[^|\}]+?))(?:\|([^\}]+?))?\}#U',
			array($this, '__cbVariables'), $file);

		# extending
		$file = preg_replace_callback('#\{block(?: (append|prepend))? (?:\#([^}]+))\}(.*)\{/block\}#Us',
			array($this, '__cbBlock'), $file);

		# functions
		$functions = implode('|', array_keys($this->tplFunctions));
		$file = preg_replace_callback('#\{(' . $functions .')(?:\s+([^|]+))?(?:\|([^\}]+?))?\}#U',
			array($this, '__cbFunctions'), $file);


		# we have extends file and no blocks
		if($this->__hasExtends === true && $this->__hasBlocks === false) {
			$pos = strrpos($file, '//--EXLUDE--//'); # length 14 + 2
			if($pos !== false) {
				$fileS = substr($file, 0, $pos + 16);
				$fileE = substr($file, $pos + 16);
				$fileE = $this->__cbBlock(array('', '', 'content', $fileE));
				$file = $fileS . $fileE;
			} else {
				$file = $this->__cbBlock(array('', '', 'content', $file));
			}
		}

		$result = $this->cache->set($cacheFile, $file, array(
			'files' => array($this->file)
		));

		if(!$result) {
			return $file;
		} else {
			return true;
		}
	}

	/**
	 * Returns template code for variables
	 *
	 * @param array $matches
	 * @return string
	 */
	protected function __cbVariables($matches)
	{
		$escape = preg_match('#^\$(\w+)#', @$matches[2], $m) && isset($this->dontEscape[$m[1]]);

		return '<?php echo ' . $this->parseFilters($matches[1] . @$matches[2], @$matches[3], !$escape) . ' ?>';
	}

	/**
	 * Returns template code for blocks
	 *
	 * @param  array  $matches array of matches
	 * @return string
	 */
	protected function __cbBlock($matches)
	{
		$this->__hasBlocks = true;
		$id = substr(md5($matches[2]), 0, 10);
		$name = '_filter_block_' . $id . '_' . substr(md5($this->file), 0, 10);

		return "<?php if (!function_exists('$name')) { "
		     . "\$template->registerBlock('$id', '$name', '$matches[1]');"
		     . "function $name() { extract(func_get_arg(0)); ?>$matches[3]<?php }} "
		     . "if (!\$template->isInRelation()) "
		     . "echo \$template->getFilterBlock('$id', get_defined_vars()); ?>";
	}

	/**
	 * Calls template trigger callback
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function __cbTriggers($matches)
	{
		$cb = $this->tplTriggers[strtolower($matches[1])];

		return call_user_func($cb, $matches[2]);
	}

	/**
	 * Calls template function callback
	 *
	 * @param array $matches
	 * @return string|null
	 */
	protected function __cbFunctions($matches)
	{
		$expression = $this->tplFunctions[$matches[1]] . '(' . @$matches[2] . ')';
		if(!empty($matches[3])) {
			$expression = $this->parseFilters($expression, $matches[3], false);
		}

		return "<?php echo $expression ?>";
	}

	/**
	 * Callback for extending template
	 *
	 * @param string $expression
	 * @return string
	 */
	protected function cbExtendsTrigger($expression)
	{
		$this->__hasExtends = true;

		return "<?php \$template->setExtendsFile($expression) //--EXLUDE--//?>";
	}

	/**
	 * Callback for php raw expression
	 *
	 * @param string $expression
	 * @return string
	 */
	protected function cbPhpTrigger($expression)
	{
		return "<?php $expression ?>";
	}

	/**
	 * Callback for assign function
	 *
	 * @param string $expression
	 * @return string
	 */
	protected function cbAssignTrigger($expression)
	{
		$space = strpos($expression, ' ');
		$var = substr($expression, 1, $space - 1);
		$val = substr($expression, $space);

		return "<?php \$template->setVar('$var', $val) //--EXLUDE--//?>";
	}

	/**
	 * Callback for turns off variable escaping
	 *
	 * @param string $expression expression
	 */
	protected function cbNoEscapeTrigger($expression)
	{
		$this->dontEscape[substr($expression, 1)] = true;
	}

	/**
	 * Parses filter expression
	 *
	 * @param string $variable variable which shoul be filtered
	 * @param string $expression unparsed filters expression
	 * @param bool $allowAutoescape
	 * @return string
	 */
	private function parseFilters($variable, $expression, $allowAutoescape = true)
	{
		$filters = array();
		if(empty($expression)) {
			$expression = array();
		} else {
			$expression = explode('|', $expression);
		}

		foreach($expression as $filter) {
			if(preg_match('#([^:]+)(?:\:("[^"]+"|\'[^\']+\'|[^\:]+))+#', $filter, $match)) {
				array_shift($match);
				$filters[array_shift($match)] = $match;
			} else {
				$filters[$filter] = array();
			}
		}

		if($allowAutoescape) {
			if(isset($filters['raw'])) {
				unset($filters['raw']);
			}
			elseif(!isset($filters['escape'])) {
				$filters['escape'] = array();
			}
		}

		if(isset($filters['raw'])) {
			unset($filters['raw']);
		}

		foreach($filters as $name => $args) {
			if(isset($this->tplFilters[$name])) {
				$name = $this->tplFilters[$name];
			}
			array_unshift($args, $variable);
			$variable = "$name(" . implode(',', $args) . ")";
		}

		return $variable;
	}
}