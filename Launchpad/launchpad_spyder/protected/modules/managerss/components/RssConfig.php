<?php

/**
 * Class for setter and getter of config for managess module
 * @package application.modules.managerss.components
 * @author Bryan Salazar
 */
class RssConfig {
    
    /**
     *
     * @var array 
     */
    public $params;
    
    /**
     *
     * @var RssConfig 
     */
    private static $_instance;
    
    /**
     *
     * @return RssConfig 
     */
    public static function app() {
        if(self::$_instance == NULL)
            self::$_instance = new RssConfig();
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
