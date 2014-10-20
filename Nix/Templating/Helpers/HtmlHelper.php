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
use Nix\Forms\Html;

/**
 * HtmlHelper
 *
 * @author      Tomas Litera 	<tomaslitera@hotmail.com>
 * @package     Nix
 * @subpackage  Templating
 */
class HtmlHelper extends Nix\Object
{
	/**
	 * Constructor
	 *
	 * @param 	Template 	$template 	Template object
	 * @param 	string 		$varName 	variable name
	 * @return HtmlHelper
	 */
	public function  __construct(Template $template = null, $varName = null)
	{
		if($template) {
			if(empty($varName)) {
				$varName = 'html';
			}

			static $functions = array('analytics', 'encoding', 'css', 'icon',
				'js', 'paginator', 'rss');

			foreach($functions as $f) {
				$template->tplFunctions[$f] = "\${$varName}->$f";
			}
		}
	}

	/**
	 * Returns HTML link
	 * If text is null, then as the title is used link url
	 *
	 * @param string $url url
	 * @param string $text link text
	 * @param array $attrs html attributes
	 * @param bool $escape escape link content
	 * @return string
	 */
	public function link($url, $text = null, $attrs = array(), $escape = true)
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('a')->setAttrs($attrs)->href($url);

		if($escape) {
			$el->setText($text === null ? $url : $text);
		} else {
			$el->setHtml($text === null ? $url : $text);
		}

		return $el->render();
	}

	/**
	 * Returns HTML form button
	 * If text is null, then as the title is used link url
	 *
	 * @param string $url url
	 * @param string $text link text
	 * @param array $attrs html attributes
	 * @param bool $escape escape link content
	 * @return string
	 */
	public function button($url, $text, $attrs = array(), $escape = false)
	{
		$form = Html::el('form', null, array(
			'action' => $this->factoryUrl($url),
			'class' => 'button',
		));

		$button = Html::el('button');
		$button->type('submit');
		if($escape) {
			$button->addText($text);
		} else {
			$button->addHtml($text);
		}

		return $form->setAttrs($attrs)->addHtml($button)->render(false);
	}

	/**
	 * Returns HTML image
	 *
	 * @param string $url
	 * @param array $attrs html attributes
	 * @return string
	 */
	public function img($url, $attrs = array())
	{
		$url = $this->factoryUrl($url);
		return Html::el('img')->setAttrs($attrs)->src($url)->render();
	}

	/**
	 * Returns HTML css-external tag
	 *
	 * @param string $file url
	 * @param string $media media type
	 * @param bool $timestamp append timestamp?
	 * @return string
	 */
	public function css($file, $media = 'screen', $timestamp = true)
	{
		$url = $this->factoryUrl($file);
		$el = Html::el('link')->rel('stylesheet')
		                      ->type('text/css')
		                      ->media($media);

		if($timestamp) {
			$file = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
			$time = @filemtime($file);
			if($time !== false) {
				$url .= '?' . $time;
			}
		}

		return $el->href($url)->render(0);
	}

	/**
	 * Returns HTML js-external tag
	 *
	 * @param string $file url
	 * @param bool $timestamp append timestamp?
	 * @return string
	 */
	public function js($file, $timestamp = true)
	{
		$url = $this->factoryUrl($file);
		$el = Html::el('script')->type('text/javascript');

		if($timestamp) {
			$file = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $file;
			$time = @filemtime($file);
			if($time !== false) {
				$url .= '?' . $time;
			}
		}

		return $el->src($url)->render(0);
	}

	/**
	 * Returns HTML rss link tag
	 *
	 * @param string $url
	 * @param string $title rss title
	 * @return string
	 */
	public function rss($url, $title = 'RSS')
	{
		$url = $this->factoryUrl($url);
		$el = Html::el('link')->rel('alternate')
		                      ->type('application/rss+xml')
		                      ->href($url)
		                      ->title($title);

		return $el->render(0);
	}

	/**
	 * Returns HTML favicon tag
	 *
	 * @param string $url
	 * @return string
	 */
	public function icon($url)
	{
		$url = $this->factoryUrl($url);
		return "<link rel=\"shortcut icon\" href=\"$url\" />\n";
	}

	/**
	 * Returns HTML encoding-header tag
	 *
	 * @param string $charset
	 * @return string
	 */
	public function encoding($charset = 'UTF-8')
	{
		return "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$charset\" />\n";
	}

	/**
	 * Returns tracking code for Google Analytics
	 *
	 * @param string $id
	 * @return string
	 */
	public function analytics($id)
	{
		return "<script type=\"text/javascript\">\n"
		     . "var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");\n"
		     . "document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));\n"
		     . "</script>\n<script type=\"text/javascript\">\n"
		     . "try {\nvar pageTracker = _gat._getTracker(\"$id\");\n"
		     . "pageTracker._trackPageview();\n} catch(err) {}</script>\n";
	}

	/**
	 * Renders paginator
	 *
	 * @param Paginator $paginator
	 * @param string $urlParamName
	 * @param int $round page aroung the current
	 * @param string $prev link text for previous page
	 * @param string $next link text for next page
	 * @return string
	 */
	public function paginator(Paginator $paginator, $urlParamName = 'page', $round = 2, $prev = 'Previous', $next = 'Next')
	{
		$pages = array();
		if($paginator->pages > 0) {
			$pages[1] = true;
			if($paginator->pages > 1) {
				$pages[2] = true;
			}
		}

		$from = max($paginator->page - $round, 1);
		$to = min($paginator->page + $round, $paginator->pages);
		for($i = $from; $i <= $to; $i++) {
			$pages[$i] = true;
		}

		if($paginator->pages > 2) {
			$pages[max($paginator->pages - 1, 1)] = true;
			$pages[max($paginator->pages, 1)] = true;
		}

		$_prev = 0;
		$pagination = array();
		foreach(array_keys($pages) as $page) {
			if($_prev != $page - 1) {
				$pagination[] = '-';
			}

			$pagination[] = $_prev = $page;
		}

		$render = '<div class="pagination">';
		if($paginator->hasPrev()) {
			$url = $this->url(null, null, array($urlParamName => $paginator->page - 1));
			$render .= "<a href=\"$url\">&laquo; $prev</a>";
		} else {
			$render .= "<span class=\"button\">&laquo; $prev</span>";
		}

		foreach($pagination as $page) {
			if(is_int($page)) {
				$url = $this->url(null, null, array($urlParamName => $page));
				$class = $page == $paginator->page ? ' active' : '';
				$render .= "<a href=\"$url\" class=\"page-link$class\">$page</a>";
			} else {
				$render .= '<span class="hellip">&hellip;</span>';
			}
		}

		if($paginator->hasNext()) {
			$url = $this->url('', null, array($urlParamName => $paginator->page + 1));
			$render .= "<a href=\"$url\">$next &raquo;</a>";
		} else {
			$render .= "<span class=\"button\">$next &raquo;</span>";
		}

		$render .= '</div>';
		return $render;
	}

	/**
	 * Processes the framework url
	 *
	 * @param string $url url
	 * @param array $args rewrite args
	 * @param array|false $params rewrite params
	 * @return string
	 */
	protected function url($url, $args = array(), $params = false)
	{
		if(class_exists('Application', false)) {
			return Controller::get()->url($url, $args, $params);
		} else {
			return frameworkUrl($url, $args, $params);
		}
	}

	/**
	 * Returns parsed and sanitized URL
	 *
	 * @param string $url
	 * @return string
	 */
	protected function factoryUrl($url)
	{
		if(substr($url, 0, 4) == 'www.') {
			$url = "http://$url";
		}

		if(strpos($url, '://') === false) {
			$url = $this->url($url);
		}

		return $url;
	}
}