<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Utils;

use Nix,
	Nix\Object;

/**
 * Paginator
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Utils
 */
class Paginator extends Object
{
	/** @var int page */
	protected $page;

	/** @var int pages */
	protected $pages;

	/** @var bool if has preview */
	protected $hasPrev;

	/** @var bool if has newxt */
	protected $hasNext;

	/**
	 * Constructor
	 * 
	 * @param int $page current page
	 * @param int $total total pages count
	 * @param int $limit limit of records per page
	 * @return Paginator
	 */
	public function __construct($page, $total, $limit)
	{
		$pages = ceil($total / $limit);
		$page = (int) $page;
		$this->page = $page < 1 ? 1 : $page;
		$this->pages = (int) $pages;
		$this->hasPrev = $this->page > 1;
		$this->hasNext = $this->page < $this->pages;
	}

	/**
	 * Returns current page num
	 * 
	 * @return int
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Returns count of pages
	 * 
	 * @return int
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * Returns true when exists previous page
	 * 
	 * @return bool
	 */
	public function hasPrev()
	{
		return $this->hasPrev;
	}

	/**
	 * Returns true when exists next page
	 * 
	 * @return bool
	 */
	public function hasNext()
	{
		return $this->hasNext;
	}

	/**
	 * Returns true when current page is the first
	 * 
	 * @return bool
	 */
	public function isFirst()
	{
		return !$this->hasPrev;
	}

	/**
	 * Returns true when current page is the last
	 * 
	 * @return bool
	 */
	public function isLast()
	{
		return !$this->hasNext;
	}
}