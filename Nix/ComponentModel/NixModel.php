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
 * NixModel
 *
 * Default Model for base/default application Model (MVC)
 *
 * @author      Tomas Litera    <tomaslitera@hotmail.com>
 * @copyright   2013-03-11      Tomas Litera
 * @version     1.0
 */ 
abstract class NixModel implements Nix\IModel
{
	/** @var logfile filename */
    public $logfile = 'NixModel.class.log';
    
	/** Constructor */
	public function __construct()
	{
        /**
         * in child class use this for automatic path setting to logfile by class name
         * parent::__construct();
         */
        $this->logfile = LOG_DIR."/".get_class($this).".class.log";
	}
	
	/**
	 * Create new or return existing instance of class
	 *
	 * @param   void
	 * @return	mixed	instance of class
	 */
	public static function getInstance()
	{
		if(self::$instance === false) {
			self::$instance = new self();
		}
		return self::$instance;
	}
    
    /**
     * Logging to file
     *
     * @param   string  $message    text
     * @param   string  $method     (I)nfo|(E)rror|(W)arning|
     * @return  bool
     */
    public function log($message,$method = 'I')
    {
        return error_log(date("Y-m-d H:i:s").' '.$method.' '.$message."\n", 3, $this->logfile);
    }
}
