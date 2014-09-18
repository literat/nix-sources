<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Database;

/**
 * Database drivers interface
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
interface IDriver
{
	/**
	 * Connects to database
	 * 
	 * @param array $config configuration
	 * @throws Exception
	 */
	public function connect($config);

	/**
	 * Runs native sql query
	 * 
	 * @param string $sql sql query
	 * @throws Exception
	 * @return Driver
	 */
	public function query($sql);

	/**
	 * Fetchs one result's row
	 * 
	 * @param bool $assoc true = associative array | false = array
	 * @return array
	 */
	public function fetch($assoc);

	/**
	 * Escapes $value as a $type
	 * 
	 * @param string $type
	 * @param string $value
	 * @return string
	 */
	public function escape($type, $value);

	/**
	 * Returns number of affected rows
	 * 
	 * @return int
	 */
	public function affectedRows();

	/**
	 * Counts rows in result
	 * 
	 * @return int
	 */
	public function rowCount();

	/**
	 * Returns last inserted id
	 * 
	 * @return int
	 */
	public function insertedId();

	/**
	 * Returns list of tables
	 * 
	 * @return array
	 */
	public function getTables();

	/**
	 * Returns description of table columns
	 * 
	 * @param string $table table name
	 * @return array
	 */
	public function getTableColumnsDescription($table);

	/**
	 * Returns result columns
	 * 
	 * @return array
	 */
	public function getResultColomns();
}