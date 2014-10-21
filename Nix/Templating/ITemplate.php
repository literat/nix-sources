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

/**
 * ITemplate
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Templating
 */
interface ITemplate
{
	/**
	 * Sets variable
	 *
	 * @param string $key var name
	 * @param mixed $val
	 * @return Template
	 */
	public function setVar($key, $val);

	/**
	 * Returns variable
	 *
	 * @param string $key var name
	 * @return mixed
	 */
	public function getVar($key);

	/**
	 * Sets variables
	 *
	 * @param array $vars variables
	 * @return ITemplate
	 */
	public function setVars($vars);

	/**
	 * Returns variables
	 *
	 * @return array
	 */
	public function getVars();

	/**
	 * Sets file name
	 *
	 * @param string $file filename
	 * @return ITemplate
	 */
	public function setFile($file);

	/**
	 * Returns file name
	 *
	 * @param string
	 */
	public function getFile();

	/**
	 * Renders template a return content
	 *
	 * @return string
	 */
	public function render();

	/**
	 * Interface method
	 * @param  string  $name variable name
	 * @return boolean       if is set
	 */
	public function __isset($name);

	/**
	 * Interface method
	 * @param 	string $name variable name
	 * @return  void
	 */
	public function __unset($name);

	/**
	 * Interface method
	 * @param 	string 	$name  variable name
	 * @param 	mixed 	$value value
	 * @return 	void
	 */
	public function __set($name, $value);

	/**
	 * Interface method
	 * @param  string $name variable name
	 * @return mixed 		variable value
	 */
	public function __get($name);
}