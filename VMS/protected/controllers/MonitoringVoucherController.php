<?php

class MonitoringVoucherController extends VMSBaseIdentity {
    
    public $showDialog = false;
    public $dialogMsg;

    public function actionIndex() {
        $model = new MonitoringVoucherForm();
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
        $display = 'none';
        Yii::app()->session['display'] = $display;
             
        if(isset($_POST['MonitoringVoucherForm']))
        {
            $model->attributes = $_POST['MonitoringVoucherForm'];
            $data = $model->attributes;
            $vouchertype = $data['vouchertype'];
            
            Yii::app()->session['vouchertype'] = $vouchertype;
            if($vouchertype > 0){
            
            //check if vouchertype is ticket
            if($vouchertype == 1){
                
                $vouchercount = $model->getAllTicketCount();
                
                $activeticketcount = $model->getTicketCount(1);
                $voidticketcount = $model->getTicketCount(2);
                $usedticketcount = $model->getTicketCount(3);
                $encashmentticketcount = $model->getTicketCount(4);
                $cancelledticketcount = $model->getTicketCount(5);
                $reimbursedticketcount = $model->getTicketCount(6);
                
                $activeticketcounts = (float)$activeticketcount;
                $voidticketcounts = (float)$voidticketcount;
                $usedticketcounts = (float)$usedticketcount;
                $encashmentticketcounts = (float)$encashmentticketcount;
                $cancelledticketcounts = (float)$cancelledticketcount;
                $reimbursedticketcounts = (float)$reimbursedticketcount;
                $vouchercounts = (float)$vouchercount;

                $activepercentage = round(($activeticketcounts/$vouchercounts)*100,2);
                $voidpercentage = round(($voidticketcounts/$vouchercounts)*100,2); 
                $usedpercentage = round(($usedticketcounts/$vouchercounts)*100,2);
                $encashmentpercentage = round(($encashmentticketcounts/$vouchercounts)*100,2);
                $cancelledpercentage = round(($cancelledticketcounts/$vouchercounts)*100,2); 
                $reimbursedpercentage = round(($reimbursedticketcounts/$vouchercounts)*100,2);
                
                $rawdata = array(
                                array(
                                    'Status' => 'Active',
                                    'Count' => number_format($activeticketcounts),
                                    'Percentage' => $activepercentage.' %',
                                ),
                                array(
                                    'Status' => 'Void',
                                    'Count' => number_format($voidticketcounts),
                                    'Percentage' => $voidpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Used',
                                    'Count' => number_format($usedticketcounts),
                                    'Percentage' => $usedpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Encashment',
                                    'Count' => number_format($encashmentticketcounts),
                                    'Percentage' => $encashmentpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Cancelled',
                                    'Count' => number_format($cancelledticketcounts),
                                    'Percentage' => $cancelledpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Reimbursed',
                                    'Count' => number_format($reimbursedticketcounts),
                                    'Percentage' => $reimbursedpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Total',
                                    'Count' => number_format($vouchercounts),
                                    'Percentage' => '100 %',
                                ),
                            );
            }
            else{
                //coupons
                $vouchercount = $model->getAllCouponCount();
                
                $inactivecouponcount = $model->getCouponCount(0);
                $activecouponcount = $model->getCouponCount(1);
                $deactivatedcouponcount = $model->getCouponCount(2);
                $usedcouponcount = $model->getCouponCount(3);
                $cancelledcouponcount = $model->getCouponCount(4);
                $reimbursedcouponcount = $model->getCouponCount(5);
                
                $inactivecouponcounts = (float)$inactivecouponcount;
                $activecouponcounts = (float)$activecouponcount;
                $deactivatedcouponcounts = (float)$deactivatedcouponcount;
                $usedcouponcounts = (float)$usedcouponcount;
                $cancelledcouponcounts = (float)$cancelledcouponcount;
                $reimbursedcouponcounts = (float)$reimbursedcouponcount;
                $vouchercounts = (float)$vouchercount;

                $inactivepercentage = round(($inactivecouponcounts/$vouchercounts)*100,2);
                $activepercentage = round(($activecouponcounts/$vouchercounts)*100,2); 
                $deactivatedpercentage = round(($deactivatedcouponcounts/$vouchercounts)*100,2);
                $usedpercentage = round(($usedcouponcounts/$vouchercounts)*100,2);
                $cancelledpercentage = round(($cancelledcouponcounts/$vouchercounts)*100,2); 
                $reimbursedpercentage = round(($reimbursedcouponcounts/$vouchercounts)*100,2);
                
                $rawdata = array(
                                array(
                                    'Status' => 'InActive',
                                    'Count' => number_format($inactivecouponcounts),
                                    'Percentage' => $inactivepercentage.' %',
                                ),
                                array(
                                    'Status' => 'Active',
                                    'Count' => number_format($activecouponcounts),
                                    'Percentage' => $activepercentage.' %',
                                ),
                                array(
                                    'Status' => 'Deactivated',
                                    'Count' => number_format($deactivatedcouponcounts),
                                    'Percentage' => $deactivatedpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Used',
                                    'Count' => number_format($usedcouponcounts),
                                    'Percentage' => $usedpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Cancelled',
                                    'Count' => number_format($cancelledcouponcounts),
                                    'Percentage' => $cancelledpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Reimbursed',
                                    'Count' => number_format($reimbursedcouponcounts),
                                    'Percentage' => $reimbursedpercentage.' %',
                                ),
                                array(
                                    'Status' => 'Total',
                                    'Count' => number_format($vouchercounts),
                                    'Percentage' => '100 %',
                                ),
                            );
            }
             Yii::app()->session['rawData'] = $rawdata;
             $display = 'block';
             Yii::app()->session['display'] = $display;
                 
            }
            else{
                $rawdata = array();
                Yii::app()->session['rawData'] = $rawdata;
            }
            
        }
        else{
            $rawdata = array();
            Yii::app()->session['rawData'] = $rawdata;
        }
        
        
        $this->render('index', array('model' => $model));
        }
    }
    
    public function actionMonitoringVoucherDataTable($rawData) {
        
            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                /*'id'=>'reimbursablevoucher-grid',
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

            if (Yii::app()->request->IsAjaxRequest) {
                $this->renderPartial('monitoringvoucher', $params);
            } else {
                $this->renderPartial('monitoringvoucher', $params);
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
            AuditLog::logTransactions(26);
            Yii::import('ext.ECSVExport');

            $rawData = Yii::app()->session['rawData'];

            //$currentdir = dirname(__FILE__) . '/';
            //$rootdir = realpath($currentdir . '../') . '/';
            
            if(Yii::app()->session['vouchertype'] == 1){
                $filename = "Monitoring_of_Voucher_Tickets_" . Date('Y_m_d') . ".csv";
            }
            else{
                $filename = "Monitoring_of_Voucher_Coupons_" . Date('Y_m_d') . ".csv";
            }
            

            $csv = new ECSVExport($rawData);

            $csv->toCSV($filename);

            $content = file_get_contents($filename);

            Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
            exit();
            //unlink($filename.'csv');
        }
    }
    
}
?>
