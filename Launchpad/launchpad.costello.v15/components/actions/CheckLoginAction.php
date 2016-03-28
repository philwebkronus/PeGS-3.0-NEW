<?php

/**
 * This class is a action for cheking if login. This action nca only be access
 * through ajax request
 * @package application.modules.launchpad.components.actions
 * @author Bryan Salazar
 */
class CheckLoginAction extends CAction
{
    /**
     * Action for cheking if login and will echo a json
     * <code>
     * {"status":"ok"} or {"status":"not ok"}
     * </code>
     */
    public function run() 
    {
        if(!Yii::app()->request->isAjaxRequest)
            throw new CHttpException(404,"Invalid request");
        
        $identity = LPUserIdentity::app();
        
        header('Content-type: application/json'); // set header json
        
        // check if authenticate
        if($identity->authenticate()) {
            // login
            Yii::app()->user->login($identity);
//            Yii::app()->user->setState('terminalCode',$identity->terminalCode);
            Yii::app()->user->setState('currServiceID',$identity->serviceID);
            Yii::app()->user->setState('terminalID',$identity->terminalID);
            Yii::app()->user->setState('siteID',$identity->siteID);
            Yii::app()->user->setState('transSummaryID',$identity->transSummaryID);
            //Yii::app()->user->setState('terminalPassword', 'pass1');
            echo CJSON::encode(array('status'=>'ok'));
        } else {
            // logout
            Yii::app()->user->logout();
            echo CJSON::encode(array('status'=>'not ok'));
        }       
        Yii::app()->end();
    }
}