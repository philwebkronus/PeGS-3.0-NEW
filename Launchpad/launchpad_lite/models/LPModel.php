<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'LPConfig.php';
include_once '../Helper/Logger.class.php';

class LPModel{
    
    private static $_logdir;
    public $_params;

    public function setpdoconn($dsn,$un,$pw){
        return new PDO($dsn,$un,$pw);
    }

    public function logerror($message, $errortype = "ERROR"){
        $logger = new Logger(LPConfig::app()->params['logpath']);
        $logger->logger($message);
    }
    
}

?>
