<?php

/**
 * Date Created 11 2, 11 9:38:56 AM <pre />
 * Description of ErrorController
 * @author Bryan Salazar
 */
class ErrorController extends MI_Controller{
    public $title = '';
    public $layout = 'layout/error_layout';
    
    // page not found
    public function error404Action() {
        $this->title = 'Page not found';
        if($this->isAjaxRequest()) {
            die('Page does not exist');
        } else {
            $this->render('error_404');
        }
    }
    
    // internal server error
    public function error500Action() {
        $this->title = 'Internal Server Error';
        
        
        if($this->isAjaxRequest()) {
            die('Server Error');
        } else {
            $this->render('error_500');
        }
    }
    
    // not authorize for this action
    public function error401Action() {
        if($this->isAjaxRequest()) {
            die('Session Expired');
        } else {
            $this->redirect(Mirage::app()->param['logout_page']);
        }
    }
}

