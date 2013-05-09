<?php

class ReimbursablevoucherController extends VMSBaseIdentity
{
        public $showDialog = false;
        public $dialogMsg;
    
	public function actionIndex()
	{
            AuditLog::logTransactions(25);
            $model = new ReimbursableVoucherForm();
            Yii::app()->session['disable'] = true;
            if(isset($_POST['ReimbursableVoucherForm']))
            {
                $model->attributes=$_POST['ReimbursableVoucherForm'];
                $data=$model->attributes;
                Yii::app()->session['rvfrom'] = $data['from']. ' 06:00:00';
                Yii::app()->session['rvto'] = $data['to']. ' 06:00:00';
                Yii::app()->session['site'] = $data['site'];
                Yii::app()->session['terminal'] = $data['terminal'];
                
                $from = Yii::app()->session['rvfrom'];
                $to = Yii::app()->session['rvto'];
                $site = Yii::app()->session['site'];
                $terminal = Yii::app()->session['terminal'];
                //print_r($_POST['ReimbursableVoucherForm']['from']);
                if($model->validate())
                {
                    if(isset($_POST['yt0']))
                    {
                        $rawData = $model->getReimbursableVoucher($from, $to, $site, $terminal);
                        Yii::app()->session['rawData'] = $rawData;
                        $display = 'block';
                        Yii::app()->session['display'] = $display;
                        Yii::app()->session['disable'] = false;
                    }
                    else
                    {
                        if(isset($_POST['forreimburse']))
                        {
                            $forreimburse = $_POST['forreimburse'];
                            $reimburselist = array();
                            foreach($forreimburse as $value)
                            {
                                $reimburselist[] = "'".$value."'";
                            }
			    Yii::app()->session['reimburselist'] = $reimburselist;
                            $vouchercode = implode(",",$reimburselist);
                            Yii::app()->session['disable'] = false;

                            //echo $r;
                            $model->getReimburseVoucher($vouchercode);
                            $rawData = $model->getReimbursableVoucher($from, $to, $site, $terminal);
                            Yii::app()->session['rawData'] = $rawData;
                            $this->showDialog = true;
                            $this->dialogMsg = "Successfully Reimburse Voucher(s)!";
                        }
                        else
                        {
                            Yii::app()->session['disable'] = false;
                            $this->showDialog = true;
                            $this->dialogMsg = "No Vouchers selected!";
                        }
                    }
                }
            }
            else
            {
                if((isset(Yii::app()->session['rvfrom']) && isset(Yii::app()->session['rvto'])) && (isset($_GET['page'])))
                {
                    $from = Yii::app()->session['rvfrom'];
                    $to = Yii::app()->session['rvto'];
                    $site = Yii::app()->session['site'];
                    $terminal = Yii::app()->session['terminal'];
                    
                    $rawData = $model->getReimbursableVoucher($from, $to, $site, $terminal);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                    Yii::app()->session['disable'] = false;
                }
                else
                {
                    Yii::app()->session['rawData'] = array(1);
                    $display = 'none';
                    Yii::app()->session['display'] = $display;
                    Yii::app()->session['disable'] = true;
                }
            }
            
            $this->render('index', array('model'=>$model));
	}
        
        public function actionReimbursableVoucherDataTable($rawData)
        {
            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                /*'id'=>'reimbursablevoucher-grid',
                'sort'=>array(
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
                  $this->renderPartial('reimbursablevoucher', $params);
            }
            else
            {
                  $this->renderPartial('reimbursablevoucher', $params);
            }
        }
        
        public function actionAjaxGetTerminal($site)
        {
            $model = new ReimbursableVoucherForm();
            $terminal = $model->getTerminal($site);
            echo $terminal;
        }
        
        public function actionExportToCSV()
        {
            AuditLog::logTransactions(26);
            Yii::import('ext.ECSVExport');
            $model = new ReimbursableVoucherForm();
            
            $rawData = Yii::app()->session['rawData'];
            
            //$currentdir = dirname(__FILE__) . '/';
            //$rootdir = realpath($currentdir . '../') . '/';
            $filename = "Reimbursable_Vouchers_".Date('Y_m_d').".csv";
            
            $csv = new ECSVExport($rawData);
            
            $csv->toCSV($filename);
            
            $content = file_get_contents($filename);
            
            Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
            exit();
            //unlink($filename.'csv');
        }

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}
