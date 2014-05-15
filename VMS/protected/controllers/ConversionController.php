<?php

class ConversionController extends VMSBaseIdentity {

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
            $model = new SiteConversionForm();
            if (isset($_POST['SiteConversionForm'])) {
                $model->attributes = $_POST['SiteConversionForm'];
                $data = $model->attributes;
                Yii::app()->session['scfrom'] = $data['from'] . Yii::app()->params->cutofftimestart;
                Yii::app()->session['scto'] = $data['to'] . Yii::app()->params->cutofftimeend;
                Yii::app()->session['site'] = $data['site'];

                $from = Yii::app()->session['scfrom'];
                $to = Yii::app()->session['scto'];
                $site = Yii::app()->session['site'];
                if ($model->validate()) {
                    $rawData = $model->getSiteConversion($from, $to, $site);
                    Yii::app()->session['rawData'] = $rawData;

                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                }
            } else {
                if ((isset(Yii::app()->session['scfrom']) && isset(Yii::app()->session['scto'])) && (isset($_GET['page']))) {
                    $from = Yii::app()->session['scfrom'];
                    $to = Yii::app()->session['scto'];
                    $site = Yii::app()->session['site'];

                    $rawData = $model->getSiteConversion($from, $to, $site);
                    Yii::app()->session['rawData'] = $rawData;

                    $display = 'block';
                    Yii::app()->session['display'] = $display;
                } else {
                    Yii::app()->session['rawData'] = array(1);
                    //print_r(Yii::app()->session['rawData']);
                    $display = 'none';
                    Yii::app()->session['display'] = $display;
                }
            }

            $this->render('index', array('model' => $model));
        }
    }

    public function actionSiteConversionDataTable($rawData) {
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
            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                /*'id'=>'siteconversion-grid',
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
                $this->renderPartial('siteconversion', $params);
            } else {
                $this->renderPartial('siteconversion', $params);
            }
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
            $model = new SiteConversionForm();

            $rawData = Yii::app()->session['rawData'];

            $filename = "Site_Conversion_" . Date('Y_m_d');

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
