<?php
include 'BaseProcess.php';
/**
 * Date Created 09 28, 11 7:41:45 PM
 * Description</b> of ProcessTopUpPaginate
 * @author Bryan Salazar
 * modified by Edson L. Perez
 */


class ProcessTopUpPaginate extends BaseProcess {
    
    /**
     * Gross Hold Monitoring Page Overview, pass list of sites on this page
     */
    public function grossHoldMonitoring() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $param['sitCode'] = $topup->getSiteCodeList();
        $this->render('topup/topup_gross_hold_monitoring',$param);
        $topup->close();
    }
    
    public function grossHoldMonitoring2() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $param['sitCode'] = $topup->getSiteCodeList();
        $this->render('topup/topup_gross_hold_monitoring2',$param);
        $topup->close();
    }
    
    /**
     * Gross Hold Monitoring Page Overview, pass list of sites on this page
     */
    public function pagcorGrossHoldMonitoring() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $param['sitCode'] = $topup->getSiteCodeList();
        $this->render('topup/pagcor_gross_hold_monitoring',$param);
        $topup->close();
    }
    
    //pagination for Gross Hold Monitoring
    public function getdata() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        
        $startdate = date('Y-m-d')." ".BaseProcess::$cutoff;

        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate']." ".BaseProcess::$cutoff;
        
//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
//            
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        
        // to check if greater than 1 day
        // since this program must support current cut-off,
        // all dates GT or LT current cut-off
        // must not permitted to retrieve data     
        
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $dir = $_GET['sord'];
        $sort = "POSAccountNo";

        if(strlen($_GET['sidx']) > 0){
            $sort = $_GET['sidx'];
        }
        ob_get_clean();
        
        //array containing complete details
        $rows = $topup->grossHoldMonitoring($sort, $dir, $startdate,$enddate); 
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '220');
        $arrdetails = array();
        foreach($rows as $id => $row) {
            $gross_hold = ((($row['Deposit'] + $row['Reload']) - ($row['Redemption'])) - $row['ActualAmount']);
            $cashonhand =((($row['DepositCash'] + $row['ReloadCash']) - $row['RedemptionCashier']) - $row['ActualAmount']) - $row["EncashedTickets"];
            $endingbalance = ($cashonhand+$row['Replenishment']) - $row['Collection'];
            $temp = array(
                        "SiteName"=>$row['SiteName'],
//                        "SiteCode"=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                        "MinBalance" => $row["MinBalance"],
                        "POS"=>$row['POSAccountNo'],
                        "BCF"=>number_format($row['BCF'],2),
                        "Deposit"=>number_format($row['Deposit'],2),
                        "EwalletLoad"=>number_format($row['EwalletLoads'],2),
                        "Reload"=>number_format($row['Reload'],2),
                        "Withdrawal"=>number_format($row['Redemption'],2),
                        "EwalletWithdrawal"=>number_format($row['EwalletWithdrawal'],2),
                        "ManualRedemption"=>(($row['ActualAmount'])?number_format($row['ActualAmount'],2):''),
                        "PrintedTickets"=>number_format($row['PrintedTickets'],2),
                        "UnusedTickets"=>number_format($row['UnusedTickets'],2),
                        "RunningActiveTickets"=>number_format($row['RunningActiveTickets'],2),
                        "Coupon"=>number_format($row['Coupon'],2),
                        "CashonHand"=>number_format($cashonhand,2),
                        "GrossHold"=>number_format($gross_hold, 2),
                        "Replenishment"=>$row['Replenishment'],
                        "Collection"=>$row['Collection'],
                        "EndingBalance"=>$endingbalance,
                        "Location"=>$row['Location'],
                    );        
            
                    if($_GET['sellocation'] != '') {
                        if($row['Location'] != $_GET['sellocation']) {
                            continue;
                        }
                    }            

            //check the amount range
            if(isset($_GET['comp1']) && isset($_GET['comp2']) && $_GET['comp1'] != '' && $_GET['comp2'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $val2 = str_replace(',', '', $_GET['num2']);
                $comp1 = $_GET['comp1'];
                $comp2 = $_GET['comp2'];
                if(eval("return \$gross_hold $comp1 \$val1;") && eval("return \$gross_hold $comp2 \$val2;")) {
                    $arrdetails[] = $temp;
                }
            } else if(isset($_GET['comp1']) && $_GET['comp1'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $comp1 = $_GET['comp1'];
                if(eval("return \$gross_hold $comp1 \$val1;")) {                    
                    $arrdetails[] = $temp;
                }               
            } else {
                $arrdetails[] = $temp;
            }
        }

        $arrdetails["CountOfSites"] = count($arrdetails);
        echo json_encode($arrdetails);
        $topup->close();
        unset($rows, $startdate, $enddate, $arrdetails, $temp);
        exit;
    }
    
    
    public function getdata2() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        
        $startdate = date('Y-m-d')." ".BaseProcess::$cutoff;

        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate']." ".BaseProcess::$cutoff;
        
//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
//            
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        
        // to check if greater than 1 day
        // since this program must support current cut-off,
        // all dates GT or LT current cut-off
        // must not permitted to retrieve data     
        
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $dir = $_GET['sord'];
        $sort = "POSAccountNo";

        if(strlen($_GET['sidx']) > 0){
            $sort = $_GET['sidx'];
        }
        ob_get_clean();
        
        //array containing complete details
        $rows = $topup->grossHoldMonitoring($sort, $dir, $startdate,$enddate); 
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '220');
        $arrdetails = array();

        foreach($rows as $id => $row) {
            $gross_hold = ((($row['Deposit'] + $row['Reload'] + $row['EwalletLoads']) - ($row['Redemption'] - $row['EwalletWithdrawal'])) - $row['ActualAmount']);
            /****************Grosshold Monitoring V1 Computation***********************/
            if ($startdate < BaseProcess::$deploymentdate) {
                $cashonhand = (((($row['DepositCash'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['Coupon']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['ActualAmount']) - $row["EncashedTickets"] - $row['EwalletWithdrawal'];
                $endingbalance = ($cashonhand + $row['Replenishment']) - $row['Collection'];    
            }
            /****************Grosshold Monitoring V2 Computation***********************/
            else {
                $cashonhand = (((($row['DepositCash'] + $row['ReloadCash'] + $row['EwalletCashLoads'] + $row['Coupon'] + $row['LoadTickets']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['ActualAmount']) - $row["EncashedTicketsV2"] - $row['EwalletWithdrawal'];
                $endingbalance = ($cashonhand + $row['Replenishment'] ) - $row['Collection'];
            }
//            if($row['SiteID'] == 167){
//                var_dump($row['Replenishment'],$row['Collection'],$endingbalance, $cashonhand, $row['DepositCash'],$row['ReloadCash'],$row['EwalletCashLoads'],$row['RedemptionCashier'],$row['ActualAmount'],$row["EncashedTickets"], $row['EwalletWithdrawal']);exit;
//            }
            $temp = array(
                        "SiteName"=>$row['SiteName'],
//                        "SiteCode"=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                        "MinBalance" => $row["MinBalance"],
                        "POS"=>$row['POSAccountNo'],
                        "BCF"=>number_format($row['BCF'],2),
                        "Deposit"=>number_format($row['Deposit'],2),
                        "EwalletLoad"=>number_format($row['EwalletLoads'],2),
                        "Reload"=>number_format($row['Reload'],2),
                        "Withdrawal"=>number_format($row['Redemption'],2),
                        "EwalletWithdrawal"=>number_format($row['EwalletWithdrawal'],2),
                        "ManualRedemption"=>(($row['ActualAmount'])?number_format($row['ActualAmount'],2):''),
                        "PrintedTickets"=>number_format($row['PrintedTickets'],2),
                        "UnusedTickets"=>number_format($row['UnusedTickets'],2),
                        "RunningActiveTickets"=>number_format($row['RunningActiveTickets'],2),
                        "Coupon"=>number_format($row['Coupon'],2),
                        "CashonHand"=>number_format($cashonhand,2),
                        "GrossHold"=>number_format($gross_hold, 2),
                        "Replenishment"=>number_format($row['Replenishment'], 2),
                        "Collection"=>  number_format($row['Collection'],2),
                        "EndingBalance"=>number_format($endingbalance,2),
                        "Location"=>$row['Location'],
                    );        
            
                    if($_GET['sellocation'] != '') {
                        if($row['Location'] != $_GET['sellocation']) {
                            continue;
                        }
                    }            

            //check the amount range
            if(isset($_GET['comp1']) && isset($_GET['comp2']) && $_GET['comp1'] != '' && $_GET['comp2'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $val2 = str_replace(',', '', $_GET['num2']);
                $comp1 = $_GET['comp1'];
                $comp2 = $_GET['comp2'];
                if(eval("return \$gross_hold $comp1 \$val1;") && eval("return \$gross_hold $comp2 \$val2;")) {
                    $arrdetails[] = $temp;
                }
            } else if(isset($_GET['comp1']) && $_GET['comp1'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $comp1 = $_GET['comp1'];
                if(eval("return \$gross_hold $comp1 \$val1;")) {                    
                    $arrdetails[] = $temp;
                }               
            } else {
                $arrdetails[] = $temp;
            }
        }

        $arrdetails["CountOfSites"] = count($arrdetails);
        echo json_encode($arrdetails);
        $topup->close();
        unset($rows, $startdate, $enddate, $arrdetails, $temp);
        exit;
    }
    
    
    public function getdataz3() {
        include_once __DIR__.'/../sys/class/TopUp2.class.php';
        
        $startdate = date('Y-m-d')." ".BaseProcess::$cutoff;

        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate']." ".BaseProcess::$cutoff;
        
//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
//            
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        
        // to check if greater than 1 day
        // since this program must support current cut-off,
        // all dates GT or LT current cut-off
        // must not permitted to retrieve data     
        
        $topup2 = new TopUp2($this->getConnection());
        $topup2->open();
        $dir = $_GET['sord'];
        $sort = "POSAccountNo";

        if(strlen($_GET['sidx']) > 0){
            $sort = $_GET['sidx'];
        }
        ob_get_clean();
        
        //array containing complete details
        $rows = $topup2->grossHoldMonitoring($sort, $dir, $startdate,$enddate); 
        ini_set('memory_limit', '-1'); 
        ini_set('max_execution_time', '220');
        $arrdetails = array();
        foreach($rows as $id => $row) {
            $gross_hold = ((($row['Deposit'] + $row['Reload'] + $row['EwalletLoads']) - ($row['Redemption'] - $row['EwalletWithdrawal'])) - $row['ActualAmount']);
            $cashonhand =((($row['DepositCash'] + $row['ReloadCash'] + $row['EwalletCashLoads']) - $row['RedemptionCashier']) - $row['ActualAmount']) - $row["EncashedTickets"] - $row['EwalletWithdrawal'];
//            if($row['SiteID'] == 167){
//                var_dump($row['DepositCash'],$row['ReloadCash'],$row['EwalletCashLoads'],$row['RedemptionCashier'],$row['ActualAmount'],$row["EncashedTickets"], $row['EwalletWithdrawal']);exit;
//            }
            $temp = array(
                        "SiteName"=>$row['SiteName'],
//                        "SiteCode"=>substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                        "MinBalance" => $row["MinBalance"],
                        "POS"=>$row['POSAccountNo'],
                        "BCF"=>number_format($row['BCF'],2),
                        "Deposit"=>number_format($row['Deposit'],2),
                        "EwalletLoad"=>number_format($row['EwalletLoads'],2),
                        "Reload"=>number_format($row['Reload'],2),
                        "Withdrawal"=>number_format($row['Redemption'],2),
                        "EwalletWithdrawal"=>number_format($row['EwalletWithdrawal'],2),
                        "ManualRedemption"=>(($row['ActualAmount'])?number_format($row['ActualAmount'],2):''),
                        "PrintedTickets"=>number_format($row['PrintedTickets'],2),
                        "UnusedTickets"=>number_format($row['UnusedTickets'],2),
                        "RunningActiveTickets"=>number_format($row['RunningActiveTickets'],2),
                        "Coupon"=>number_format($row['Coupon'],2),
                        "CashonHand"=>number_format($cashonhand,2),
                        "GrossHold"=>number_format($gross_hold, 2),
                        "Location"=>$row['Location'],
                    );        
            
                    if($_GET['sellocation'] != '') {
                        if($row['Location'] != $_GET['sellocation']) {
                            continue;
                        }
                    }            

            //check the amount range
            if(isset($_GET['comp1']) && isset($_GET['comp2']) && $_GET['comp1'] != '' && $_GET['comp2'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $val2 = str_replace(',', '', $_GET['num2']);
                $comp1 = $_GET['comp1'];
                $comp2 = $_GET['comp2'];
                if(eval("return \$gross_hold $comp1 \$val1;") && eval("return \$gross_hold $comp2 \$val2;")) {
                    $arrdetails[] = $temp;
                }
            } else if(isset($_GET['comp1']) && $_GET['comp1'] != '') {
                $val1 = str_replace(',', '', $_GET['num1']);
                $comp1 = $_GET['comp1'];
                if(eval("return \$gross_hold $comp1 \$val1;")) {                    
                    $arrdetails[] = $temp;
                }               
            } else {
                $arrdetails[] = $temp;
            }
        }

        $arrdetails["CountOfSites"] = count($arrdetails);
        echo json_encode($arrdetails);
        $topup2->close();
        unset($rows, $startdate, $enddate, $arrdetails, $temp);
        exit;
    }
    
    //this will render to Cash on hand Adjustment history
    public function cohAdjustmentOverview() 
    {
        $this->render('topup/topup_coh_adjustment');
    }
    
    //pagination for COH Adjustment History
    public function getCohAdjustmentData() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $vstartdate = date('Y-m-d');
        if(isset($_GET['startdate']))
            $vstartdate = $_GET['startdate'];
        
//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;

        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($vstartdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $startdate = $vstartdate." ".BaseProcess::$cutoff;
        $total_row = $topup->getCohAdjustmentHistoryTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 'b.SiteName');
        $jqgrid = $params['jqgrid'];
        $rows = $topup->getCohAdjustmentHistory($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);
        //$rows = $topup->getCohAdjustmentHistory($startdate,$enddate);
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['DateCreated'],'cell'=>array(
                $row['SiteName'], // Site
                $row['POSAccountNo'],
                number_format($row['Amount'],2),
                $row['Reason'],
                $row['ApprovedBy'], //site transaction date
                $row['CreatedBy'], // Date Created
                $row['DateCreated'], // Verified By
                //date('Y-m-d H:i:s:m', strtotime($row['DateCreated']))
            ));
        }
        echo json_encode($jqgrid);
        $topup->close();
        unset($jqgrid, $rows, $params, $total_row, $startdate, $enddate);
        exit;
    }
    
    //this will render to Collection History Page
    public function postedDepositOverview() 
    {
        $this->render('topup/topup_posted_deposit');
    }
    
    //pagination for Collection History
    public function getPostedDepositData() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $startdate = date('Y-m-d');
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate'];
        

//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate));
        
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $startdate .=" ".BaseProcess::$cutoff;
        if(isset($_GET['enddate']))
            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;  ;

        $total_row = $topup->getBankDepositHistoryTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 'sr.DateCreated');
        $jqgrid = $params['jqgrid'];
        $rows = $topup->getBankDepositHistory($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['DateCreated'],'cell'=>array(
                $row['siteName'], // Site
                $row['POSAccountNo'],
                $row['bankname'], // Bank
                $row['Branch'], // Branch
                $row['BankTransactionID'], // Bank Transaction ID
                $row['BankTransactionDate'], // Bank Transaction Date
                $row['ChequeNumber'], // Cheque Number
                number_format($row['Amount'],2),
                $row['Particulars'],
                $row['remittancename'],
                $row['DateCreated'], // Date Created
                $row['name'], // Verified By
            ));
        }
        echo json_encode($jqgrid);
        $topup->close();
        unset($jqgrid, $rows, $params, $total_row, $startdate, $enddate);
        exit;
    }
    
    //this will render to Top-up History Page
    public function topUpHistoryOverview() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $param['sitCode'] = $topup->getSiteCodeList();        
        $this->render('topup/topup_history',$param);
        $topup->close();
    }
    
    //pagination for Top-up History
    public function getTopUpHistory() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $startdate = date('Y-m-d');
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate'];

//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate));
        
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $startdate .= " ".BaseProcess::$cutoff;
        $type = '';
        if(isset($_GET['type']))
            $type = $_GET['type'];
        
        if(!isset($_GET['site_code']))
            $_GET['site_code'] = '';
        
        $total_row = $topup->getTopUpHistoryTotal($startdate, $enddate, $type,$_GET['site_code']);
        $params = $this->getJqgrid($total_row['totalrow'], 'TopupHistoryID'); //get jqgrid parameters
        $jqgrid = $params['jqgrid'];
        //top-up history (Manual, Auto) details
        $rows = $topup->getTopUpHistory($params['sort'], $params['dir'], $params['start'], $params['limit'], $startdate, $enddate, $type,$_GET['site_code']);
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['TopupHistoryID'],'cell'=>array(

                $row['SiteName'],
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['POSAccountNo'],
                number_format($row['StartBalance'],2),
                number_format($row['EndBalance'],2),
                number_format($row['MinBalance'],2), 
                number_format($row['MaxBalance'],2), 
                $row['TopupCount'],
                number_format($row['TopupAmount'],2), 
                number_format($row['TotalTopupAmount'],2), 
                $row['DateCreated'],
                $this->_topupType($row['TopupType'])
            ));
        }        
        echo json_encode($jqgrid); 
        $topup->close();
        unset($startdate, $enddate, $type, $total_row, $params, $jqgrid, $rows, $jqgrid);
        exit;
    }
    
    //this method will be called when defining Top-up Type
    private function _topupType($type) 
    {
        if($type == 0) {
            return 'Manual';
        } else {
            return 'Auto';
        }
    }
    
   //this method will be called when defining Manual Redemption Status
    private function redemptionstatus($status)
    {
        switch($status)
        {
            case 0:
                return 'Pending';
                break;
            case 1:
                return 'Successful';
                break;
            case 2:
                return 'Failed';
                break;
            default:
                return 'Invalid Status';
                break;
        }
    }
    
    //this will render to Manual Top-up Reversal History
    public function reversalManual() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $this->render('topup/topup_reversal_manual');        
    }
    
    //pagination for Manual Top-up Reversal History
    public function getReversalManual() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $startdate = date('Y-m-d');
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate'];

//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate));
        
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        $startdate .= " ".BaseProcess::$cutoff;
        $topup = new TopUp($this->getConnection());
        $topup->open();        
        $total_row = $topup->getReversalManualTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 'SiteCode'); //get jqgrid parameters
        $jqgrid = $params['jqgrid'];
        //get topup history (Reversal) details
        $rows = $topup->getReversalManual($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);

        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['TopupHistoryID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['SiteName'],
                $row['POSAccountNo'],
                number_format($row['StartBalance'],2),
                number_format($row['EndBalance'],2),
                number_format($row['ReversedAmount'],2), 
                $row['TransDate'],
                $row['ReversedBy'], 
            ));
        }        
        echo json_encode($jqgrid); 
        $topup->close();
        unset($startdate, $enddate, $total_row, $params, $jqgrid, $rows, $jqgrid);
        exit;        
    }
    
    //this will render on Playing Balance Page, after it get its site codes
    public function playingBalance() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();   
        $param['sites'] = $topup->getAllSiteCode();
        $this->render('topup/topup_playing_balance',$param);     
        $topup->close();
    }
    
    public function CountSession() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();   
        $siteID = $_POST['siteID'];
        if($siteID == 'all'){
            $siteID = $_POST['siteID'];
            $terminalID = 'all';
            $vipTerminal = 'all';
        } else {
            $siteID = $topup->getSiteID($siteID);
            $terminalID = isset($_POST['terminalID']) ? $_POST['terminalID']:'all';
                    $siteCode = $topup->getSiteCodes($siteID);

                    if ($terminalID=='all')
                    {
                       $vipTerminal= 'all'; 
                    }
                    else{
                        $code = $_POST['siteID'];
                        $terminalCode = $topup->getTerminalCode($code, $terminalID);
                        $terminal=$terminalCode."VIP";
                        $terminalVip = $topup->getVipTerminal($code,$terminal);
                        $vipTerminal = $terminalVip;
                    }
        }                   
        $count = $topup->getActiveSessionCount($siteID, $txtcardnumber = '', $terminalID, $vipTerminal);
        echo "$count";
        $topup->close();
        unset($count);
    }
    
    
    public function CountSessionTer() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $siteID = $_POST['siteID'];
        if($siteID == 'all'){
            $siteID = $_POST['siteID'];
            $terminalID = '';
            $vipTerminal = '';
        } else {
            $siteID = $topup->getSiteID($siteID);
            $terminalID = isset($_POST['terminalID']) ? $_POST['terminalID']:'';
            $siteCode = $topup->getSiteCodes($siteID);
                    if ($terminalID=='all')
                    {
                       $vipTerminal= 'all'; 
                    }
                    else{
                        $code = $_POST['siteID'];
                        $terminalCode = $topup->getTerminalCode($code, $terminalID);
                        $terminal=$terminalCode."VIP";
                        $terminalVip = $topup->getVipTerminal($code,$terminal);
                        $vipTerminal = $terminalVip;
                    }
        }
             $usermode = 0;
        $count = $topup->getActiveSessionCountMod($siteID, $cardnumber = '', $usermode,$terminalID, $vipTerminal);
        echo "$count";
        $topup->close();
       
        unset($count);
    }
    
    
    public function CountSessionUB() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $siteID = $_POST['siteID'];
        if($siteID == 'all'){
            $siteID = $_POST['siteID'];
            $terminalID = '';
            $vipTerminal = '';
        } else {
            $siteID = $topup->getSiteID($siteID);
            $terminalID = isset($_POST['terminalID']) ? $_POST['terminalID']:'';
                  $siteCode = $topup->getSiteCodes($siteID);
                    if ($terminalID=='all')
                    {
                       $vipTerminal= 'all'; 
                    }
                    else{
                        $code = $_POST['siteID'];
                        $terminalCode = $topup->getTerminalCode($code, $terminalID);
                        $terminal=$terminalCode."VIP";
                        $terminalVip = $topup->getVipTerminal($code,$terminal);
                        $vipTerminal = $terminalVip;
                    }
        }
   
        $usermode = 1;
        $count = $topup->getActiveSessionCountMod($siteID, $cardnumber = '', $usermode, $terminalID, $vipTerminal);
        echo "$count";
        $topup->close();
        unset($count);
    }
    
    public function CountSessionUB1() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $txtcardnumber = $_POST['txtcardnumber'];
        $usermode = 1;
        $count = $topup->getActiveSessionCountMod($siteID = '', $txtcardnumber, $usermode);
        echo "$count";
        $topup->close();
        unset($count);
    }
    
    
    public function CountSession1() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $txtcardnumber = $_POST['txtcardnumber'];
        $count = $topup->getActiveSessionCount($siteID = '', $txtcardnumber);
        echo "$count";
        $topup->close();
        unset($count);
    }
    
    public function CountSessionTer1() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $txtcardnumber = $_POST['txtcardnumber'];
        $usermode = 0;
        $count = $topup->getActiveSessionCountMod($siteID = '', $txtcardnumber, $usermode);
        echo "$count";
        $topup->close();
        unset($count);
    }
    
    public function getListOfTerminals() 
    {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();  
        $sitecode = $_POST['sitecode'];
        
        if($sitecode == 'all'){
            $siteID = '';
        } else {
            $siteID = $topup->getSiteID($sitecode);
        }
        
        $result = $topup->getlistofterminals($siteID);

        if(count($result) > 0){
            $terminals = array();
            foreach($result as $row) {
                $rsitecode = $topup->getsitecode($row['SiteID']);
                $rterminalID = $row['TerminalID'];
                $rterminalName = $row['TerminalName'];

                //remove the "icsa-[SiteCode]"
                    $rterminalCode = substr($row['TerminalCode'], strlen($rsitecode['SiteCode']));

                //create a new array to populate the combobox
                $newvalue = array("TerminalID" => $rterminalID, "TerminalCode" => $rterminalCode, "TerminalName" => $rterminalName);
                array_push($terminals, $newvalue);
            }
        }
            else {
                    $terminals = array(0);
            }

        echo json_encode($terminals);
        $topup->close();
        unset($result);
    }
    
    //this will render on Playing Balance Page User Based
    public function playingBalanceub() 
    {  
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();   
        $this->render('topup/topup_playing_balance_ub');     
        $topup->close();
    }
    
    
    //check loyalty card number
    public function getCardNumber() 
    {  
        include_once __DIR__.'/../sys/class/LoyaltyUBWrapper.class.php';
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $loyalty = new LoyaltyUBWrapper();
        $topup->open();
        $cardnumber = $_POST['cardnumber'];
        $cardinfo = BaseProcess::$cardinfo;
        $loyaltyResult = $loyalty->getCardInfo2($cardnumber, $cardinfo, 1);
        $casinoinfo = array();
        $cardnoinfos = '';        
        $obj_result = json_decode($loyaltyResult);
        
        $service_title = "Playing Balance per Membership Card";

        if(isset($_POST['rstpin'])){
            if($_POST['rstpin'] == "ResetPin"){
                $service_title = "Reset Player PIN";
            }
        } 
        
        if($obj_result == NULL){
            $statuscode = 0;
        }
        else{
            $statuscode = $obj_result->CardInfo->StatusCode;
        }
                    
        if(!is_null($statuscode) ||$statuscode == '')
        {
                if($statuscode == 1 || $statuscode == 5)
                {
                   $casinoarray_count = count($obj_result->CardInfo->CasinoArray);

                   if($casinoarray_count != 0)
                   {
                       for($ctr = 0; $ctr < $casinoarray_count;$ctr++) {
                           $serviceid = $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID;
                           
                           $servicename = $topup->getServiceName($serviceid);
                           
                           $cardnoinfos = array(
                                     'UserName'  => $obj_result->CardInfo->MemberName,
                                     'MobileNumber'  => $obj_result->CardInfo->MobileNumber,
                                     'Email'  => $obj_result->CardInfo->Email,
                                     'Birthdate' => $obj_result->CardInfo->Birthdate,
                                     'Casino' => $servicename,
                                     'CardNumber' => $obj_result->CardInfo->CardNumber,
                                     'Login' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                                     'StatusCode' => $obj_result->CardInfo->StatusCode,
                            );

                           $_SESSION['ServiceUsername'] = $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername;
                           $_SESSION['MID'] = $obj_result->CardInfo->MemberID;
                           
                           array_push($casinoinfo, $cardnoinfos);
                       }
                       
                       echo json_encode($casinoinfo);
                  }
                  else
                  {
                   $services = $service_title . " : Casino is empty";
                   echo "$services";
                  }
               }
               else
               {  
                   //check membership card status
                   $statusmsg = $topup->membershipcardStatus($statuscode);
                   $services = $service_title . " : ".$statusmsg;
                   echo "$services";
               }
        }
        else
        {
            $statuscode = 100;
            //check membership card status
            $statusmsg = $topup->membershipcardStatus($statuscode);
            $services = $service_title . " : ".$statusmsg;
            echo "$services";
        }
     $topup->close();      
    }
    
     //pagination for Playing Balance: get active terminals only
    public function getActiveTerminals() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/LoyaltyUBWrapper.class.php';
        //include_once __DIR__.'/../sys/class/CasinoAPIHandler.class.php';
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        
        $topup = new TopUp($this->getConnection());
        $loyalty = new LoyaltyUBWrapper();
        $cardinfo = BaseProcess::$cardinfo;
        $topup->open();   
        $sitecode = $_POST['sitecode'];
        $terminalID = isset($_POST['terminalID']) ? $_POST['terminalID']:'';
        if ($terminalID=='all')
                    {
                       $vipTerminal= 'all'; 
                    }
                    else{
        $terminalCode = $topup->getTerminalCode($sitecode, $terminalID);
        $terminal=$terminalCode."VIP";
        $terminalVip = $topup->getVipTerminal($sitecode,$terminal);
        $vipTerminal = $terminalVip;
                    }
 
        $rcount = $topup->countActiveTerminals2($sitecode,$terminalID,$vipTerminal);

        
        foreach ($rcount as $value) {
            $count = $value['rcount'];
        }
        
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
        $direction = $_POST['sord']; // get the direction

        if($count > 0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages)
        {
            $page = $total_pages;
        }

        $start = (int)(((int)$page * $limit) - $limit);
        $limit = (int)$limit;   
        $rows = $topup->getActiveTerminals2($sitecode, $terminalID, $vipTerminal, $direction, $start, $limit);
        
        if(count($rows) == 0){
            $jqgrid = array();
        } else {
            $jqgrid->page = $page;
            foreach($rows as $key => $row) {
                $balance = $this->getBalance($row);
                /********************* GET BALANCE API ****************************/

                if(is_string($balance['Balance']) && $balance['Balance'] != "Error: Cannot get balance") {
                    $rows[$key]['PlayingBalance'] = number_format((double)$balance['Balance'],2, '.', ',');
                }  else {
                    if($balance['Balance'] != "Error: Cannot get balance"){
                        $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
                    } else {
                        $rows[$key]['PlayingBalance'] = $balance['Balance'];
                    }
                }
            }
            foreach($rows as $row) {
                $temp_pbal = explode('.', $row['PlayingBalance']);
                if(count($temp_pbal) != 2) {
                    if(is_string($row['PlayingBalance'])) {
                        $row['PlayingBalance'] = $row['PlayingBalance'];
                    }
                    else
                    {
                        $row['PlayingBalance'] = number_format($row['PlayingBalance'], 2, '.', ',');
                    }

                }

                if($row['PlayingBalance'] == "Error: Cannot get balance"){
                        $row['PlayingBalance'] = "N/A";
                }
                
                $loyalty_result = json_decode($loyalty->getCardInfo2($row['LoyaltyCardNumber'], $cardinfo, 1));

                $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
                $row['UserMode'] == 0 ? $row['UserMode'] = "Terminal Based" : $row['UserMode'] = "User Based";

                
                $jqgrid->rows[] = array('id'=>$row['TerminalID'],'cell'=>array(
                    substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                    $row['SiteName'], 
                    substr($row['TerminalCode'], strlen($row['SiteCode'])),
                    $row['PlayingBalance'], 
                    $row['ServiceName'],
                    $row['UserMode'],
                    $row['TerminalType'],
                    $isEwallet,
                ));
            }
            $jqgrid->total = ceil($count/$limit);
            $jqgrid->records = $count;
        }
        
        echo json_encode($jqgrid);
        $topup->close();
        unset($jqgrid, $rows, $jqgrid);
        exit;
    }
    
     //pagination for Playing Balance: get active terminals only user based
    public function getActiveTerminalsUb() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/LoyaltyUBWrapper.class.php';
        //include_once __DIR__.'/../sys/class/CasinoAPIHandler.class.php';
        include_once __DIR__.'/../sys/class/CasinoGamingCAPI.class.php';
        
        $topup = new TopUp($this->getConnection());
        $loyalty = new LoyaltyUBWrapper();
        $topup->open();
        $cardinfo = BaseProcess::$cardinfo;
        
        $total_row = $topup->getActiveTerminalsTotalub();
        $params = $this->getJqgrid($total_row, 'ts.TerminalID');
        $jqgrid = $params['jqgrid'];
        if(isset($_GET['sidx']) && $_GET['sidx'] !=  ''){
             $sort = $_GET['sidx'];
        }
        else{
            $sort = 't.TerminalCode';
        }
        $rows = $topup->getActiveTerminalsub($params['sort'], $params['dir'], $params['start'], $params['limit']);
        
        foreach($rows as $key => $row) {
            $balance = $this->getBalanceUB($row);
            /********************* GET BALANCE API ****************************/
            
            if(is_string($balance['Balance']) && $balance['Balance'] != "Error: Cannot get balance") {
                $rows[$key]['PlayingBalance'] = (float)$balance['Balance'];
            }  else {
                
                if($balance['Balance'] != "Error: Cannot get balance"){
                            $rows[$key]['PlayingBalance'] = number_format($balance['Balance'],2, '.', ',');
                    } else {
                        $rows[$key]['PlayingBalance'] = $balance['Balance'];
                    }
            }
        }
        
        $loyalty_result = json_decode($loyalty->getCardInfo2($_GET['cardnumber'], $cardinfo, 1));
        
        foreach($rows as $row) {
            $temp_pbal = explode('.', $row['PlayingBalance']);
            if(count($temp_pbal) != 2) {
                if(is_string($row['PlayingBalance'])) {
                    $row['PlayingBalance'] = $row['PlayingBalance'];
                }
                else
                {
                    $row['PlayingBalance'] = number_format($row['PlayingBalance'], 2, '.', ',');
                }
                
                if($row['PlayingBalance'] == "Error: Cannot get balance"){
                    $row['PlayingBalance'] = "N/A";
                }
            }
            $loyalty_result->CardInfo->IsEwallet == 1 ? $isEwallet = "Yes" : $isEwallet = "No";
            $row['UserMode'] == 0 ? $row['UserMode'] = "Terminal Based" : $row['UserMode'] = "User Based";
            
            $jqgrid->rows[] = array('id'=>$row['TerminalID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['SiteName'], 
                substr($row['TerminalCode'], strlen($row['SiteCode'])),
                $row['PlayingBalance'], 
                $row['ServiceName'],
                $row['UserMode'],
                $row['TerminalType'],
                $isEwallet
            ));
        }
        echo json_encode($jqgrid);
        $topup->close();
        unset($total_row, $params, $sort, $jqgrid, $rows, $jqgrid);
        exit;
    }
    
    //this will render to betting credit page
    public function bettingCredit() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();   
        $param['sites'] = $topup->getAllSiteCode();
        $param['owner'] = $topup->getOwner();
        $param['report'] = $_GET['report'];
        
        $this->render('topup/topup_betting_credit',$param);
        $topup->close();
    }
    
    //pagination: Betting Credit info
    public function getBettingCredit() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $owner = '';
        $site_id = '';
        if(isset($_GET['owner']))
            $owner = $_GET['owner'];
        if(isset($_GET['sel_site_id']))
            $site_id = $_GET['sel_site_id'];
        
        isset($_GET["report"]) ? $report = $_GET["report"] : $report = ""; #Added om 06/04/2012 for BCF Critical & Non-Critical
        
        $total = $topup->getBettingCreditTotal($_GET['bal'],$_GET['selcomp'],$owner,$site_id,$report);
        $total_row = 0 ;
        $total_balance = 0;
        if(isset($total[0]['totalrow'])) {
            $total_row = $total[0]['totalrow'];
            $total_balance = $total[0]['totalbalance'];
        }
        $params = $this->getJqgrid($total_row, 's.SiteCode');
        
        
       
        $rows = $topup->getBettingCredit($params['sort'], $params['dir'], $params['start'], $params['limit'],$_GET['bal'],$_GET['selcomp'],$owner,$site_id, $report);
        $jqgrid = $params['jqgrid'];
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['SiteID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['SiteName'], 
                $row['POSAccountNo'],
                number_format($row['Balance'],2),
                $row['MinBalance']
            ));
        }
        $jqgrid->{'totalbalance'} = number_format($total_balance,2);
        echo json_encode($jqgrid);
        $topup->close();
        unset($owner, $site_id, $total, $total_row, $total_balance, $params, $rows, $jqgrid);
        exit;
    }
    
    public function getSitesDetail() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();   
        $owner = '';
        if(isset($_GET['operator']))
            $owner = $_GET['operator'];
        
        $sites = $topup->getSitesDetails($owner);
        $arrsites = array();
        foreach ($sites as $val)
        {
            $sitecode = $val['SiteCode'];
            $vcode = substr($sitecode, strlen(BaseProcess::$sitecode));
            array_push($arrsites, array("SiteCode"=>$vcode,"SiteName"=>$val['SiteName'], 
                        "POSAccountNo"=>$val['POSAccountNo'], "SiteID"=>$val['SiteID']));
        }
        echo json_encode($arrsites);
        $topup->close();
        exit;
        unset($arrsites, $sites, $owner);
    }
    
    //this will render to manual redemption page
    public function manualRedemption() {
        $this->render('topup/topup_manual_redemption');
    }
    
    //pagination: Manual Redemption Details
    public function getManualRedemption() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $startdate = date('Y-m-d');
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate'];

        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 
        if(isset($_GET['enddate']))
            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate));
                
        $startdate .= " ".BaseProcess::$cutoff;
        $total_row = $topup->getManualRedemptionTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 'st.SiteCode'); //get jqgrid pagination parameters
        // get manual redemption history details
        $rows = $topup->getManualRedemption($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);
        $jqgrid = $params['jqgrid'];
        foreach($rows as $row) {
            
            $jqgrid->rows[] = array('id'=>$row['ManualRedemptionsID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['SiteName'], 
                $row['POSAccountNo'],
                $row['TerminalCode'] != NULL ? substr($row['TerminalCode'], strlen($row['SiteCode'])) : "N/A",
                number_format($row['ReportedAmount'],2),
                $row['Name'],
                $row['TransDate'],
                $row['TicketID'],
                $row['Remarks'],
                $this->redemptionstatus($row['Status']),
                $row["ServiceName"]
            ));
        }        
        echo json_encode($jqgrid);
        $topup->close();
        unset($startdate, $enddate, $total_row, $params, $rows, $jqgrid);
        exit;
    }
    
    //method for jqgrid plugin: parameters
    protected function getJqgrid($total_row,$default_field) {
        $jqgrid = new jQGrid();
        $jqgrid->page = $_GET['page']; 
        $limit = (int)$_GET['rows'];
        $start = ((int)$_GET['page'] * $limit) - $limit;
        $dir = $_GET['sord'];
        $sort = $_GET['sidx'];
        if($_GET['sidx'] == '') 
            $sort = $default_field;
        
        $jqgrid->total = ceil($total_row / $limit);
        $jqgrid->records = $total_row;
        return array('jqgrid'=>$jqgrid,'sort'=>$sort,'dir'=>$dir,'start'=>$start,'limit'=>$limit);
    }  
    
    //method for get balance through API (Playing Balance)
    protected function getBalance($row) {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $providername = $this->CasinoType($row['ServiceID']);
        
        switch ($providername)
        {
                case "RTG":
                    $url = self::$service_api[$row['ServiceID'] - 1];
                    $capiusername = '';
                    $capipassword = '';
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
                case "RTG2":
                    $url = self::$service_api[$row['ServiceID'] - 1];
                    $capiusername = '';
                    $capipassword = '';
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
                case "MG":
                    $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
                    list($mgurl, $mgserverID) =  $_MGCredentials;
                    $url = $mgurl;
                    $capiusername = self::$capi_username;
                    $capipassword = self::$capi_password;
                    $capiplayername = self::$capi_player;
                    $capiserverID = $mgserverID;
                    break;
                case "PT":
                    $url = self::$player_api[$row['ServiceID'] - 1];
                    $capiusername = self::$ptcasinoname;
                    $capipassword = self::$ptSecretKey;
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
        }
        $serviceusername = $topup->getUBServiceLogin($row['TerminalID']);
       switch ($providername)
        {
                case "RTG":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case "RTG2":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    if($row['UserMode'] == 0){
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $serviceusername, $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);   
                    }    
                    break;
                case "MG":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case "PT":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    if($row['UserMode'] == 0){
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $serviceusername, $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);   
                    }    
                    
                    
                    break;
        }

        return array("Balance"=>$balance, "Casino"=>$providername);    
        $topup->close();
    }
    
    //method for get balance through API (Playing Balance)
    protected function getBalanceUB($row) {
        
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $providername = $this->CasinoType($row['ServiceID']);
        
        switch ($providername)
        {
                case "RTG":
                    $url = self::$service_api[$row['ServiceID'] - 1];
                    $capiusername = '';
                    $capipassword = '';
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
                case "RTG2":
                    $url = self::$service_api[$row['ServiceID'] - 1];
                    $capiusername = '';
                    $capipassword = '';
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
                case"MG":
                    $_MGCredentials = self::$service_api[$row['ServiceID'] - 1];
                    list($mgurl, $mgserverID) =  $_MGCredentials;
                    $url = $mgurl;
                    $capiusername = self::$capi_username;
                    $capipassword = self::$capi_password;
                    $capiplayername = self::$capi_player;
                    $capiserverID = $mgserverID;
                    break;
                case "PT":
                    $url = self::$player_api[$row['ServiceID'] - 1];
                    $capiusername = self::$ptcasinoname;
                    $capipassword = self::$ptSecretKey;
                    $capiplayername = '';
                    $capiserverID = '';
                    break;
        }
        
        
        switch ($providername)
        {
                case "RTG":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case "RTG2":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    if($row['UserMode'] == 0){
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $_SESSION['ServiceUsername'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);   
                    }   
                    break;
                case "MG":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();
                    $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    break;
                case "PT":
                    $CasinoGamingCAPI = new CasinoGamingCAPI();

                    if($row['UserMode'] == 0){
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $row['TerminalCode'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);
                    }
                    else
                    {
                        $balance = $CasinoGamingCAPI->getBalance($providername, $row['ServiceID'], $url, 
                            $_SESSION['ServiceUsername'], $capiusername, $capipassword, $capiplayername, 
                            $capiserverID);   
                    }    
                    
                    
                    break;
        }
      
        
       
        return array("Balance"=>$balance, "Casino"=>$providername);    
        $topup->close();
    }
    
    //this renders replenishment
    public function replenishmentOverview() {
        $this->render('topup/topup_replenish_overview');
    }
    
    //@date modified 03-03-2015
    public function replenishment() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 

        $startdate = date("Y-m-d");
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate'];

        $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($startdate)))." ".BaseProcess::$cutoff;
        if(isset($_GET['enddate']))
            $enddate = date ('Y-m-d' , strtotime (BaseProcess::$gaddeddate, strtotime($_GET['enddate'])))." ".BaseProcess::$cutoff;
        
        $startdate .= " ".BaseProcess::$cutoff;

        $total_row = $topup->getReplenishmentTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 'r.DateCreated'); //get jqgrid pagination
        //$params = $this->getJqgrid($total_row, 's.SiteCode'); //get jqgrid pagination
        $rows = $topup->getReplenishment($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);
        $jqgrid = $params['jqgrid'];
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['ReplenishmentID'],'cell'=>array(
                substr($row['SiteCode'],strlen(BaseProcess::$sitecode)),
                $row['POSAccountNo'],
                number_format($row['Amount'],2), 
                $row['DateCreated'],
                $row['Name'],
                $row['ReferenceNumber'],
                $row['ReplenishmentName']
            ));
        }        
        echo json_encode($jqgrid);        
        $topup->close();
        unset($startdate, $enddate, $total_row, $params, $rows, $jqgrid);
        exit;
    }
    
    public function confirmationOverview() {
        $this->render('topup/topup_confirmation_overview');
    }
    
    public function confirmation() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $startdate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate']." ".BaseProcess::$cutoff;
        
        if(isset($_GET['enddate']))
            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $total_row = $topup->getConfirmationTotal($startdate, $enddate);
        $params = $this->getJqgrid($total_row, 's.SiteCode'); //get jqgrid pagination
        $rows = $topup->getConfirmation($params['sort'], $params['dir'], $params['start'], $params['limit'],$startdate,$enddate);
        $jqgrid = $params['jqgrid'];
        foreach($rows as $row) {
            $jqgrid->rows[] = array('id'=>$row['GrossHoldConfirmationID'],'cell'=>array(
                $row['UserName'],
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['POSAccountNo'],
                date('Y-m-d H:i:s',strtotime($row['DateCredited'])), 
                date('Y-m-d H:i:s',strtotime($row['DateCreated'])), 
                $row['SiteRepresentative'],
                number_format($row['AmountConfirmed'],2),
                $row['UserName']
            ));
        }        
        echo json_encode($jqgrid);
        $topup->close();
        unset($startdate, $enddate, $total_row, $params, $rows, $jqgrid);
        exit;
    }
    
    public function grossHoldBalanceOverview() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';        
        $topup = new TopUp($this->getConnection());
        $topup->open();         
        $sites = $topup->getAllSiteCode();
        $this->render('topup/topup_grosshold_balance',array('sites'=>$sites));
        $topup->close();
    }
    
    public function grossHoldBalance() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open(); 
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $startdate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['startdate']))
            $startdate = $_GET['startdate']." ".BaseProcess::$cutoff;
        
//        if(isset($_GET['enddate']))
//            $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($_GET['enddate'])) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        
        $enddate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startdate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff; 

        $dir = $_GET['sord'];
        $sort = "s.SiteCode";
        $siteID = $_GET['site'];
        if(strlen($_GET['sidx']) > 0)
            $sort = $_GET['sidx'];
        
        $rows = array();
        //check if queried date is date today
        if($datenow != $startdate)
        {
//            $rows = $topup->getGrossHoldBalance($sort, $dir, $startdate,$enddate); //get gross hold balance, (date today)
//        }
//        else
//        {
            $rows = $topup->getoldGHBalance($sort, $dir, $startdate, $enddate,$siteID); //get gross hold balance, (past)
        }
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $count = count($rows); //count total rows
        if($count > 0 ) {
               $total_pages = ceil($count/$limit);
        } else {
              $total_pages = 0;
        }

        if ($page > $total_pages)
        {
            $page = $total_pages;
            $start = $limit * $page - $limit;           
        }
        if($page == 0)
        {
            $start = 0;
        }
        else{
            $start = $limit * $page - $limit;   
        }

        $limit = (int)$limit;
        $rresult =  $topup->paginatetransaction($rows, $start, $limit); //paginate grosshold balance array return
        
        $params = $this->getJqgrid($count, 's.SiteCode'); //call jqgrid method to initialize start and limit
        $jqgrid = $params['jqgrid'];
        foreach($rresult as $row) {
            $grosshold = (($row['InitialDeposit'] + $row['Reload']) - $row['Redemption']) - $row['ManualRedemption'];
//            var_dump($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash']);
//            var_dump($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash'] + $row['DepositTicket'] + $row['ReloadTicket']);
//            var_dump($row['RedemptionCashier'] + $row['EwalletWithdrawals'] + $row['ManualRedemption']);
//            var_dump($row['EncashedTickets']);exit;
            /*************Cash on Hand V1 computation******************/
            if ($startdate < BaseProcess::$deploymentdate) {
                $cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTickets'];
            }
            /*************Cash on Hand V2 computation******************/
            else {
                $cashonhand = ((((($row['DepositCash'] + $row['EwalletCashLoads'] + $row['ReloadCash'] + $row['DepositTicket'] + $row['ReloadTicket'] + $row['EwalletTicketDeposit'] + $row['Coupon']) - $row['RedemptionCashier']) - $row['RedemptionGenesis']) - $row['EwalletWithdrawals']) - $row['ManualRedemption']) - $row['EncashedTicketsV15'];
            }
            
            $endbal = ($cashonhand + $row['Replenishment']) - $row['Collection'];
//            $viewdetails = "<input type='button' id='btnviewdetails' value='View Details' SiteID='".$row['SiteID']."' StartDate='".$row['ReportDate']." ".BaseProcess::$cutoff."' EndDate='".$row['CutOff']."'".
//                                           " onClick = '$(\"#hdnsiteid\").val($(this).attr(\"SiteID\")); $(\"#hdnstartdate\").val($(this).attr(\"StartDate\")); $(\"#hdnenddate\").val($(this).attr(\"EndDate\"));".
//                                           "jQuery( \"#frmexport\").attr(\"action\",\"\"); jQuery(\"#frmexport\").attr(\"action\",\"GrossHoldBalanceViewDetails.php\");  document.getElementById(\"frmexport\").submit();'/>";
            $jqgrid->rows[] = array('id'=>$row['SiteID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['CutOff'],
                number_format($row['BegBal'],2),
                number_format($row['InitialDeposit'],2),
                number_format($row['EwalletDeposits'],2),
                number_format($row['Reload'],2),
                number_format($row['Redemption'],2),
                number_format($row['EwalletWithdrawals'],2),
                number_format($row['ManualRedemption'],2),
                number_format($row['PrintedTickets'],2),
                number_format($row['UnusedTickets'],2),
                number_format($row['Coupon'],2),
                number_format($cashonhand,2),
//                number_format($grosshold,2),
                number_format($row['Replenishment'],2),
                number_format($row['Collection'],2),
                number_format($endbal,2),
//                $viewdetails
            ));
        }        
        echo json_encode($jqgrid); 
        $topup->close();
        unset($datenow, $startdate, $enddate, $dir, $sort, $rows, $page, $limit, $count, $total_pages, $jqgrid, $rresult, $params);
        exit;
    }
    
    public function grossHoldBalanceViewDetails($siteid, $startdate, $enddate) {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';        
        $topup = new TopUp($this->getConnection());
        $topup->open();         
        $datenow = date("Y-m-d")." ".BaseProcess::$cutoff;
        $dir = 'ASC';
        $sort = "s.SiteCode";
        if($datenow != $startdate)
        {
            $rows = $topup->getoldGHBalance($sort, $dir, $startdate, $enddate,$siteid); //get gross hold balance, (past)
        }

        if(!empty($rows)){
            foreach ($rows as $value) {
                $sitename = $value['SiteName'];
                $sitecode = $value['SiteCode'];
                $posaccnt = $value['POSAccountNo'];
                $cutoff = $value['CutOff'];
                $begbal = $value['BegBal'];
                $totaldeposit = $value['InitialDeposit'];
                $depositcash = $value['DepositCash'];
                $depositticket = $value['DepositTicket'];
                $depositcoupon = $value['DepositCoupon'];
                $totalreload = $value['Reload'];
                $reloadcash = $value['ReloadCash'];
                $reloadticket = $value['ReloadTicket'];
                $reloadcoupon = $value['ReloadCoupon'];
                $totalredemption = $value['Redemption'];
                $redemptioncashier = $value['RedemptionCashier'];
                $redemptiongenesis = $value['RedemptionGenesis'];
                $manualredemption = $value['ManualRedemption'];
                $encashedtickets = $value['EncashedTickets'];
                $replenishment = $value['Replenishment'];
                $collection = $value['Collection'];
                $endbal = $value['EndBal'];
                $grosshold = (($value['InitialDeposit'] + $value['Reload']) - $value['Redemption']) - $value['ManualRedemption'];
                $cashonhand = ((($value['DepositCash'] + $value['ReloadCash']) - $value['RedemptionCashier']) - $value['ManualRedemption']) - $value['EncashedTickets'];
            
                $this->render('topup/topup_ghbal_viewdetails',array('SiteID' => $siteid,'SiteName'=> $sitename, 'SiteCode' => $sitecode, 'POSAccountNo' => $posaccnt, 'CutOff'=> $cutoff,
                                            'BegBal' => number_format($begbal,2), 'TotalDeposit' => number_format($totaldeposit,2), 
                                            'DepositCash' => number_format($depositcash,2), 'DepositTicket' => number_format($depositticket,2),
                                            'DepositCoupon' => number_format($depositcoupon,2), 'TotalReload' => number_format($totalreload,2), 
                                            'ReloadCash' => number_format($reloadcash,2),'ReloadTicket' => number_format($reloadticket,2), 
                                            'ReloadCoupon' => number_format($reloadcoupon,2), 'TotalRedemption' => number_format($totalredemption,2),
                                            'RedemptionCashier' => number_format($redemptioncashier,2), 'RedemptionGenesis' => number_format($redemptiongenesis,2),
                                            'ManualRedemption' => number_format($manualredemption,2), 'EncashedTickets' => number_format($encashedtickets,2), 
                                            'Replenishment' => number_format($replenishment,2),'Collection' => number_format($collection,2), 'EndBal' => number_format($endbal,2), 
                                            'GrossHold' => number_format($grosshold,2), 'CashOnHand' => number_format($cashonhand,2), 'StartDate' => $startdate, 'EndDate' => $enddate));
                $topup->close();
                break;
            }
        } else {
            $sitename = 'N/A';
            $sitecode = 'N/A';
            $posaccnt = 'N/A';
            $cutoff = 'N/A';
            $begbal = '0.00';
            $totaldeposit = '0.00';
            $depositcash = '0.00';
            $depositticket = '0.00';
            $depositcoupon = '0.00';
            $totalreload = '0.00';
            $reloadcash = '0.00';
            $reloadticket = '0.00';
            $reloadcoupon = '0.00';
            $totalredemption = '0.00';
            $redemptioncashier = '0.00';
            $redemptiongenesis = '0.00';
            $manualredemption = '0.00';
            $encashedtickets = '0.00';
            $replenishment = '0.00';
            $collection = '0.00';
            $endbal = '0.00';
            $grosshold = '0.00';
            $cashonhand = '0.00';
            $this->render('topup/topup_ghbal_viewdetails',array('SiteID' => $siteid,'SiteName'=> $sitename, 'SiteCode' => $sitecode, 'POSAccountNo' => $posaccnt, 'CutOff'=> $cutoff,
                                        'BegBal' => number_format($begbal,2), 'TotalDeposit' => number_format($totaldeposit,2), 
                                        'DepositCash' => number_format($depositcash,2), 'DepositTicket' => number_format($depositticket,2),
                                        'DepositCoupon' => number_format($depositcoupon,2), 'TotalReload' => number_format($totalreload,2), 
                                        'ReloadCash' => number_format($reloadcash,2),'ReloadTicket' => number_format($reloadticket,2), 
                                        'ReloadCoupon' => number_format($reloadcoupon,2), 'TotalRedemption' => number_format($totalredemption,2),
                                        'RedemptionCashier' => number_format($redemptioncashier,2), 'RedemptionGenesis' => number_format($redemptiongenesis,2),
                                        'ManualRedemption' => number_format($manualredemption,2), 'EncashedTickets' => number_format($encashedtickets,2), 
                                        'Replenishment' => number_format($replenishment,2),'Collection' => number_format($collection,2), 'EndBal' => number_format($endbal,2), 
                                        'GrossHold' => number_format($grosshold,2), 'CashOnHand' => number_format($cashonhand,2), 'StartDate' => $startdate, 'EndDate' => $enddate));
            $topup->close();
        }

    }
    
    public function ewalletsitehistory() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';        
        $topup = new TopUp($this->getConnection());
        $topup->open();         
        $sites = $topup->getAllSiteCode();
        $this->render('topup/topup_eWallet_transaction_site_history',array('sites'=>$sites));
        $topup->close();
    }
    
     public function ewalletcardehistory() {
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        include_once __DIR__.'/../sys/class/helper/common.class.php';        
//        $topup = new TopUp($this->getConnection());
//        $topup->open();         
//        $sites = $topup->getAllSiteCode();
        $this->render('topup/topup_eWallet_transaction_card_history');
//        $topup->close();
    }
    
    public function getewalletsitehistory(){
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();    
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $site="";
        
        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//        if(isset($_GET['dateTo']))
            $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cmbsite']))
            $site = $_GET['cmbsite'];
        

        
        $total_row=0;
        $total_row = $topup->getTotaleWalletTransactionHistory($site, $transType, $transStatus, $startDate, $endDate);
        $params = $this->getJqgrid($total_row, 'a.EwalletTransID'); 
        $result = $topup->geteWalletTransactionHistory($params['sort'], $params['dir'], $params['start'], $params['limit'],$site,$transType,$transStatus,$startDate,$endDate);
        $jqgrid = $params['jqgrid'];
        foreach($result as $row) {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D'){
                $trans_type = "Load";
            }
            if($row['Status']==0){$row['Status']="Pending";};
            if($row['Status']==1){$row['Status']="Successful";};
            if($row['Status']==2){$row['Status']="Failed";};
            if($row['Status']==3){$row['Status']="Fulfillment Approved";};
            if($row['Status']==4){$row['Status']="Fulfillment Denied";};
            $jqgrid->rows[] = array('id'=>$row['EwalletTransID'],'cell'=>array(
                $row['LoyaltyCardNumber'], 
                $row['StartDate'],
                $row['EndDate'],
                number_format($row['Amount'],2),
                $trans_type,
                $row['Status'],
                $row['Name']
            ));
        }  
        echo json_encode($jqgrid);
        $topup->close();
        unset($startDate, $endDate, $total_row, $params, $result, $jqgrid);
        exit;
    }
    
    public function getCardNumberStatus(){
        include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();
        $cardNumber = $_GET['cardNum'];
        
        $status = $topup->getCardNumberStatus($cardNumber);
        $topup->close();
        
        print $status;
        
    }
    public function getewalletcardhistory(){
         include_once __DIR__.'/../sys/class/TopUp.class.php';
        $topup = new TopUp($this->getConnection());
        $topup->open();    
        $startDate = date('Y-m-d')." ".BaseProcess::$cutoff;
        $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        $transStatus="";
        $transType="";
        $cardNumber="";
        
        if(isset($_GET['dateFrom']))
            $startDate = $_GET['dateFrom']." ".BaseProcess::$cutoff;
//        if(isset($_GET['dateTo']))
            $endDate = date('Y-m-d',strtotime(date("Y-m-d", strtotime($startDate)) .BaseProcess::$gaddeddate))." ".BaseProcess::$cutoff;
        if(isset($_GET['cmbtransStatus']))
            $transStatus = $_GET['cmbtransStatus'];
        if(isset($_GET['cmbtransType']))
            $transType = $_GET['cmbtransType'];
        if(isset($_GET['cardNum']))
            $cardNumber = $_GET['cardNum'];
        
        $total_row=0;
        $total_row = $topup->getTotaleWalletTransactionCardHistory($cardNumber, $transType, $transStatus, $startDate, $endDate);
        $params = $this->getJqgrid($total_row, 'a.EwalletTransID'); 
       
        $result = $topup->geteWalletTransactionCardHistory($params['sort'], $params['dir'], $params['start'], $params['limit'],$cardNumber,$transType,$transStatus,$startDate,$endDate);
        $jqgrid = $params['jqgrid'];
        foreach($result as $row) {
            $trans_type = 'Withdraw';
            if($row['TransType'] == 'D'){
                $trans_type = "Load";
            }
            if($row['Status']==0){$row['Status']="Pending";};
            if($row['Status']==1){$row['Status']="Successful";};
            if($row['Status']==2){$row['Status']="Failed";};
            if($row['Status']==3){$row['Status']="Fulfillment Approved";};
            if($row['Status']==4){$row['Status']="Fulfillment Denied";};
            $jqgrid->rows[] = array('id'=>$row['EwalletTransID'],'cell'=>array(
                substr($row['SiteCode'], strlen(BaseProcess::$sitecode)),
                $row['LoyaltyCardNumber'], 
                $row['StartDate'],
                $row['EndDate'],
                number_format($row['Amount'],2),
                $trans_type,
                $row['Status'],
                $row['Name']
            ));
        }  
        echo json_encode($jqgrid);
        $topup->close();
        unset($startDate, $endDate, $total_row, $params, $result, $jqgrid);
        exit;
        
    }
        
}

$process = new ProcessTopUpPaginate();

// this will call the method of the class
include_once 'TopupRoutes.php';
