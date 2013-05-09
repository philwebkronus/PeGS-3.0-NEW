<?php

class ReimbursableVoucherController extends Controller
{
	public function actionIndex()
	{
            $model = new ReimbursableVoucherForm();
            if(isset($_POST['ReimbursableVoucherForm']))
            {
                $model->attributes=$_POST['ReimbursableVoucherForm'];
                $data=$model->attributes;
                Yii::app()->session['rvfrom'] = $data['from']. ' 06:00:00';
                Yii::app()->session['rvto'] = $data['to']. ' 06:00:00';
                
                $from = Yii::app()->session['rvfrom'];
                $to = Yii::app()->session['rvto'];
                
                if($model->validate())
                {
                    //echo 'test';
                    $rawData = $model->getReimbursableVoucher($from, $to);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                }
            }
            else
            {
                if((isset(Yii::app()->session['rvfrom']) && isset(Yii::app()->session['rvto'])) && (isset($_GET['page'])))
                {
                    $from = Yii::app()->session['rvfrom'];
                    $to = Yii::app()->session['rvto'];
                    
                    $rawData = $model->getReimbursableVoucher($from, $to);
                    Yii::app()->session['rawData'] = $rawData;
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
        
        public function actionReimbursableVoucherDataTable($rawData)
        {
            $arrayDataProvider = new CArrayDataProvider($rawData, array(
		'keyField'=>'id',
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
