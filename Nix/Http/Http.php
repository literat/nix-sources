<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Http;

use Nix,
	Nix\Http\Request,
	Nix\Http\Response;

/**
 * Http
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Http
 */
class Http
{
	/** @var string domain name */
	public static $domain;

	/** @var string server url */
	public static $serverURL;

	/** @var string base url */
	public static $baseURL;
 
	/** @var HttpRequest request */
	public static $request;

	/** @var HttpResponse request */
	public static $response;
 
	/**
	 * Initializes HTTP class
	 */
	public function __construct()
	{
		self::sanitizeData();
		self::$domain = $_SERVER['SERVER_NAME'];
		// use http or secure https
		self::$serverURL = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . '://' . self::$domain;

		$base = trim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
		if(!empty($base)) {
			self::$baseURL = "/$base";
		}

		self::$request = new Request();
		self::$response = new Response();
	}
 
	/**
	 * Sanitizes superglobal variables ($_GET, $_POST, $_COOKIE a $_REQUEST)
	 */
	public static function sanitizeData()
	{
		if(!get_magic_quotes_gpc()) {
			return;
		}

		$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		while(list($key, $val) = each($process)) {
			foreach($val as $k => $v) {
				unset($process[$key][$k]);
				if(is_array($v)) {
					$process[$key][$k] = $v;
					$process[] = & $process[$key][$k];
				} else {
					$process[$key][$k] = stripslashes($v);
				}
			}
		}

		unset($process);
	}
}