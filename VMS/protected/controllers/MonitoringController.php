<?php

class MonitoringController extends VMSBaseIdentity
{
	public function actionIndex()
	{
                $issubmitted = 0;
                if(Yii::app()->session['AccountType'] == 4)
                {
                    $siteinfo = Utilities::getSiteInfo();
                    $sitecode = $siteinfo[0]['SiteCode'];
                    Yii::app()->session['SiteCode'] = $sitecode;
                }
                $model = new VoucherMonitoringForm();
                if(isset($_POST['VoucherMonitoringForm']))
                {
                    
                    $model->attributes=$_POST['VoucherMonitoringForm'];
                    $data=$model->attributes;
                    Yii::app()->session['from'] = $data['from'];
                    Yii::app()->session['to'] = $data['to'];
                    Yii::app()->session['status'] = $data['status'];
                    Yii::app()->session['site'] = $data['site'];
                    Yii::app()->session['terminal'] = $data['terminal'];
                    Yii::app()->session['vouchercode'] = $data['vouchercode'];
                    
                    $from = Yii::app()->session['from'];
                    $to = Yii::app()->session['to'];
                    $status = Yii::app()->session['status'];
                    $site = Yii::app()->session['site'];
                    $terminal = Yii::app()->session['terminal'];
                    $vouchercode = Yii::app()->session['vouchercode'];
                    
                    if($model->validate())
                    {
                        $issubmitted = 1;
                        $rawData = $model->getVouchersByRangeStatus($from, $to, $status, $site, $terminal, $vouchercode);
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
                    if ((isset(Yii::app()->session['from']) && isset(Yii::app()->session['to'])) && (isset($_GET['page'])))
                    {
                        $issubmitted = 1;
                        
                        $from = Yii::app()->session['from'];
                        $to = Yii::app()->session['to'];
                        $status = Yii::app()->session['status'];
                        $site = Yii::app()->session['site'];
                        $terminal = Yii::app()->session['terminal'];
                        $vouchercode = Yii::app()->session['vouchercode'];
                        $rawData = $model->getVouchersByRangeStatus($from, $to, $status, $site, $terminal, $vouchercode);
                        Yii::app()->session['rawData'] = $rawData;
                        //print_r(Yii::app()->session['rawData']);
                        $display = 'block';
                        Yii::app()->session['display'] = $display;
                    }
                    else
                    {
                        
                        $issubmitted = 0;
                        //$rawData = array('0'=>array('2012-01-01','2012-01-02','All','All','All','1'));
                        //Yii::app()->session['rawData'] = $rawData;
                        Yii::app()->session['rawData'] = array(1);
                        $display = 'none';
                        Yii::app()->session['display'] = $display; 
                    }
                }
                
                $this->render('index', array('model'=>$model,'issubmitted'=>$issubmitted));
		
	}
        
        public function actionDataTable($rawData)
        {
            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                //'id'=>'vouchers-grid',
                'sort'=>array(
                    'attributes'=>array('DateCreated','DateExpiry','Status'),
                    'defaultOrder'=>array('DateCreated'=>true, 'DateExpiry'=>false),
                    ),
                'pagination'=>array(
                    'pageSize'=>15,
                ),
            ));
            $params =array(
                    'arrayDataProvider'=>$arrayDataProvider,
                    
            );
            
            if(!isset($_GET['ajax']))
            {
                  $this->renderPartial('vouchermonitoringdatatable', $params);
            }
            else
            {
                  $this->renderPartial('vouchermonitoringdatatable', $params);
            }
        }
        
        public function actionAjaxGetTerminal($site)
        {
            $model = new VoucherMonitoringForm();
            $terminal = $model->getTerminal($site);
            echo $terminal;
        }
        
        /*public function actionGetTerminal()
        {
            $site = $_POST['site'];
            
            $model = new VoucherReportForm();
            $terminal = $model->getTerminal($site);
            
            foreach($terminal as $k=>$v)
            {
                echo CHtml::tag('option', array('value'=>$k),CHtml::encode($v),true);
            }
        }*/
}