<?php

/*
 * @Date Feb 1, 2013
 * @Author owliber
 * 
 */
?>

<?php

class UsageController extends VMSBaseIdentity
{
    public $dateFrom;
    public $dateTo;
    public $status;
    public $vouchertype = 'All';
    public $egmmachine = 'All';
    public $site = 'All';
    
    public function actionIndex()
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
        //unset(Yii::app()->session['from']);
        //unset(Yii::app()->session['to']);
        //print_r(Yii::app()->session['from']);
        $model = new VoucherUsageForm();
        if(isset($_POST['VoucherUsageForm']))
        {
            $model->attributes=$_POST['VoucherUsageForm'];
            $data=$model->attributes;
            Yii::app()->session['vufrom'] = $data['from']. Yii::app()->params->cutofftimestart;
            Yii::app()->session['vuto'] = $data['to']. Yii::app()->params->cutofftimeend;
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
            //$rawData = $model->getVoucherUsageStatus(Yii::app()->session['from'], Yii::app()->session['to'], Yii::app()->session['vouchertype'], Yii::app()->session['site'], Yii::app()->session['status']);
            //$rawData = array('0'=>array('2012-01-01', '2012-01-02', 'All', 'All', 'All'));
            $rawData = array(1);
            Yii::app()->session['rawData'] = $rawData;
            $display = 'none';
            Yii::app()->session['display'] = $display;
        }
        $this->render('index', array('model'=>$model));
    }
    }
    
    public function actionVoucherUsageDataTable($rawData)
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
        $arrayDataProvider = new CArrayDataProvider($rawData, array(
        //'id'=>'voucherusage-grid',
        /*'sort'=>array(
            'attributes'=>array('DateCreated','DateExpiry','Status'),
            'defaultOrder'=>array('DateCreated'=>true, 'DateExpiry'=>false),
            ),*/
        'keyField'=>false,
        'pagination'=>array(
            'pageSize'=>10,
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
    }
    
    public function actionAjaxVoucherUsage()
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
        if(Yii::app()->request->isAjaxRequest)
        {
            $this->dateFrom = $_GET['DateFrom'];
            $this->dateTo = $_GET['DateTo'];
            
            if(Yii::app()->user->isPerSite())
                $this->site = Yii::app()->user->getSiteID();
            else
                $this->site = $_GET['Site'];
            
            if($this->egmmachine == 'All')
            {
                $egmmachines = Stacker::activeEGMMachinesBySite($this->site);

                foreach($egmmachines as $value)
                {
                    $egmmachine[] = $value['EGMMachineInfoId_PK'];
                }

                $egmmachines = $egmmachine;

            }
            else
                $egmmachines = $this->egmmachine;
        
            $model = new Usage();
            
            $voucherusage = $model->getUsage($this->dateFrom, $this->dateTo, $this->vouchertype, $this->status, $egmmachines);

            $dataProvider = new CArrayDataProvider($voucherusage, array(
                'keyField'=>'VoucherID',
                'pagination'=>array(
                    'pageSize'=>10,
                ),
            ));

            $this->renderPartial('_lists',array(
                'dataProvider'=>$dataProvider,
            ));

            Yii::app()->end();
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

            $rawData = Yii::app()->session['rawData'];

            $filename = "Voucher_Usage_" . Date('Y_m_d');

            $csv = new ECSVExport($rawData);
            $csv->toCSV($filename);

            $content = file_get_contents($filename);

            Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
            exit();
        }
    }
}
?>