<?php

/*
 * @Date Dec 11, 2012
 * @Author owliber
 */
class ToolsController extends VMSBaseIdentity
{
    public $dateFrom;
    public $dateTo;
    public $advanceFilter = false;
    
    public function actionVMSLogs()
    {
        $model = new DBLogs();
        
        if(Yii::app()->request->IsAjaxRequest)
        {
            if(empty($_GET['DateFrom']) || empty($_GET['DateTo']))
            {
                $this->dateFrom = date('Y-m-d ') . '00:00';
                $this->dateTo = date('Y-m-d H:i');
            }
            else
            {
                $this->dateFrom = $_GET['DateFrom'];
                $this->dateTo = $_GET['DateTo'];
            }
            
        }    
        else
        {
            $this->dateFrom = date('Y-m-d ') . '00:00';
            $this->dateTo = date('Y-m-d H:i');
        }
        
        $vmslogs = $model->getAuditTrailByDate($this->dateFrom,$this->dateTo);
        $dataProvider = new CArrayDataProvider($vmslogs, array(
            'keyField'=>'ID',
            'pagination'=>array(
                    'pageSize'=>50,
                ),
        ));
        
        if(Yii::app()->request->IsAjaxRequest)
        {
            $this->renderPartial('_vmslogresults',array(
                'dataProvider'=>$dataProvider,
            ));
        }
        else
        {
            $this->render('vmslogs',array(
                'dataProvider'=>$dataProvider,
            )); 
        }
    }
    
    public function actionAPILogs()
    {
        $model = new DBLogs();
        
        if(Yii::app()->request->IsAjaxRequest)
        {
            if(empty($_GET['DateFrom']) || empty($_GET['DateTo']))
            {
                $this->dateFrom = date('Y-m-d ') . '00:00';
                $this->dateTo = date('Y-m-d H:i');
            }
            else
            {
                $this->dateFrom = $_GET['DateFrom'];
                $this->dateTo = $_GET['DateTo'];
            }
            
        }    
        else
        {
            $this->dateFrom = date('Y-m-d ') . '00:00';
            $this->dateTo = date('Y-m-d H:i');
        }
        
        $vmslogs = $model->getAPILogsByDate($this->dateFrom,$this->dateTo);
        $dataProvider = new CArrayDataProvider($vmslogs, array(
            'keyField'=>'LogID',
            'pagination'=>array(
                    'pageSize'=>50,
                ),
        ));
        
        if(Yii::app()->request->IsAjaxRequest)
        {
            $this->renderPartial('_apilogresults',array(
                'dataProvider'=>$dataProvider,
            ));
        }
        else
        {
            $this->render('apilogs',array(
                'dataProvider'=>$dataProvider,
            )); 
        }
    }
}
?>
