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

use Nix;

/**
 * Response
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @version     2014-02-19
 * @package     Nix
 * @subpackage  Http
 */
class Response
{
	/**
	 * Sends error header
	 *
	 * @param int $code error code
	 * @return HttpResponse
	 */
	public function error($code = 404)
	{
		switch ($code) {
		case 401:
			header('HTTP/1.1 401 Unauthorized');
			break;
		case 404:
			header('HTTP/1.1 404 Not Found');
			break;
		case 500:
			header('HTTP/1.1 500 Internal Server Error');
			break;
		default:
			throw new Exception("Unsupported error code '$code'.");
			break;
		}

		return $this;
	}

	/**
	 * Sends redirect header
	 *
	 * @param string $url absolute url
	 * @param int $code redirect code
	 * @return HttpResponse
	 */
	public function redirect($url, $code = 300)
	{
		header("Location: $url", true, $code);
		return $this;
	}

	/**
	 * Sends mime-type header
	 *
	 * @param string $mime mime-type
	 * @return HttoResponse
	 */
	public function mimetype($mime)
	{
		header("Content-type: $mime");
		return $this;
	}
}