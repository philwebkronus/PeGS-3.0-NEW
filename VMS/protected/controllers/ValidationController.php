<?php

class ValidationController extends VMSBaseIdentity {

    public function actionIndex() {
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
        } else {
            Yii::app()->session['disable'] = true;
            $model = new ValidationForm();
            if (isset($_POST['ValidationForm'])) {
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

                if ($model->validate()) {
                    $rawData = $model->getVoucherValidation($from, $to, $site, $terminal, $vouchercode);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                    //print_r($rawData);
                }
            } else {
                if ((isset(Yii::app()->session['vfrom']) && isset(Yii::app()->session['vto'])) && (isset($_GET['page']))) {
                    $from = Yii::app()->session['vfrom'];
                    $to = Yii::app()->session['vto'];
                    $site = Yii::app()->session['site'];
                    $terminal = Yii::app()->session['terminal'];
                    $vouchercode = Yii::app()->session['vouchercode'];

                    $rawData = $model->getVoucherValidation($from, $to, $site, $terminal, $vouchercode);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                } else {
                    $from = Yii::app()->session['vfrom'];
                    $to = Yii::app()->session['vto'];
                    $site = Yii::app()->session['site'];
                    $terminal = Yii::app()->session['terminal'];
                    $vouchercode = Yii::app()->session['vouchercode'];

                    $rawData = $model->getVoucherValidation($from, $to, $site, $terminal, $vouchercode);
                    Yii::app()->session['rawData'] = $rawData;
                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                }
            }
            
            
            $this->render('index', array('model'=>$model));
	}
        
        
    }
    
    public function actionValidationDataTable($rawData)
        {
           $arrayDataProvider = new CArrayDataProvider($rawData, array(
                /*'id'=>'vouchers-grid',
                'sort'=>array(
                    'attributes'=>array('DateCreated','DateExpiry','Status'),
                    'defaultOrder'=>array('DateCreated'=>true, 'DateExpiry'=>false),
                    ),*/
                'keyField'=>false,
                'pagination'=>array(
                    'pageSize'=>10,
                ),
            ));
            $params = array(
                'arrayDataProvider' => $arrayDataProvider,
            );

            if (!isset($_GET['ajax'])) {
                $this->renderPartial('validation', $params);
            } else {
                $this->renderPartial('validation', $params);
            }
        }

    public function actionAjaxGetTerminal($site) {
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
        } else {
        $model = new VoucherMonitoringForm();
        $terminal = $model->getTerminal($site);
        echo $terminal;
        }
    }
    
    public function actionExportToCSV() {
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
        } else {
            Yii::import('ext.ECSVExport');

            $rawData = Yii::app()->session['rawData'];

            $filename = "Voucher_Validation_" . Date('Y_m_d');

            $csv = new ECSVExport($rawData);
            $csv->toCSV($filename);

            $content = file_get_contents($filename);

            Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
            exit();
        }
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
