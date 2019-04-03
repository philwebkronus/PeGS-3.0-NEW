<?php

/**
 * Date Created 10 28, 11 1:48:46 PM <pre />
 * Description of MI_Database
 * @author Bryan Salazar
 */
class MI_Database {

    public static $dbh;
    public static $dbh2;
    public static $dbh3;
    public static $dbh4;
    public static $dbh5;
    public static $dbh6;

    /*     * **************************************************************************
     * Original Author: Bryan Salazar
     * Date Creation: May 31, 2011
     * Description: create instance of model, PDO and connect to database
     * ************************************************************************* */

    public function __construct($config) {
        if (!isset(self::$dbh) || self::$dbh == null) {
            try {
                self::$dbh = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }

        if (!isset(self::$dbh2) || self::$dbh2 == null) {
            try {
                self::$dbh2 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh2->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }

        if (!isset(self::$dbh3) || self::$dbh3 == null) {
            try {
                self::$dbh3 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh3->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }

        if (!isset(self::$dbh4) || self::$dbh4 == null) {
            try {
                self::$dbh4 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh4->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }


        if (!isset(self::$dbh5) || self::$dbh5 == null) {
            try {
                self::$dbh5 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh5->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }


        if (!isset(self::$dbh6) || self::$dbh6 == null) {
            try {
                self::$dbh6 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh6->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh) || self::$dbh == null) {
            try {
                self::$dbh = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect2($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh2) || self::$dbh2 == null) {
            try {
                self::$dbh2 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh2->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect3($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh3) || self::$dbh3 == null) {
            try {
                self::$dbh3 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh3->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect4($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh4) || self::$dbh4 == null) {
            try {
                self::$dbh4 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh4->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect5($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh5) || self::$dbh5 == null) {
            try {
                self::$dbh5 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh5->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    public static function connect6($config) {
        //,PDO::ATTR_EMULATE_PREPARES, false
        // PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION
        if (!isset(self::$dbh6) || self::$dbh6 == null) {
            try {
                self::$dbh6 = new PDO(
                        $config['connection_string'], $config['username'], $config['password']
                );
                self::$dbh6->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch (PDOException $e) {
                MI_Logger::log($e->getMessage(), E_ERROR);
                Mirage::app()->error500();
            }
        }
    }

    /*     * **************************************************************************
     * Original Author: Bryan Salazar
     * Date Creation: May 31, 2011
     * Description: When model is destroyed it will automatically disconnect
     *              to database
     * ************************************************************************* */

    public function __destruct() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh = NULL;
    }

    public function __destruct2() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh2 = NULL;
    }

    public function __destruct3() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh3 = NULL;
    }

    public function __destruct4() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh4 = NULL;
    }

    public function __destruct5() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh5 = NULL;
    }

    public function __destruct6() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh6 = NULL;
    }

    public static function close() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh = null;
    }

    public static function close2() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh2 = null;
    }

    public static function close3() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh3 = null;
    }

    public static function close4() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh4 = null;
    }

    public static function close5() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh5 = null;
    }

    public static function close6() {
        gc_enable();
        gc_collect_cycles();
        self::$dbh6 = null;
    }

}

