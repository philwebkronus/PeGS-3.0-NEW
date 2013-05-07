<?php

/**
 * Description of ErrorController
 * @package application.modules.managerss.controllers
 * @author Bryan Salazar
 */
class ErrorController extends CController 
{
    public $layout = 'main';
    
    public function actionError()
    {
        if(Yii::app()->request->isAjaxRequest)
            throw new CHttpException (404, 'Page not found');
        else {
            $this->render('error');
        }
    }
}
