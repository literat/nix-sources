<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Database\Drivers;

use Nix,
	Nix\Database\Db,
	Nix\Database\IDriver;

/**
 * MySqli Driver
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database\Drivers
 */
class MysqliDriver extends Nix\Object implements IDriver
{
	/** @var MySQLi */
	protected $connection;

	/** @var MySQLi_Result */
	protected $result;

	/**
	 * Connects to database
	 * 
	 * @param   array     configuration
	 * @throws  Exception
	 * @return  void
	 */	
	public function connect($config)
	{
		$this->connection = new \mysqli($config['server'], $config['username'], $config['password'], $config['database']);

		if(mysqli_connect_errno()) {
			throw new \Exception(mysqli_connect_error());
		}

		$this->connection->set_charset($config['encoding']);
	}

	/**
	 * Runs native sql query
	 * 
	 * @param   string    sql query
	 * @throws  Exception
	 * @return  DbDriver  clone $this
	 */
	public function query($sql)
	{
		$this->result = $this->connection->query($sql);

		if($this->connection->errno) {
			throw new \Exception($this->connection->error . " ($sql).");
		}

		return clone $this;
	}

	/**
	 * Fetchs one result's row
	 * 
	 * @param   bool      true = associative array | false = array
	 * @return  array
	 */
	public function fetch($assoc)
	{
		return $this->result->fetch_array($assoc ? MYSQLI_ASSOC : MYSQLI_NUM);
	}

	/**
	 * Escapes $value as a $type
	 * 
	 * @param   strign    type
	 * @param   strign    value
	 * @return  string
	 */
	public function escape($type, $value)
	{
		switch($type) {
			case Db::COLUMN:
				if(strpos($value, '.') === false) {
					return "`$value`";
				} else {
					list($table, $column) = explode('.', $value);
				
					return "`$table`" . ($column == '*' ? '.*' : ".`$column`");
				}
			case Db::TEXT:
				return "'" . $this->connection->escape_string($value) . "'";
			case Db::BINARY:
				return "'" . $this->connection->escape_string($value) . "'";
			case Db::BOOL:
				return $value ? 1 : 0;
			case Db::TIME:
				return date("'H:i:s'", $value);
			case Db::DATE:
				return date("'Y-m-d'", $value);
			case Db::DATETIME:
				return date("'Y-m-d H:i:s'", $value);
			default:
				throw new \InvalidArgumentException('Unknown column type.');
		}
	}

	/**
	 * Returns number of affected rows
	 * 
	 * @return  int
	 */
	public function affectedRows()
	{
		return $this->connection->affected_rows;
	}

	/**
	 * Counts rows in result
	 * 
	 * @return  int
	 */
	public function rowCount()
	{
		return $this->result->num_rows;
	}

	/**
	 * Returns last inserted id
	 * 
	 * @return  int
	 */
	public function insertedId()
	{
		return $this->connection->insert_id;
	}

	/**
	 * Returns list of tables
	 * 
	 * @return  array
	 */
	public function getTables()
	{
		return db::fetchPairs('SHOW TABLES');
	}

	/**
	 * Returns description of table columns
	 * 
	 * @param   string    table name
	 * @return  array
	 */
	public function getTableColumnsDescription($table)
	{
		$structure = array();
		foreach(db::fetchAll("DESCRIBE [$table]") as $row) {
			$type = $row->Type;
			$length = null;
			if(preg_match('#^(.*)\((\d+)\)( unsigned)?$#', $row->Type, $match)) {
				$type = $match[1];
				$length = $match[2];
			} elseif(preg_match('#^(enum|set)\((.+)\)$#', $row->Type, $match)) {
				$type = $match[1];
				$length = array();
				foreach(explode(',', $match[2]) as $val) {
					$length[] = substr($val, 1, -1);
				}
			}

			$structure[$row->Field]['null'] = $row->Null === 'YES';
			$structure[$row->Field]['primary'] = $row->Key === 'PRI';
			$structure[$row->Field]['length'] = $length;
			$structure[$row->Field]['type'] = $type;
		}

		return $structure;
	}

	/**
	 * Returns result columns
	 * 
	 * @return  array
	 */
	public function	getResultColomns()
	{
		$count = $this->result->field_count;

		$cols = array();
		for($i = 0; $i < $count; $i++) {
			$col = $this->result->fetch_field_direct($i);
			$cols[] = array($col->table, $col->name);
		}

		return $cols;
	}
}