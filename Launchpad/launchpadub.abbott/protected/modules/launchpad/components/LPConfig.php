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
    
    /**
     *
     * @var type 
     */
    public $params;
    
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
            throw new CException("Config should return array '$file'");    
    }
    
    
}
