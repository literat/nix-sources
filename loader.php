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
require_once dirname(__FILE__) . '/Loaders/NixLoader.php';

$NixLoader = new Nix\Loaders\NixLoader();
$NixLoader->register();

/**
 * Processes the framework url
 *
 * @param string $url url
 * @param array $args rewrite args
 * @param array|false $params rewrite params
 * @return string
 */
function frameworkUrl($url, $args = array(), $params = false) {
	if(empty($url)) {
		$url = Http::$request->request;
	} else {
		$url = preg_replace('#\<\:([a-z0-9]+)\>#ie', "isset(\$args['\1']) ? \$args['\1'] : ''", $url);
	}

	if($params !== false) {
		$p = array();
		$params = array_merge($_GET, (array) $params);
		foreach($params as $name => $value) {
			if ($value == null) continue;
			$p[] = urlencode($name) . '=' . urlencode($value);
		}

		if(!empty($p)) {
			$url .= '?' . implode('&', $p);
		}
	}

	return Http::$baseURL . '/' . ltrim($url, '/');
}