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
 * PgSql Driver
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database\Drivers
 */
class PgsqlDriver extends Nix\Object implements IDriver
{
	/** @var Resource */
	protected $connection;

	/** @var Resource */
	protected $result;

	/** @var int */
	private static $insertedId;

	/**
	 * Connects to database
	 * 
	 * @param   array     configuration
	 * @throws  Exception
	 * @return  void
	 */
	public function connect($config)
	{
		$pairs = array(
			'server' => 'host',
			'username' => 'user',
			'password' => 'password',
			'port' => 'port',
			'database' => 'dbname'
		);

		$connection = '';
		foreach($pairs as $key => $val) {
			if(array_key_exists($key, $config)) {
				$connection .= " $val={$config[$key]}";
			}
		}

		$this->connection = @pg_connect($connection);

		if($this->connection === false) {
			throw new \Exception(pg_last_error());
		}

		pg_set_client_encoding($this->connection, $config["encoding"]);
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
		$this->result = @pg_query($this->connection, $sql);

		if($this->result === false) {
			throw new \Exception(pg_last_error($this->connection) . " ($sql).");
		}

		if(stripos('insert', $sql) === 0) {
			self::$insertedId = pg_last_oid($this->connection);
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
		$fetch = pg_fetch_array($this->result, null, $assoc ? PGSQL_ASSOC : PGSQL_NUM);

		if($fetch === false) {
			return null;
		} else {
			return $fetch;
		}
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
					return '"' . str_replace('"', '""', $value) . '"';
				} else {
					list($table, $column) = explode('.', $value);
				
					return $table . ($column == '*' ? '.*' : '"' . str_replace('"', '""', $value) . '"');
				}
			case Db::TEXT:
				return "'" . pg_escape_string($this->connection, $value) . "'";
			case Db::BINARY:
				return "'" . pg_escape_bytea($this->connection, $value) . "'";
			case Db::BOOL:
				return $value ? 'TRUE' : 'FALSE';
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
		return pg_affected_rows($this->result);
	}

	/**
	 * Counts rows in result
	 * 
	 * @return  int
	 */
	public function rowCount()
	{
		return pg_num_rows($this->result);
	}

	/**
	 * Returns last inserted id
	 * 
	 * @return  int
	 */
	public function insertedId()
	{
		return self::$insertedId;
	}

	/**
	 * Returns list of tables
	 * 
	 * @return  array
	 */
	public function getTables()
	{
		return db::fetchPairs("
			SELECT table_name FROM information_schema.tables
			WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
		"); 
	}

	/**
	 * Returns description of table columns
	 * 
	 * @param   string    table name
	 * @return  array
	 */
	public function getTableColumnsDescription($table)
	{
		$meta = db::fetchAll("
			SELECT a.attnum, a.attname AS field, t.typname AS type, a.attlen AS length,
			a.atttypmod AS lengthvar, a.attnotnull AS null, p.contype AS keytype
			FROM pg_type t, pg_class c, pg_attribute a LEFT JOIN pg_constraint p
			ON p.conrelid = a.attrelid AND a.attnum = ANY (p.conkey)
			WHERE c.relname = %s AND a.attnum > 0 AND a.attrelid = c.oid AND a.atttypid = t.oid
			ORDER BY a.attnum
		", $table);

		$structure = array();
		foreach($meta as $row) {
			$key = $row->pg_attribute->field;
			$type = $row->pg_type->type;
			$length = $row->pg_attribute->length > 0 ? $row->pg_attribute->length : $row->pg_attribute->lengthvar - 4;

			if(preg_match('#^(.*)\((\d+)\)( unsigned)?$#', $type, $match)) {
				$type = $match[1];
				$length = $match[2];

			} elseif(preg_match('#^(enum|set)\((.+)\)$#', $type, $match)) {
				$type = $match[1];
				$length = array();
				foreach(explode(',', $match[2]) as $val) {
					$length[] = substr($val, 1, -1);
				}
			}

			$structure[$key] = array(
				'null' => $row->pg_attribute->null === 't',
				'primary' => $row->pg_constraint->keytype === 'p',
				'length' => $length,
				'type' => $type
			);
		}

		return $structure;
	}

	/**
	 * Returns result columns
	 * 
	 * @return  array
	 */
	public function getResultColomns()
	{
		$count = pg_num_fields($this->result);

		$cols = array();
		for($i = 0; $i < $count; $i++) {
			$cols[] = array(
				pg_field_table($this->result, $i),
				pg_field_name($this->result, $i)
			);
		}

		return $cols;
	}
} 