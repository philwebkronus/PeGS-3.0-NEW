<?php

/**
 * Launchpad module has standalone config file for modularity
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */

class LPConfig {
    
    /**
     *
     * @var LPConfig
     */
    private static $_instance = null;
    private $_configloc = '../config/main.php';
    /**
     *
     * @var type 
     */
    public $params;
    
    
    private function __construct() {
        return $this->setConfigFile($this->_configloc);
    }


    /**
     *
     * @return LPConfig 
     */
    public static function app()
    {
        if(self::$_instance == null)
            self::$_instance = new LPConfig();
        return self::$_instance;
    }
    
    /**
     * Set the configuration file
     * @param string $file 
     */
    public function setConfigFile($file)
    {
        $this->params = include_once $file;
        if(!is_array($this->params))
            throw new Exception("Config should return array '$file'");    
    }
    
}

