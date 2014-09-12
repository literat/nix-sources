<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Templating\Helpers;

use Nix,
	Nix\Templating\Template;

/**
 * FilterHelper
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Templating
 */
class FilterHelper extends Nix\Object
{
	/**
	 * Constructor
	 *
	 * @param Template $template
	 * @param string $varName
	 * @return FilterHelper
	 */
	public function __construct(Template $template = null, $varName = null)
	{
		if($template) {
			if(empty($varName)) {
				$varName = 'filter';
			}

			static $filters = array('bytes', 'escape', 'lower', 'upper',
				'strip', 'stripTags', 'date', 'ldate', 'format', 'shuffle',
				'explode', 'implode', 'truncate');

			foreach($filters as $f) {
				$template->tplFilters[$f] = "\${$varName}->$f";
			}

			$template->tplFunctions['translate'] = "\${$varName}->translate";
			$template->tplFunctions['__'] = "\${$varName}->translate";
		}
	}

	/**
	 * Escape filter
	 *
	 * @param string $var
	 * @param string $mode escaping mode - html/js/css
	 * @return string
	 */
	public function escape($var, $mode = 'html')
	{
		switch ($mode) {
			case 'js':
			case 'javascript':
				$var = str_replace(']]>', ']]\x3E', json_encode($s));
				return htmlspecialchars($var, ENT_QUOTES);
			case 'css':
				$var = addcslashes($s, "\x00..\x2C./:;<=>?@[\\]^`{|}~");
				return htmlspecialchars($var, ENT_QUOTES);
			case 'html':
			case 'xhtml':
			default:
				return htmlspecialchars($var, ENT_QUOTES);
		}
	}

	/**
	 * Lower (strtolower) filter
	 *
	 * @param string $var
	 * @return string
	 */
	public function lower($var)
	{
		return strtolower($var);
	}

	/**	
	 * Upper (strtoupper) filter
	 *
	 * @param string $var
	 * @return string
	 */
	public function upper($var)
	{
		return strtoupper($var);
	}

	/**
	 * Strip filter - strips (white) spaces
	 *
	 * @param string $var
	 * @param string $replace
	 * @return string
	 */
	public function strip($var, $replace = ' ')
	{
		return preg_replace('#\s*#', $replace, $var);
	}

	/**
	 * Strip tags filter
	 *
	 * @param string $var
	 * @return string
	 */
	public function stripTags($var)
	{
		return strip_tags($var);
	}

	/**
	 * Date filter
	 *
	 * @param int|string $var
	 * @param string $format
	 * @return string
	 */
	public function date($var, $format = 'd.m.Y')
	{
		if(!is_int($var)) {
			$var = strtotime($var);
		}

		return date($format, $var);
	}

	/**
	 * Date filter with localization
	 *
	 * @param int|string $var
	 * @param string $format
	 * @return string
	 */
	public function ldate($var, $format = '%d. %B %Y')
	{
		if(!is_int($var)) {
			$var = strtotime($var);
		}

		return strftime($format, $var);
	}

	/**
	 * Format (sprintf) filter
	 *
	 * @param string $var
	 * @param string $format
	 * @return string
	 */
	public function format($var, $format)
	{
		return sprintf($format, $var);
	}

	/**
	 * Shuffle filter
	 *
	 * @param array $var
	 * @return array
	 */
	public function shuffle($var)
	{
		shuffle($var);
		return $var;
	}

	/**
	 * Explode filter
	 *
	 * @param string $var
	 * @param string $delimeter
	 * @return array
	 */
	public function explode($var, $delimeter = ', ')
	{
		return explode($delimeter, $var);
	}

	/**
	 * Implode filter
	 *
	 * @param array $var
	 * @param string $delitemter
	 * @return string
	 */
	public function implode($var, $delimeter = ', ')
	{
		return implode($delimeter, $var);
	}

	/**
	 * Converts bytes to human readable file size
	 *
	 * @param int $bytes
	 * @param int $precision
	 * @return string
	 */
	public function bytes($bytes, $precision = 2)
	{
		static $s = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
		$e = floor(log($bytes) / log(1024));
		return sprintf('%.' . $precision . 'f ' . $s[$e], ($bytes / pow(1024, floor($e))));
	}

	/**
	 * Truncates to maximal length
	 *
	 * @param string $string
	 * @param int $len maximal length
	 * @param string $append
	 * @return string
	 */
	public function truncate($string, $len, $append = "\xE2\x80\xA6")
	{
		if(strlen($string) <= $len) {
			return $string;
		}

		$string = rtrim(rtrim(substr($string, 0, $len)), '.-');
		if(preg_match('#[a-z0-9]$#i', $string)) {
			$string .= $append;
		} else {
			$string .= ' ' . $append;
		}

		return $string;
	}

	/**
	 * Translates expression, optionally in plural form
	 *
	 * @param string $string translation key
	 * @param string $domain
	 * @return string
	 */
	public function translate()
	{
		$args = func_get_args();
		$nargs = func_num_args();
		if($nargs <= 2) {
			return call_user_func_array(array('L10n', '__'), $args);
		} else {
			return call_user_func_array(array('L10n', '__n'), $args);
		}
	}
}