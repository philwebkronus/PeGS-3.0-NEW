<?php

/**
 * Database handler so it will not conflict if it will interrate with 
 * other module that use different connection. For standalone purpose
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class LPDB extends CDbConnection{
    
    /**
     *
     * @var LPDB 
     */
    private static $_instance = null;


    /**
     * Get instance of LPDB
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @return LPDB 
     */
    public static function app($dsn='', $username='', $password='')
    {
        if(self::$_instance == null)
            self::$_instance = new LPDB ($dsn, $username, $password);
        return self::$_instance;
    }
    
    /**
     * Creates a command for execution.
     * @param mixed $query the DB query to be executed. This can be either a string representing a SQL statement,
     * or an array representing different fragments of a SQL statement. Please refer to {@link CDbCommand::__construct}
     * for more details about how to pass an array as the query. If this parameter is not given,
     * you will have to call query builder methods of {@link CDbCommand} to build the DB query.
     * @return LPCommand the DB command
     */    
    public function createCommand($query = null)
    {
        $this->setActive(true);
        return new LPCommand($this,$query);
    }
}
