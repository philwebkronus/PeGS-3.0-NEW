<?php

/**
 * Description of Controller
 * @package application.modules.managerss.components
 * @author Bryan Salazar
 */
class Controller extends CController{
    public $pageTitle = 'MANAGE RSS';
    
    
    /**
     * This method will be run before the requested action
     * @param object $action
     * @return boolean 
     */
    protected function beforeAction($action) {
        
        // checking if session id not equal to database or not exist in database then force logout
        if(!Yii::app()->user->isGuest) {
            $row = RssAccountSessions::model()->getSessionIDByAID(Yii::app()->user->getState('aid'));
            if($row == false) {
                    Yii::app()->user->logout();
            } elseif(isset($row['SessionID']) && $row['SessionID'] != Yii::app()->session->getSessionID()) {
                Yii::app()->user->logout();
            }
        }

        // redirect to login page if guest and requested controller is not equal to auth and requested action not equal to feed
        if(Yii::app()->user->isGuest && Yii::app()->getController()->id != 'auth' && $action->id != 'feed') {
            
            // check if request type is ajax then do not show the layout
            if(Yii::app()->request->isAjaxRequest)
                $this->redirect(Yii::app()->createUrl('managerss/auth/login',array('isajax'=>1)));
            else
                $this->redirect(Yii::app()->createUrl('managerss/auth/login'));
            
        // check if already login. if requested controller is auth and requested action is login then redirect to managerss/rss/overview    
        }elseif(!Yii::app()->user->isGuest && Yii::app()->getController()->id == 'auth' && $action->id == 'login') {
            $this->redirect(Yii::app()->createUrl('managerss/rss/overview'));
        }
        return parent::beforeAction($action);
    }
}
