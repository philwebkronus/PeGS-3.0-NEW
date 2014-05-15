<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
class OptionsController extends VMSBaseIdentity
{
    public $updateDialog = false;
    
    public $layout='//layouts/column2';
    
    public function actionManage()
    {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }
        
        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
        
        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        }
        else{
        $model = new OptionsForm();
        $rawData = $model->getAllParams();
        
        $arrayDataProvider = new CArrayDataProvider($rawData, array(
            'keyField'=>'ParamID',
        ));
        
        if(isset($_POST['Submit']) && $_POST['Submit']=='Update')
        {
            $param['ParamID'] = $_POST['ParamID'];
            $param['ParamName'] = $_POST['Name'];
            $param['ParamValue'] = $_POST['Value'];
            $param['ParamDesc'] = $_POST['Description'];
            
            $result = $model->updateParameters($param);
            
            //Log to audit trail            
            $transDetails = ' ID '.$_POST['ParamID'];
            AuditLog::logTransactions(10, $transDetails);
                   
            $this->redirect("manage");
        }
        
        $this->render('index',array(
            'arrayDataProvider'=>$arrayDataProvider,
        ));        
        
    }
    }
    
    public function actionUpdate()
    {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }
        
        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
        
        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        }
        else{
        $model = new OptionsForm();
        $rawData = $model->getAllParams();
        
        $arrayDataProvider = new CArrayDataProvider($rawData, array(
            'keyField'=>'ParamID',
        ));
        
        if(isset($_GET['ParamID']))
        {
            $this->updateDialog = true;
            $param = $model->getParamByID($_GET['ParamID']);
        }
        
        $this->render('index',array(
            'arrayDataProvider'=>$arrayDataProvider,
            'param'=>$param,
        ));
    }
    }
    
}
?>