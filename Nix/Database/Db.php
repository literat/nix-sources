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

use Nix\Database\Connection,
	Nix\Config\Configurator,
	Nix\Debugging\Debugger;

/**
 * Database class
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
class Db
{
	/**
	 * Column types
	 */
	const COLUMN = 'c'; 
	const RAW = 'r';
	const NULL = 'n';
	const TEXT = 's';
	const BINARY = 'bin';
	const BOOL = 'b';
	const INTEGER = 'i';
	const FLOAT = 'f';
	const TIME = 't';
	const DATE = 'd';
	const DATETIME = 'dt';
	const SET = 'set';
	const A_LIST = 'l';
	const A_ASSOC = 'a';
	const A_VALUES = 'v';
	const A_MULTI_VALUES = 'm';
	/***/

	/** @var array of sqls */
	public static $sqls = array();

	/** @var string is active */
	private static $active;

	/** @var array of connections */
	private static $connections = array();

	/**
	 * Connects to database
	 * 
	 * If you don't provide $config, its load from config directive Db.connection
	 * @param array $config connection config
	 * @param string $name connection name
	 * @return bool
	 */
	public static function connect($config = array(), $name = 'default')
	{
		if(isset(self::$connections[$name])) {
			self::$active[$name];
		}

		if(empty($config) && class_exists('Nix\Config\Configurator', false)) {
			$config = Configurator::read('db.connection');
		}

		self::$connections[$name] = new Connection($config);
		self::$active = $name;
		
		return true;
	}

	/**
	 * Actives the connection $name
	 * 
	 * @param string $name connection name
	 */
	public static function active($name)
	{
		if(!isset(self::$connections[$name])) {
			throw new Exception("Connection '$name' doesn't exists.");
		}

		self::$active = $name;
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::rawPrepare()
	 * @param string $sql sql query
	 * @return Result
	 */
	public static function execute($sql)
	{
		$args = func_get_args();

		return call_user_func_array(array(self::getConnection(), 'execute'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::prepare()
	 * @param string $sql sql query
	 * @return PreparedResult
	 */
	public static function prepare($sql)
	{
		$args = func_get_args();

		return call_user_func_array(array(self::getConnection(), 'prepare'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::query()
	 * @param string $sql sql query
	 * @return Result
	 */
	public static function query($sql)
	{
		$args = func_get_args();
		
		return call_user_func_array(array(self::getConnection(), 'query'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::fetchField()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchField($sql)
	{
		$args = func_get_args();
		
		return call_user_func_array(array(self::getConnection(), 'fetchField'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::fetch()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetch($sql)
	{
		$args = func_get_args();
		
		return call_user_func_array(array(self::getConnection(), 'fetch'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::fetchAll()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchAll($sql)
	{
		$args = func_get_args();
		
		return call_user_func_array(array(self::getConnection(), 'fetchAll'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::fetchPairs()
	 * @param string $sql sql query
	 * @return mixed
	 */
	public static function fetchPairs($sql)
	{
		$args = func_get_args();
		
		return call_user_func_array(array(self::getConnection(), 'fetchPairs'), $args);
	}

	/**
	 * Wrapper for active connection
	 * 
	 * @see Connection::affectedRows()
	 * @return int
	 */
	public static function affectedRows()
	{
		return call_user_func(array(self::getConnection(), 'affectedRows'));
	}

	/**
	 * Logs sql query to debugger. Works only when Db.debug is active
	 * 
	 * @param string $sql sql query
	 * @param int $time microtime timestamp
	 */
	public static function debug($sql, $time)
	{
		if(!class_exists('Nix\Config\Configurator', false) || !class_exists('Nix\Debugging\Debugger', false)) {
			return;
		}

		if(Configurator::read('db.debug', 1) == 0) {
			return;
		}

		$abbr = 'time: ' . Debugger::getTime($time) . 'ms; affected: ' . self::affectedRows();
		$text = "<abbr title=\"$abbr\">" . htmlspecialchars($sql) . '</abbr>';
		Debugger::toolbar($text, 'sql');
	}

	/**
	 * Returns active connection
	 * 
	 * @throws Exception
	 * @return Connection
	 */
	public static function getConnection()
	{
		if(empty(self::$active) || !isset(self::$connections[self::$active])) {
			self::connect();
		}

		return self::$connections[self::$active];
	}
	
	/**
	 * Returns db driver
	 * 
	 * @return DbDriver
	 */
	public static function getDriver()
	{
	    $connection = self::getConnection();

	    return $connection->getDriver();
	}
}