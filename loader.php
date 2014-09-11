<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

/**
 * Default loader
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 */

ob_start();
$startTime = microtime(true);

require_once dirname(__FILE__) . '/common/Object.php';
require_once dirname(__FILE__) . '/Loaders/AutoLoader.php';
require_once dirname(__FILE__) . '/Loaders/NixLoader.php';

$NixLoader = new Nix\Loaders\NixLoader();
$NixLoader->register();