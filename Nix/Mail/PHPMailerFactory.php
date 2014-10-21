<?php

/**
 * This file is part of the Nix Framework
 *
 * Copyright (c) 2014 Tomáš Litera
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace Nix\Mail;

/**
 * PHPMailerFactory
 *
 * factory to create instance of PHPMAiler
 *
 * @created 2012-10-08
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class PHPMailerFactory
{
	/** @var PHPMailer instance of PHPMailer */
	public $PHPMailer;
	
	/** @var array of configuration */
	private $configuration;
	
	/**
	 * Constructor
	 * @param PHPMailer $PHPMailer     instance of PHPMailer
	 * @param array     $configuration configuration options
	 */
	public function __construct(PHPMailer $PHPMailer, $configuration)
	{
		$this->PHPMailer = $PHPMailer;
		$this->configuration = $configuration;
	}
	
	/**
	 * Return new PHPMailer with few settings
	 *
	 * @return	PHPMailer;
	 */
	public function create()
	{
		// use PHP function mail()
		//$this->PHPMailer->IsMail();
		// enable SMTP
		$this->PHPMailer->IsSMTP();
		// debugging: 1 = errors and messages, 2 = messages only
		$this->PHPMailer->SMTPDebug = 1;
		// authentication enabled
		$this->PHPMailer->SMTPAuth = true;
		// secure transfer enabled REQUIRED for GMail
		$this->PHPMailer->SMTPSecure = 'ssl';
		// smtp server
		$this->PHPMailer->Host = 'smtp.gmail.com';
		// port
		$this->PHPMailer->Port = 465; 
		// user + password
		$this->PHPMailer->Username = $this->configuration['gmail_user'];  
		$this->PHPMailer->Password = $this->configuration['gmail_passwd'];            
		// sender name and address
		$this->PHPMailer->SetFrom($this->configuration['mail-sender-address'], $this->configuration['mail-sender-name']);
		// e-mail is in HTML format
		$this->PHPMailer->IsHTML(true);
		// encoding
		$this->PHPMailer->CharSet = $this->configuration['mail-encoding'];
		// language
		$this->PHPMailer->SetLanguage($this->configuration['mail-language']);
		// sender e-mail address
		//$this->PHPMailer->From = $this->configuration['mail-sender-address'];
		// sender name
		//$this->PHPMailer->FromName = $this->configuration['mail-sender-name'];
		
		return $this->PHPMailer;
	}
}