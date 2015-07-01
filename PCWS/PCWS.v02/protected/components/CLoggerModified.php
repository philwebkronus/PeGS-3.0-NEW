<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CLoggerModified {
    
    const ERROR = 'error';
    const WARNING = 'warning';
    const REQUEST = 'request';
    const RESPONSE = 'response';
   
    public static function log($message,$type='error'){
        
        Yii::log($message,$type);
    }
    
}

?>
