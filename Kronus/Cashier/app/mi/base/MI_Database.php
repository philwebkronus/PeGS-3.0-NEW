<?php

/**
 * Date Created 10 28, 11 1:48:46 PM <pre />
 * Description of MI_Database
 * @author Bryan Salazar
 */
class MI_Database {
    public static $dbh;
    
   /****************************************************************************
    * Original Author: Bryan Salazar
    * Date Creation: May 31, 2011
    * Description: create instance of model, PDO and connect to database
    ***************************************************************************/
    public function __construct($config) {
        if(!isset(self::$dbh) || self::$dbh == null) {
            try {
                self::$dbh = new PDO(
                                      $config['connection_string'],
                                      $config['username'],
                                      $config['password']
                                   );
                self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }
    
    public static function connect($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if(!isset(self::$dbh) || self::$dbh == null) {
            try {
                self::$dbh = new PDO(
                                      $config['connection_string'],
                                      $config['username'],
                                      $config['password']
                                   );
                self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }
   
    /****************************************************************************
    * Original Author: Bryan Salazar
    * Date Creation: May 31, 2011
    * Description: When model is destroyed it will automatically disconnect
    *              to database
    ***************************************************************************/
    public function __destruct() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh = NULL;
    }
   
    public static function close() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh = null;
    }
}

