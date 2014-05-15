<?php
/**
 * Change Status History Controller
 * @author Mark Kenneth Esguerra
 * @date Febraury 27, 2014
 */
class ChangeStatusHistoryController extends VMSBaseIdentity
{
    public $showdialog = false;
    public $showalert = false;
    public $message;
    public $messagealert;
    
    CONST COUPONS = 1;
    CONST TICKETS = 2;
    /***************************
     * Change Status History View entry point
     * Febraury 27, 2014
     **************************/
    public function actionCoupon()
    {
        $model          = new ChangeStatusHistoryForm();
        $accessrights   = new AccessRights();
        
        $option         = array();
        $submenuID      = 27;
        $headertitle = "Change Coupon Status History";
        $option[] = array('VoucherID' => 1, 'VoucherName' => 'Coupons');
        //Provide list data
        $vouchers = CHtml::listData($option, 'VoucherID', 'VoucherName');
        //Check if has access rights
        $hasRight = $accessrights->checkSubMenuAccess(Yii::app()->session['AccountType'], $submenuID);
        
        if ($hasRight == false)
        {
            $this->showalert = true;
            $this->messagealert = "User has no access rights to this page.";
        }
        $this->render('index', array('model' => $model, 'vouchertype' => $vouchers, 'headertitle' => $headertitle));
    }
    public function actionTicket()
    {
        $model          = new ChangeStatusHistoryForm();  
        $accessrights   = new AccessRights();
        
        $option         = array();
        $submenuID      = 28;
        $headertitle = "Change Ticket Status History";       
        $option[] = array('VoucherID' => 2, 'VoucherName' => 'Tickets');
        //Provide list data
        $vouchers = CHtml::listData($option, 'VoucherID', 'VoucherName');
        
        //Check if has access rights
        $hasRight = $accessrights->checkSubMenuAccess(Yii::app()->session['AccountType'], $submenuID);
        if ($hasRight == false)
        {
            $this->showalert = true;
            $this->messagealert = "User has no access rights to this page.";
        }
        $this->render('index', array('model' => $model, 'vouchertype' => $vouchers, 'headertitle' => $headertitle));
    }
    /***********************************
     * Get Change Status History for Report
     * 
     **********************************/
    public function actionGetChangeStatusHistory()
    {
        $voucherstatushistory   = new VoucherStatusHistoryModel();
        $accounts               = new AccountTypes();
        
        $vouchertype    = $_POST['vouchertype'];
        $vouchercode    = $_POST['vouchercode'];
        $datefrom       = $_POST['date_from'];
        $dateto         = $_POST['date_to'];
        
        $response = array();
        //Check validity in date range
        if (strtotime($datefrom) < strtotime($dateto))
        {
            if ($vouchertype == self::COUPONS)
            {
                
            }
            else if ($vouchertype == self::TICKETS)
            {
                $history = $voucherstatushistory->getChangeStatusHistory($vouchercode, $datefrom, $dateto);
                $page = $_POST['page']; // get the requested page
                $limit = $_POST['rows']; // get how many rows we want to have into the grid

                $count = count($history);
                $response = array();

                if ($count > 0)
                {
                    $total_pages = ceil($count / $limit);
                }
                else
                {
                    $total_pages = 0;
                }
                if ($page > $total_pages)
                {
                    $page = $total_pages;
                }
                $start = $limit * $page - $limit;
                if ($count == 0)
                    $start = 0;

                if ($count > 0)
                {
                    $i = 0;
                    foreach ($history as $rows)
                    {
                        $response["rows"][$i]['id'] = $rows['TicketStatusID'];
                        $response["rows"][$i]['cell'] = array(
                            $rows['DateCreated'], 
                            $rows['TicketCode'], 
                            TicketModel::nameStatus($rows['OriginalStatus']), 
                            TicketModel::nameStatus($rows['NewStatus']), 
                            $accounts->getUsername($rows['CreatedByAID'])
                        );
                        $i++;
                    }

                    $response["page"]     = $page;
                    $response["total"]    = $total_pages;
                    $response["records"]  = $count;
                }
                else
                {
                    $i = 0;
                    $response["page"]     = $page;
                    $response["total"]    = $total_pages;
                    $response["records"]  = $count;
                }
            }
            
        }
        echo json_encode($response);
    }
    /**********************************
     * Generate Excel File
     * @author Mark Kenneth Esguerra
     * @date March 3, 2014
     **********************************/
    public function actionExportXls()
    {
        $voucherstatushistory   = new VoucherStatusHistoryModel();
        $account                = new AccountTypes();

        if (isset($_POST['ChangeStatusHistoryForm']))
        {
            $postvars = $_POST['ChangeStatusHistoryForm'];
            
            $vouchertype = $postvars['hdn_vouchertype'];
            //Check voucher type
            if ($vouchertype == self::COUPONS)
            {
                
            }
            else if ($vouchertype == self::TICKETS)
            {
                $vouchercode    = $postvars['hdn_vouchercode'];
                $datefrom       = $postvars['hdn_datefrom'];
                $dateto         = $postvars['hdn_dateto'];
                
                if (strtotime($datefrom) < strtotime($dateto))
                {
                    $statushistory = $voucherstatushistory->getChangeStatusHistory($vouchercode, $datefrom, $dateto);
                    //TicketCode, OriginalStatus, NewStatus, CreatedByAID, DateCreated
                    $fileName = "Change_Voucher_Status_History.xls";
                    $excel_obj = new ExportExcel("$fileName");

                    $headers = array('Date Created','TicketCode', 'Original Status', 'Updated Status', 'Processed By');
                    $finalXlsValues = array();
                    //Generate Rows
                    if(count($statushistory) > 0)
                    {                
                        foreach($statushistory as $key => $row)
                        {
                             $xlsRow = array($row['DateCreated'],
                                             $row['TicketCode'], 
                                             TicketModel::nameStatus($row['OriginalStatus']), 
                                             TicketModel::nameStatus($row['NewStatus']), 
                                             $account->getUsername($row['CreatedByAID']));  
                             array_push($finalXlsValues, $xlsRow);
                        }
                    }

                    $excel_obj->setHeadersAndValues($headers, $finalXlsValues);
                    unset($finalXlsValues, $xlsRow);
                    $excel_obj->GenerateExcelFile();
                }
            }
        } 
    }
}
?>
