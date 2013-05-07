<?php

class ValidationController extends VMSBaseIdentity
{
	public function actionIndex()
	{
            $model = new ValidationForm();
            if(isset($_POST['ValidationForm']))
            {
                $model->attributes = $_POST['ValidationForm'];
                $data = $model->attributes;
                Yii::app()->session['vfrom'] = $data['from'];
                Yii::app()->session['vto'] = $data['to'];
                Yii::app()->session['site'] = $data['site'];
                Yii::app()->session['terminal'] = $data['terminal'];
                Yii::app()->session['vouchercode'] = $data['vouchercode'];
                
                $from = Yii::app()->session['vfrom'];
                $to = Yii::app()->session['vto'];
                $site = Yii::app()->session['site'];
                $terminal = Yii::app()->session['terminal'];
                $vouchercode = Yii::app()->session['vouchercode'];
                
                if($model->validate())
                {
                    $rawData = $model->getVoucherValidation($from, $to, $site, $terminal, $vouchercode);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                    //print_r($rawData);
                }
            }
            else
            {
                if ((isset(Yii::app()->session['vfrom']) && isset(Yii::app()->session['vto'])) && (isset($_GET['page'])))
                {
                    $from = Yii::app()->session['vfrom'];
                    $to = Yii::app()->session['vto'];
                    $site = Yii::app()->session['site'];
                    $terminal = Yii::app()->session['terminal'];
                    $vouchercode = Yii::app()->session['vouchercode'];
                    
                    $rawData = $model->getVoucherValidation($from, $to, $site, $terminal, $vouchercode);
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                }
                else
                {
                    Yii::app()->session['rawData'] = array(1);
                    $display = 'none';
                    Yii::app()->session['display'] = $display; 
                }
            }
            
            
            $this->render('index', array('model'=>$model));
	}
        
        public function actionValidationDataTable($rawData)
        {
           $arrayDataProvider = new CArrayDataProvider($rawData, array(
                /*'id'=>'vouchers-grid',
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
                  $this->renderPartial('validation', $params);
            }
            else
            {
                  $this->renderPartial('validation', $params);
            }
        }
        
        public function actionAjaxGetTerminal($site)
        {
            $model = new VoucherMonitoringForm();
            $terminal = $model->getTerminal($site);
            echo $terminal;
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