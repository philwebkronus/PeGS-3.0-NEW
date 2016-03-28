<?php
Yii::import('application.extensions.curl.Curl');
/**
 * Curl Controller - extend Curl extendsion to override url
 *
 * @author elperez
 */
 
class CurlController extends Curl{
   
    public function run($url,$GET = TRUE,$POSTSTRING = array())
    {
        //$wsurl = Yii::app()->params['wsURL'].$url;
        return parent::run($url,$GET,$POSTSTRING);
    }
}

?>
