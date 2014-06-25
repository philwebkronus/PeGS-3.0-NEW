<?php

/**
 * @Description: For Commonly used functions
 * @Author: aqdepliyan
 * @DateCreated: 2014-03-21 10:29 AM
 */

class Utilities {
    
    public function logger($message, $errortype = "ERROR"){
        Yii::log($message, $errortype);
    }
    
}

?>
