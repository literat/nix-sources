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
	Nix\Database\ResultNode,
	Nix\Database\IDriver;

/**
 * Result class
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
class Result extends Nix\Object implements \Countable, \IteratorAggregate
{
	/** @var string */
	protected $query;

	/** @var Driver */
	protected $driver;

	/** @var bool */
	protected $executed = false;

	/** @var array */
	protected $association = array();

	/** @var int - Points to the last fetched row */
	private $rowPointer = -1;

	/** @var array - Array of columns data */
	private $cols = array();

	/** @var bool - Fetch data with table names? */
	private $tables = false;

	/** @var array - Stored fetched rows */
	private $rows = array();

	/** @var null|array - Stored one over-fetched row */
	private $stored = array();

	/**
	 * Constructor
	 * 
	 * @param string $query sql query
	 * @param IDriver $driver driver instance
	 * @return Result
	 */
	public function __construct($query, IDriver $driver)
	{
		$this->query = $query;
		$this->driver = $driver;
	}

	/**
	 * Executes sql query
	 * 
	 * @return Result
	 */
	public function execute()
	{
		$this->runSqlQuery();
		$this->loadResultColumns();
		return $this;
	}

	/**
	 * Returns first field value
	 * 
	 * @return mixed
	 */
	public function fetchField()
	{
		$this->checkExecution();

		if(empty($this->rows)) {
			$this->rows = array($this->fetch());
		}

		if(empty($this->rows[0])) {
			return null;
		}

		return current($this->rows[0]);
	}

	/**
	 * Returns array of pairs
	 * 
	 * If is select only one column then is returned scalar array
	 * 
	 * @return array
	 */
	public function fetchPairs()
	{
		$this->checkExecution();

		$array = array();
		foreach($this->fetchAll() as $row) {
			if(count((array) $row) == 1) {
				$array[] = current($row);
			} else {
				$array[current($row)] = next($row);
			}
		}

		return $array;
	}

	/** 
	 * Returns one fetched row
	 * 
	 * @return array
	 */
	public function fetch()
	{
		if(!isset($this->rows[$this->rowPointer + 1])) {
			$this->fetchRow();
		}

		if(!isset($this->rows[$this->rowPointer + 1])) {
			return null;
		}

		$this->rowPointer += 1;

		return $this->rows[$this->rowPointer];
	}

	/**
	 * Returns all fetched rows
	 * 
	 * @return array
	 */
	public function fetchAll()
	{
		$this->checkExecution();

		while(($row = $this->fetchRow()) != null);
		
		return $this->rows;
	}

	/**
	 * Retruns num of affected rows
	 * 
	 * @return int
	 */
	public function affectedRows()
	{
		$this->checkExecution();

		return $this->driver->affectedRows();
	}

	/**
	 * Returns columns names
	 * 
	 * @param array
	 */
	public function getColumnNames()
	{
		$names = array();
		foreach($this->cols as $col) {
			$names[] = $col[1];
		}

		return $names;
	}

	/**
	 * IteratorAggregate interface
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		$this->checkExecution();
		
		return new \ArrayIterator($this->fetchAll());
	}

	/**
	 * Countable interface
	 * 
	 * @return int
	 */
	public function count()
	{
		$this->checkExecution();
		
		return $this->driver->rowCount();
	}

	/**
	 * Checks if was sql query executed 
	 */
	protected function checkExecution()
	{
		if(!$this->executed) {
			$this->execute();
		}
	}

	/**
	 * Runs sql query, measure time
	 */
	protected function runSqlQuery()
	{
		$time = microtime(true);
		$this->driver = $this->driver->query($this->query);
		$this->executed = true;
		Db::debug($this->query, $time);
	}

	/**
	 * Loads columns informations ans counts num of tables
	 * If there is > 1 table - sets multi table mode
	 */
	protected function loadResultColumns()
	{
		$this->cols = $this->driver->getResultColomns();
		
		$tables = array();
		foreach($this->cols as $col) {
			if(!empty($col[0])) {
				$tables[$col[0]] = true;
			}
		}

		$this->tables = count($tables) > 1;
	}

	/*
	 * Combines tables with theirs columns
	 * 
	 * @param array $row table row
	 * @return array
	 */
	private function combineColumns($row)
	{
		foreach($this->cols as $i => $col) {
			if(!array_key_exists($i, $row)) {
				continue;
			}

			if(empty($col[0])) {
				$r[$col[1]] = $row[$i];
			} else {
				$r[$col[0]][$col[1]] = $row[$i];
			}
		}

		foreach($r as & $data) {
			if(is_array($data)) {
				$data = new ResultNode($data);
			}
		}

		return $r;
	}

	/**
	 * Returns one reuslt row (stored or new fetched)
	 * 
	 * @return bool
	 */
	private function getRow($assoc)
	{
		if(!empty($this->stored)) {
			$stored = $this->stored;
			$this->stored = null;
			
			return $stored;
		} else {
			return $this->driver->fetch($assoc);
		}
	}

	/**
	 * Returns one row of db result
	 * 
	 * @return DbResultNode
	 */
	protected function fetchRow()
	{
		$this->checkExecution();
		$row = $this->getRow(!$this->tables);
		if(is_null($row)) {
			return null;
		}

		if($this->tables) {
			$row = $this->combineColumns($row);

			# hasMany association
			if(!empty($this->association)) {
				# initialize hasMany table
				if(!isset($row[$this->association[1]])) {
					$row[$this->association[1]] = array();
				} else {
					$data = $row[$this->association[1]];
					if(reset($data) !== null) {
						$row[$this->association[1]] = array($data);
					} else {
						$row[$this->association[1]] = array();
					}
				}

				# add associated rows
				while(($newRow = $this->getRow(false)) !== null) {
					$this->stored = $newRow;
					$newRow = $this->combineColumns($newRow);

					if(strpos($this->association[0], '.') !== false) {
					# compare table and column
						list($t, $c) = explode('.', $this->association[0]);
						if($row[$t][$c] != $newRow[$t][$c]) {
							break;
						}

						unset($newRow[$t]);
					} else { 
					# compare table
						if(!isset($row[$this->association[0]]) || $row[$this->association[0]] != $newRow[$this->association[0]]) {
							break;
						}

						unset($newRow[$this->association[0]]);
					}
					$this->stored = null;

					# copy tables
					foreach ($newRow as $table => $data) {
						if($table != $this->association[1]) {
							# hasOne
							$row[$table] = $data;
						} else {
							# hasMany
							$row[$table][] = $data;
						}
					}
				}
			}

		}

		return $this->rows[] = new ResultNode($row);
	}
}