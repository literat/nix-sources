<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Exporting;

/**
 * PdfFactory
 *
 * factory to create instance of mPDF
 *
 * @created 2013-02-18
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class PdfFactory
{
	/** @var Pdf instace of Pdf */
	public $Pdf;

	/** @var array of configuration */
	private $configuration;

	/** @var string encoding */
	private $encoding = 'utf-8';

	/** @var string paper format */
	private $paperFormat = 'A4';

	/** @var int font size */
	private $fontSize = 0;

	/** @var string font name */
	private $font = '';

	/** @var int margin left */
	private $marginLeft = 15;

	/** @var int margin right */
	private $marginRight = 15;

	/** @var int margin top */
	private $marginTop = 16;

	/** @var int margin bottom */
	private $marginBottom = 16;

	/** Constructor */
	public function __construct()
	{
	}
	
	/**
	 * Return new Mpdf with few settings
	 *
	 * @return	Mpdf;
	 */
	public function create()
	{
		$this->Pdf = new mPdf($this->encoding,
							  $this->paperFormat,
							  $this->fontSize,
							  $this->font,
							  $this->marginLeft,
							  $this->marginRight,
							  $this->marginTop,
							  $this->marginBottom
							 );
		
		// debugging on demand
		if(defined('DEBUG') && DEBUG === TRUE){
			$this->Pdf->debug = true;
		}
		$this->Pdf->useOnlyCoreFonts = true;
		$this->Pdf->SetDisplayMode('fullpage');
		$this->Pdf->SetAutoFont(0);
		$this->Pdf->defaultfooterfontsize = 16;
		$this->Pdf->defaultfooterfontstyle = 'B';
		
		return $this->Pdf;
	}
	
	/**
	 * Set margins of PDF
	 * 
	 * @param  int  $left    left margin
	 * @param  int  $right   right margin
	 * @param  int  $top     top margin
	 * @param  int  $bottom  bottom margin
	 * @return void
	 */
	public function setMargins($left, $right, $top, $bottom)
	{
		$this->marginLeft = $left;
		$this->marginRight = $right;
		$this->marginTop = $top;
		$this->marginBottom = $bottom;
	}

	/**
	 * Set paper format of PDF
	 * 
	 * @param  string $paper_format paper format
	 * @return void
	 */
	public function setPaperFormat($paper_format)
	{
		$this->paperFormat = $paper_format;
	}
}