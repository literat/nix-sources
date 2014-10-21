<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix;

use Nix;

/**
 * IComponent
 *
 * interface for default application component (MVC)
 *
 * @created 2012-12-16
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
interface IComponent extends Nix\IModel
{
	/**
	 * Create a new record
	 *
	 * @param  array   $dbData data from database
	 * @return boolean
	 */
	public function create(array $dbData);

	/**
	 * Modify record
	 *
	 * @param	int    $id      ID of record
	 * @param	array  $dbData  Associated array with data to DB
	 * @return	bool
	 */	
	public function update($id, array $dbData);
	
	/**
	 * Delete record
	 *
	 * @param	int      $id  ID of record
	 * @return	boolean 
	 */
	public function delete($id);
	
	/**
	 * Render data in a table
	 *
	 * @return	string	html of a table
	 */
	public function renderData();
}