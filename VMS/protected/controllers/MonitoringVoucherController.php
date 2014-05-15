<?php

class MonitoringVoucherController extends VMSBaseIdentity {
    
    public $showDialog = false;
    public $dialogMsg;
    public $hasError;
    public $voucher;
    public $hasResult = false;
    
    //Entry point in monitoring of Ticket voucher
    public function actionTicket()
    {
        $model = new MonitoringVoucherForm();

        $rawdata    = array();
        $option     = array();
        $option[]   = array('VoucherID' => 1, 'VoucherName' => 'Ticket');
        //Provide list data
        $vouchers = CHtml::listData($option, 'VoucherID', 'VoucherName');
        if (isset($_POST['MonitoringVoucherForm']))
        {
            $rawdata = $this->monitorVouchers($_POST['MonitoringVoucherForm']);
            Yii::app()->session['rawData'] = $rawdata;
        }
        if (count($rawdata) > 0)
        {
            $this->hasResult = true;
        }
        else
        {
            $this->hasResult = false;
        }
        $headertitle = "Monitoring of Tickets";
        $this->render('index', array('model' => $model, 'vouchers' => $vouchers, 'rawdata' => $rawdata, 'title' => $headertitle));
    }
    //Entry point in monitoring of Coupon voucher
    public function actionCoupon()
    {
        $model = new MonitoringVoucherForm();
        
        $rawdata    = array();
        $option     = array();
        $option[]   = array('VoucherID' => 2, 'VoucherName' => 'Coupons');
        //Provide list data
        $vouchers = CHtml::listData($option, 'VoucherID', 'VoucherName');
        
        if (isset($_POST['MonitoringVoucherForm']))
        {
            $rawdata = $this->monitorVouchers($_POST['MonitoringVoucherForm']);
            Yii::app()->session['rawData'] = $rawdata;
        }
        if (count($rawdata) > 0)
        {
            $this->hasResult = true;
        }
        else
        {
            $this->hasResult = false;
        }
        $headertitle = "Monitoring of Coupons";
        $this->render('index', array('model' => $model, 'vouchers' => $vouchers, 'rawdata' => $rawdata, 'title' => $headertitle));
    }
    /****************************************
     * Monitoring of Voucher process
     */
    public function monitorVouchers($postvars)
    {
        $model = new MonitoringVoucherForm();
        
        $rawdata = array();
        
        $model->attributes = $postvars;
        $data = $model->attributes;
        $vouchertype    = $data['vouchertype'];
        $datefrom       = $data['datefrom'];
        $dateto         = $data['dateto'];

        Yii::app()->session['vouchertype'] = $vouchertype;
        if($vouchertype > 0){
/*******************************************TICKETS**********************************************************/
            //check if vouchertype is ticket
            if($vouchertype == 1)
            {
                if ($datefrom != "" && $dateto != "")
                {
                    $vouchercount = $model->getAllTicketCount($datefrom, $dateto);

                    $activeticketcount = $model->getTicketCount(1, $datefrom, $dateto);
                    $queuedticketcount = $model->getTicketCount(1, $datefrom, $dateto, 1);

                    $voidticketcount = $model->getTicketCount(2, $datefrom, $dateto);
                    $usedticketcount = $model->getTicketCount(3, $datefrom, $dateto);
                    $encashmentticketcount = $model->getTicketCount(4, $datefrom, $dateto);
                    $cancelledticketcount = $model->getTicketCount(5, $datefrom, $dateto);
                    $reimbursedticketcount = $model->getTicketCount(6, $datefrom, $dateto);
                    $expiredticketcount = $model->getTicketCount(7, $datefrom, $dateto);

                    $activeticketcounts = (float)$activeticketcount;
                    $queuedticketcounts = (float)$queuedticketcount;
                    $voidticketcounts = (float)$voidticketcount;
                    $usedticketcounts = (float)$usedticketcount;
                    $encashmentticketcounts = (float)$encashmentticketcount;
                    $cancelledticketcounts = (float)$cancelledticketcount;
                    $reimbursedticketcounts = (float)$reimbursedticketcount;
                    $expiredticketcounts = (float)$expiredticketcount;
                    $vouchercounts = (float)$vouchercount;
                    //ActiveTicket Count
//                    if ($activeticketcounts > 0)
//                    {
//                        $activepercentage = round(($activeticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $activepercentage = 0;
//                    }
//                    //Active (Queued)
//                    if ($queuedticketcounts > 0)
//                    {
//                        $queuedpercentage = round(($queuedticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $queuedpercentage = 0;
//                    }
//                    //Void Tickets
//                    if ($voidticketcounts > 0)
//                    {
//                        $voidpercentage = round(($voidticketcounts/$vouchercounts)*100,2); 
//                    }
//                    else
//                    {
//                        $voidpercentage = 0;
//                    }
//                    //Used Percentage
//                    if ($usedticketcounts > 0)
//                    {
//                         $usedpercentage = round(($usedticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $usedpercentage = 0;
//                    }
//                    //Encashment Percentage
//                    if ($encashmentticketcounts > 0)
//                    {
//                        $encashmentpercentage = round(($encashmentticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $encashmentpercentage = 0;
//                    }
//                    //Cancelled Tickets
//                    if ($cancelledticketcounts > 0)
//                    {
//                         $cancelledpercentage = round(($cancelledticketcounts/$vouchercounts)*100,2); 
//                    }
//                    else
//                    {
//                        $cancelledpercentage = 0;
//                    }
//                    //Reimbursed Tickets
//                    if ($reimbursedticketcounts > 0)
//                    {
//                        $reimbursedpercentage = round(($reimbursedticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $reimbursedpercentage = 0;
//                    }
//                    //Expired Tickets
//                    if ($expiredticketcounts > 0)
//                    {
//                        $expiredpercentage = round(($expiredticketcounts/$vouchercounts)*100,2);
//                    }
//                    else
//                    {
//                        $expiredpercentage = 0;
//                    }
                    $rawdata = array(
//                                    array(
//                                        'Status' => 'Active (Queued)',
//                                        'Count' => number_format($queuedticketcounts),
//                                        //'Percentage' => $queuedpercentage.' %',
//                                    ),
                                    array(
                                        'Status' => 'Active',
                                        'Count' => number_format($activeticketcounts),
                                        //'Percentage' => $activepercentage.' %',
                                    ),
                                    array(
                                        'Status' => 'Void',
                                        'Count' => number_format($voidticketcounts),
                                        //'Percentage' => $voidpercentage.' %',
                                    ),
                                    array(
                                        'Status' => 'Used',
                                        'Count' => number_format($usedticketcounts),
                                        //'Percentage' => $usedpercentage.' %',
                                    ),
                                    array(
                                        'Status' => 'Encashed',
                                        'Count' => number_format($encashmentticketcounts),
                                        //'Percentage' => $encashmentpercentage.' %',
                                    ),
//                                    array(
//                                        'Status' => 'Cancelled',
//                                        'Count' => number_format($cancelledticketcounts),
//                                        //'Percentage' => $cancelledpercentage.' %',
//                                    ),
                                    array(
                                        'Status' => 'Reimbursed',
                                        'Count' => number_format($reimbursedticketcounts),
                                        //'Percentage' => $reimbursedpercentage.' %',
                                    ),
                                    array(
                                        'Status' => 'Expired',
                                        'Count' => number_format($expiredticketcounts),
                                        //'Percentage' => $expiredpercentage.' %',
                                    ),
                                    array(
                                        'Status' => 'Total',
                                        'Count' => number_format($vouchercounts),
                                        //'Percentage' => '100 %',
                                    ),
                                );
                }
                else
                {
                    $this->hasError = true;
                    $errorcode = 1; //Please enter date from/to
                }
            }
/*******************************************COUPONS**********************************************************/
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
                                    'Status' => 'Inactive',
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
            if ($this->hasError)
            {
                if ($errorcode == 1)
                {
                    $this->showDialog = true;
                    $this->dialogMsg = "Please enter From/To Date";
                }
            }
            else
            {
                $display = 'block';
                Yii::app()->session['display'] = $display;
            }
        }
        else{
            $rawdata = array();
        }
       return $rawdata;     
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
