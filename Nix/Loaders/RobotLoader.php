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
	Nix\Caching\Cache,
	Nix\Utils\Tools;

/**
 * RobotLoader
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Loaders
 */
class RobotLoader extends AutoLoader
{
	/** @var array - Allowed extension */
	public $exts = array('php');

	/** @var bool if use autorebuild */
	public $autoRebuild = false;

	/** @var bool if rebuild */
	public $rebuild = false;

	/** @var array of classes */
	private $classes = array();

	/** @var array of files */
	private $files = array();

	/** @var array of dirs */
	private $dirs = array();

	/** @var Cache cache handler */
	private $cache;

	/**
	 * Contructor - registers autoload
	 *
	 * @param  string|Cache $storage cache path or Cache class instance
	 * @return RobotLoader
	 */
	public function __construct($storage = './')
	{
		if($storage instanceof Cache) {
			$this->cache = $storage;
		} else {
			$this->cache = new Cache(true, $storage);
		}
	}

	/**
	 * Adds directory for scan
	 *
	 * @param string $dir path
	 * @throws Exception
	 * @return RobotLoader
	 */
	public function addDir($dir)
	{
		if(!is_dir($dir)) {
			throw new \Exception("Directory '$dir' does not exists.");
		}

		$this->dirs[] = $dir;
		return $this;
	}

	/**
	 * Autoload handler - loads file with $class, or rebuild cache
	 *
	 * @param string $class class name
	 * @return RobotLoader
	 */
	public function load($class)
	{
		$class = strtolower($class);
		if(isset($this->classes[$class]) && file_exists($_SERVER['DOCUMENT_ROOT'] . $this->classes[$class])) {
			require_once $_SERVER['DOCUMENT_ROOT'] . $this->classes[$class];
		} elseif(!$this->rebuild && $this->autoRebuild) {
			$this->rebuild();

			if(isset($this->classes[$class]) && file_exists($_SERVER['DOCUMENT_ROOT'] . $this->classes[$class])) {
				require_once $_SERVER['DOCUMENT_ROOT'] . $this->classes[$class];
			}
		}

		return $this;
	}

	/**
	 * Rebuilds cache list
	 *
	 * @param void
	 * @throws Exception
	 * @return RobotLoader
	 */
	public function rebuild()
	{
		$this->findClasses();
		$this->rebuild = true;
		$this->cache->set('robotloader', $this->classes);
		return $this;
	}

	/**
	 * Loads list of cached classes or creates it
	 *
	 * @param void
	 * @return RobotLoader
	 */
	public function register()
	{
		parent::registerCallback(array($this, 'load'));
		$this->classes = $this->cache->get('robotloader');
		if($this->classes === null) {
			$this->rebuild();
		}

		return $this;
	}

	/**
	 * Returns list of classes
	 *
	 * @param void
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * Finds all files and theirs classes
	 *
	 * @param void
	 * @return RobotLoader
	 */
	private function findClasses()
	{
		$this->files = array();
		$this->classes = array();

		foreach($this->dirs as $dir) {
			$this->getFiles(new \RecursiveDirectoryIterator($dir));
		}

		foreach($this->files as $file) {
			$catch = false;
			foreach(token_get_all(file_get_contents($file)) as $token) {
				if(is_array($token)) {
					if($token[0] == T_CLASS || $token[0] == T_INTERFACE) {
						$catch = true;
					} elseif($token[0] == T_STRING && $catch) {
						$this->classes[strtolower($token[1])] = Tools::relativePath($file);
						$catch = false;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Recursive DirectoryIterator handler
	 *
	 * @param RecursiveDirectoryIterator $rdi
	 * @return RobotLoader
	 */
	private function getFiles($rdi)
	{
		$exts = '#\.(' . implode('|', $this->exts) . ')$#i';
		for($rdi->rewind(); $rdi->valid(); $rdi->next()) {
			if($rdi->isDot()) {
				continue;
			}

			if($rdi->isFile() && preg_match($exts, $rdi->getFilename())) {
				$this->files[] = $rdi->getPathname();
			} elseif($rdi->isDir() && !preg_match('#^\.(svn|cvs)$#i', $rdi->getFilename()) && $rdi->hasChildren()) {
				$this->getFiles($rdi->getChildren());
			}
		}

		return $this;
	}
}