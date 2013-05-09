<?php
class UsageController extends VMSBaseIdentity
{
    public function actionIndex()
    {
        //AuditLog::logTransactions(23);
        $model = new VoucherUsageForm();
        if(isset($_POST['VoucherUsageForm']))
        {
            $model->attributes=$_POST['VoucherUsageForm'];
            $data=$model->attributes;
            Yii::app()->session['vufrom'] = $data['from']. ' 06:00:00';
            Yii::app()->session['vuto'] = $data['to']. ' 06:00:00';
            Yii::app()->session['vouchertype'] = $data['vouchertype'];
            Yii::app()->session['site'] = $data['site'];
            Yii::app()->session['status'] = $data['status'];
            
            $from = Yii::app()->session['vufrom'];
            $to = Yii::app()->session['vuto'];
            $vouchertype = Yii::app()->session['vouchertype'];
            $site = Yii::app()->session['site'];
            $status = Yii::app()->session['status'];
            
            if($model->validate())
            {
                //echo 'validate';
                $rawData = $model->getVoucherUsageStatus($from, $to, $vouchertype, $site, $status);
                Yii::app()->session['rawData'] = $rawData;
                $display = 'block';
                Yii::app()->session['display'] = $display;
            }
            /*else
                {
                    $arr = $model->getErrors();     
                    foreach ($arr as &$value) 
                    {
                        $arr2 = $value;
                        foreach ($arr2 as &$value2) 
                        {
                            //$this->displayPopup("Invalid Data", $value2,260);
                            echo $value2;
                        }
                    }
                }*/
        }
        else
        {
            $rawData = array(1);
            Yii::app()->session['rawData'] = $rawData;
            $display = 'none';
            Yii::app()->session['display'] = $display;
        }
        $this->render('index', array('model'=>$model));
    }
    public function actionVoucherUsageDataTable($rawData)
    {
        $arrayDataProvider = new CArrayDataProvider($rawData, array(
	'keyField'=>'VoucherType',
        //'id'=>'voucherusage-grid',
        /*'sort'=>array(
            'attributes'=>array('DateCreated','DateExpiry','Status'),
            'defaultOrder'=>array('DateCreated'=>true, 'DateExpiry'=>false),
            ),*/
        'pagination'=>array(
            'pageSize'=>15,
        ),
        ));
        
        $params =array(
                 'arrayDataProvider'=>$arrayDataProvider,
                    
            );
            
            if(!isset($_GET['ajax']))
            {
                  $this->renderPartial('voucherusage', $params);
            }
            else
            {
                  $this->renderPartial('voucherusage', $params);
            }
    }
    
    public function actionExportToCSV()
    {
        AuditLog::logTransactions(24);
        Yii::import('ext.ECSVExport');
        $model = new VoucherUsageForm();

        $rawData = Yii::app()->session['rawData'];

        $filename = "Voucher_Usage_".Date('Y_m_d');

        $csv = new ECSVExport($rawData);
        $csv->toCSV($filename);

        $content = file_get_contents($filename);

        Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
        exit();
    }
}
?>
