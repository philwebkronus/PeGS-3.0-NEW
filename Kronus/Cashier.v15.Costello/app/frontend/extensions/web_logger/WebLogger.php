<?php

/**
 * Date Created 12 27, 11 11:46:33 AM
 * Description of WebLogger
 * @package 
 * @author Bryan Salazar <brysalazar12@gmail.com>
 */
class WebLogger extends MI_Controller{
    public function log() {
        if($this->isAjaxRequest())
            return;
        $queries = MI_Model::getCachedQuery();
        include_once 'views/web_logger_tpl.php';
    }
}

