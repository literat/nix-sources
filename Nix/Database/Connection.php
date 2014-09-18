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

use Nix,
	Nix\Database\Db,
	Nix\Database\Result,
	Nix\Database\Drivers;

/**
 * Database connection
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
class Connection extends Nix\Object
{
	/** @var bool */
	private $connected = false;

	/** @var DbDriver */
	private $driver;

	/** @var array */
	private $config = array();

	/**
	 * Constructor
	 * 
	 * @param array $config connection configuration
	 * @return DbConnection
	 */
	public function __construct($config)
	{
		static $default = array(
			'driver' => 'mysqli',
			'server' => 'localhost',
			'username' => 'root',
			'password' => 'root',
			'database' => 'test',
			'encoding' => 'utf8',
			'lazy' => false
		);

		$this->config = array_merge($default, (array) $config);

		if(!$this->config['lazy']) {
			$this->needConnection();
		}
	}

	/**
	 * Returns result object with parsed sql query
	 * 
	 * @param string $sql sql query
	 * @return PreparedResult
	 */
	public function prepare($sql)
	{
		$sqls = func_get_args();

		return new PreparedResult($this->factorySql($sqls), $this->driver);
	}

	/**
	 * Returns result object with executed parsed sql query or last inserted id
	 * 
	 * @param string $sql sql query
	 * @return Result|int
	 */
	public function query($sql)
	{
		$sqls = func_get_args();
		$sql = $this->factorySql($sqls);

		if(preg_match('#^(select|describe|show|explain)#i', $sql)) {
			$result = new Result($sql, $this->driver);
			return $result->execute();
		} else {
			$time = microtime(true);
			$driver = $this->driver->query($sql);
			Db::debug($sql, $time);

			if(stripos($sql, 'insert') === 0) {
				return $driver->insertedId();
			} else {
				return $driver->affectedRows();
			}
		}
	}

	/**
	 * Wrapper for called result
	 * 
	 * @see Result::fetchField()
	 * @param string $args sql query
	 * @return mixed
	 */
	public function fetchField($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);

		return $query->fetchField();
	}

	/**
	 * Wrapper for called result
	 * 
	 * @see Result::fetch()
	 * @param string $args sql query
	 * @return mixed
	 */
	public function fetch($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);
		
		return $query->fetch();
	}

	/**
	 * Wrapper for called result
	 * 
	 * @see Result::fetchAll()
	 * @param string $args sql query
	 * @return mixed
	 */
	public function fetchAll($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);

		return $query->fetchAll();
	}

	/**
	 * Wrapper for called result
	 * 
	 * @see Result::fetchPairs()
	 * @param string $args sql query
	 * @return array
	 */
	public function fetchPairs($args)
	{
		$args = func_get_args();
		$query = call_user_func_array(array($this, 'query'), $args);

		return $query->fetchPairs();
	}

	/**
	 * Return number of affected rows
	 * 
	 * @return int
	 */
	public function affectedRows()
	{
		return $this->driver->affectedRows();
	}

	/**
	 * Transforms sql and variables to regular sql query
	 * 
	 * @param array $args sql fragments and variables
	 * @return string
	 */
	public function factorySql($args)
	{
		$this->needConnection();

		$sql = '';
		$cond = array();
		while(($frag = array_shift($args)) !== null) {

			if(is_string($frag)) {
				$column = Db::COLUMN;
				$frag = preg_replace("#\[(.+)\]#Ue", '$this->driver->escape($column, "\\1")', $frag);
			}

			if(is_string($frag) && preg_match_all('#(?:(!)?%(r|c|s|i|f|b|bin|d|t|dt|set|l|a|v|m|if|end))(?!\w)#', $frag, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER)) {
				$temp = '';
				$start = 0;
				foreach($matches as $match) {
					$temp .= substr($frag, $start, $match[0][1] - $start);
					$start = $match[0][1] + strlen($match[0][0]);

					if($match[2][0] == 'if') {
						if(array_shift($args) == false) {
							$temp .= '/* ';
							$cond[] = false;
						} else {
							$cond[] = true;
						}

					} elseif($match[2][0] == 'end') {
						$pop = array_pop($cond);
						if ($pop == false && !in_array(false, $cond))
							$temp .= '*/';

					} else {
						if(empty($args)) {
							throw new \InvalidArgumentException('Missing sql argument.');
						}

						if($match[1][0] == '!') {
							$match[2][0] = Db::NULL;
						}

						$temp .= $this->escape(array_shift($args), $match[2][0]) . ' ';
					}
				}

				if(strlen($start) < strlen($frag)) {
					$temp .= substr($frag, $start);
				}

				$sql .= $temp;
			} else {
				if(is_string($frag)) {
					$sql .= $frag;
				} else {
					$sql .= $this->escape($frag);
				}
			}
		}

		return trim($sql);
	}

	/**
	 * Escapes $value as type $type
	 * 
	 * @param mixed $value value
	 * @param string $type type of variable
	 * @return mixed
	 */
	public function escape($value, $type = null)
	{
		$this->needConnection();

		if(empty($type)) {
			$type = $this->getType($value);
		}

		switch ($type) {
			case Db::RAW:
				return $value;
			case Db::NULL:
				return 'NULL';
			case Db::INTEGER:
			case Db::FLOAT:
				return rtrim(rtrim(number_format($value, 5, '.', ''), '0'), '.');
			case Db::COLUMN:
			case Db::TEXT:
			case Db::BOOL:
				return $this->driver->escape($type, $value);
			case Db::TIME:
			case Db::DATE:
			case Db::DATETIME:
				$value = is_int($value) ? $value : strtotime($value);
				return $this->driver->escape($type, $value);
			case Db::SET:
				return $this->driver->escape(Db::TEXT, implode(',', $value));
			case Db::A_ASSOC:
				foreach ($this->escapeArray($value) as $key => $val) $r[] = "$key = $val";
				return implode(', ', $r);
			case Db::A_LIST:
				return '(' . (empty($value) ? 'NULL' : implode(', ', $this->escapeArray($value))) . ')';
			case Db::A_VALUES:
				$array = $this->escapeArray($value);
				return '(' . implode(', ', array_keys($array)) . ') VALUES (' . implode(', ', $array) . ')';
			case Db::A_MULTI_VALUES:
				$first = array_shift($value);
				$first = $this->escapeArray($first);
				$return = '(' . implode(', ', array_keys($first)) . ') VALUES (' . implode(', ', $first) . ')';
				foreach($value as $array) {
					$return .= ', (' . implode(', ', $this->escapeArray($array)) . ')';
				}
				return $return;
			default:
				throw new \InvalidArgumentException('Unknown column modificator.');
		}
	}

	/**
	 * Escapes array - uses modificators in the key (column%i)
	 * 
	 * @param array $array
	 * @return array
	 */
	public function escapeArray($array)
	{
		$this->needConnection();

		$eArray = array();
		foreach((array) $array as $key => $val) {
			$key = explode('%', $key);
			if(count($key) == 1) {
				$eArray[$this->driver->escape(Db::COLUMN, $key[0])] = $this->escape($val);
			} else {
				$eArray[$this->driver->escape(Db::COLUMN, $key[0])] = $this->escape($val, $key[1]);
			}
		}

		return $eArray;
	}

	/**
	 * Returns modificator by varibale type
	 * 
	 * @param mixed $var variable
	 * @return string
	 */
	public function getType($var)
	{
		switch (gettype($var)) {
			case 'integer':
				return Db::INTEGER;
			case 'double':
				return Db::FLOAT;
			case 'array':
				return Db::A_VALUES;
			case 'boolean':
				return Db::BOOL;
			case 'NULL':
				return Db::NULL;
			default:
				return Db::TEXT;
		}
	}

	/**
	 * Returns db driver
	 * 
	 * @return DbDriver
	 */
	public function getDriver()
	{
	    $this->needConnection();

	    return clone $this->driver;
	}

	/**
	 * Checks connection and creates it
	 * 
	 * @throws Exception
	 */
	private function needConnection()
	{
		if($this->connected) {
			return;
		}

		$file = dirname(__FILE__) . '/Drivers/' . strtolower($this->config['driver']) . 'Driver.php';

		if(file_exists($file)) {
			require_once $file;
		}

		$class = 'Nix\Database\Drivers\\' . $this->config['driver'] . 'Driver';
		if(!class_exists($class)) {
			throw new \Exception("Missing database driver $class.");
		}

		$this->driver = new $class;
		$this->driver->connect($this->config);
		$this->connected = true;
	}
}