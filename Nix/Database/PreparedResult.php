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

use Nix\Database\Result,
	Nix\Utils\Paginator;

/**
 * Prepared result class
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Database
 */
class PreparedResult extends Result
{
	/** @var Paginator */
	public $paginator;

	/**
	 * Sets sql order
	 * 
	 * @param mixed $order
	 * @return DbResult
	 */
	public function setOrder($order)
	{	
		if(!empty($order)) {
			$this->query .= ' ORDER BY ' . $order;
		}

		return $this;
	}

	/**
	 * Sets pagination
	 * 
	 * @param int $pate page
	 * @param int $limit limit (default = 10)
	 * @param int $count count pages
	 * @return DbPreparedResult
	 */
	public function setPagination($page, $limit = 10, $count = null)
	{
		if($this->executed) {
			throw new \Exception('You can not paginate excecuted query.');
		}

		if(empty($count)) {
			$query = preg_replace('#select(.+)from#si', 'SELECT COUNT(*) FROM', $this->query, -1, $c);
			if($c < 1) {
				throw new \Exception('Unsuccessful sql replacement for pagination. Provide your count of entries.');
			}

			$count = db::fetchField($query);
		}

		if($page < 1) {
			$page = 1;
		}

		$this->query .= ' LIMIT ' . ($page - 1) * $limit . ', ' . $limit;

		require_once dirname(__FILE__) . '/../Utils/Paginator.php';

		$this->paginator = new Paginator($page, $count, $limit);

		return $this;
	}

	/**
	 * Sets association
	 * 
	 * @param string $main main table name
	 * @param string $hasMany table which is in relation hasMany
	 * @return DbPreparedResult
	 */
	public function setAssociation($main, $hasMany)
	{
		$this->association[0] = $main;
		$this->association[1] = (string) $hasMany;
		
		return $this;
	}
}