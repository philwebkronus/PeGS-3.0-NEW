<?php
/**
 * Coupon/Ticket Generation Tool (Controller)
 * @author Mark Kenneth Esguerra
 * @date October 30, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class GenerationController extends VMSBaseIdentity
{
    public $showdialog = false;
    public $showdialog2 = false;
    public $showconfirmdlg = false;
    public $message;
    public $confirmmsg;
    public $title;
    public $iscreditable;
    public $amount;
    public $remainingcount;
    public $batchID;
    public $vtype;
    public $currentstat;
    public $ticketcode;
    public $status; 
    public $refresh;
    public $autogenjob;
    public $threshold;
    public $ticketcount;
    public $showalert = false;
    public $messagealert;
    
    CONST COUPON    = 1;
    CONST TICKET    = 2;
    CONST ON        = 1;
    CONST OFF       = 2;
    
    public function actionIndex()
    {
        $model          = new GenerationToolModel();
        $counponbatch   = new CouponBatchModel();
        $coupons        = new CouponModel();
        $ticketbatch    = new TicketBatchModel();
        $tickets        = new TicketModel();
        //Check if Submit
        if (isset($_POST['GenerationToolModel']))
        {
            $model->attributes = $_POST['GenerationToolModel'];
            
            $vouchertype        = $model->vouchertype;
            $count              = $model->count;
            $amount             = $model->amount;
            $distributiontag    = $model->distributiontag;
            $iscreditable       = $model->iscreditable;
            //Check if all fields are blank
            if (($vouchertype && $count && $iscreditable) != "" && $count != 0)
            {
                $user = Yii::app()->session['AID'];
                //Determine what voucher selecter
                if ($vouchertype == self::COUPON) //Coupon
                {
                    if ($amount != "" && $distributiontag != "")
                    {
                        //Check maximum amount
                        if ($amount <= 100000)
                        {
                            //Check if amount entered is divisible by 100
                            if (($amount % 100) == 0)
                            {
                                //Check if amount enterd is greater than or equal to 500
                                if ($amount >= 500)
                                {
                                    //Start Generation of Coupons
                                    $result = $counponbatch->insertCoupons($count, $amount, $distributiontag, $iscreditable, $user);
                                    //Check TransCode
                                    switch ($result['TransCode'])
                                    {
                                        case 0: 
                                            $this->title = "ERROR MESSAGE";
                                            $this->showdialog = true;
                                            break;
                                        case 1:
                                            $this->title = "SUCCESS MESSAGE";
                                            $this->showdialog = true;
                                            break;
                                        case 2:
                                            $this->title = "WARNING";
                                            $this->showdialog2 = true;
                                            $this->iscreditable = $result['IsCreditable'];
                                            $this->amount = $result['Amount'];
                                            $this->remainingcount = $result['RemainingCoupon'];
                                            $this->batchID = $result['CouponBatchID'];
                                            $this->vtype = self::COUPON;
                                            break;
                                    }
                                    $this->message = $result['TransMsg'];
                                }
                                else
                                {
                                    $this->showdialog = true;
                                    $this->title = "ERROR MESSAGE";
                                    $this->message = "Amount must at least 500";
                                }
                            }
                            else
                            {
                                $this->showdialog = true;
                                $this->title = "ERROR MESSAGE";
                                $this->message = "Amount must be divisible by 100";
                            }
                        }
                        else
                        {
                            $this->showdialog = true;
                            $this->title = "ERROR MESSAGE";
                            $this->message = "Maximum amount is 100,000";
                        }
                    }
                    else
                    {
                        $this->showdialog = true;
                        $this->title = "ERROR MESSAGE";
                        $this->message = "Please fill up all fields";
                    }
                }
                else if ($vouchertype == self::TICKET) //Ticket
                {
                    $iscreditable = 1; //Always creditable
                    
                    $result = $ticketbatch->insertTickets($count, $iscreditable, $user);
                    switch ($result['TransCode'])
                    {
                        case 0: 
                            $this->title = "ERROR MESSAGE";
                            $this->showdialog = true;
                            break;
                        case 1:
                            $this->title = "SUCCESS MESSAGE";
                            $this->showdialog = true;
                            break;
                        case 2:
                            $this->title = "WARNING";
                            $this->showdialog2 = true;
                            $this->iscreditable = $result['IsCreditable'];
                            $this->remainingcount = $result['RemainingTickets'];
                            $this->batchID = $result['TicketBatchID'];
                            $this->vtype = self::TICKET;
                            break;
                    }
                    $this->message = $result['TransMsg'];
                }
            }
            else
            {
                $this->showdialog = true;
                $this->title = "ERROR MESSAGE";
                $this->message = "Please fill up all fields";
            }
        }
        $this->render('index', array('model'=>$model));
    }
    /**
     * Regenerate Coupons/Ticket when generated coupon/ticket
     * code has duplicate entry
     */
    public function actionRegenerate()
    {
        $model          = new GenerationToolModel();
        $counponbatch   = new CouponBatchModel();
        $coupons        = new CouponModel();
        $ticketbatch    = new TicketBatchModel();
        $tickets        = new TicketModel();
        
        if (isset($_POST['GenerationToolModel']))
        {
            $model->attributes = $_POST['GenerationToolModel'];

            $user = Yii::app()->session['AID'];
            //Coupon
            if ($model->vouchertype == self::COUPON)
            {
                $amount         = $model->amount;
                $couponbatchID  = $model->couponbatch;
                $count          = $model->remainingcount; //Remaining No. of Coupons
                $iscreditable   = $model->iscreditable; 
                //Start regenerate coupons
                $result = $coupons->regenerateCoupons($amount, $count, $couponbatchID, $iscreditable, $user);
                //Check TransCode
                switch ($result['TransCode'])
                {
                    case 0: 
                        $this->title = "ERROR MESSAGE";
                        $this->showdialog = true;
                        break;
                    case 1:
                        $this->title = "SUCCESS MESSAGE";
                        $this->showdialog = true;
                        break;
                    case 2:
                        $this->title = "WARNING";
                        $this->showdialog2 = true;
                        $this->iscreditable = $result['IsCreditable'];
                        $this->amount = $result['Amount'];
                        $this->remainingcount = $result['RemainingCoupon'];
                        $this->batchID = $result['CouponBatchID'];
                        $this->vtype = self::COUPON;
                        break;
                }
                $this->message = $result['TransMsg'];
            }
            //Ticket
            else if ($model->vouchertype == self::TICKET)
            {
                $ticketbatchID  = $model->couponbatch;
                $iscreditable   = $model->iscreditable;
                $count          = $model->remainingcount; //Remaining No. of tickets
                //Start regenerate tickets
                $result = $tickets->regenerateTickets($count, $iscreditable, $ticketbatchID, $user);
                //Check TransCode
                switch ($result['TransCode'])
                {
                    case 0: 
                        $this->title = "ERROR MESSAGE";
                        $this->showdialog = true;
                        break;
                    case 1:
                        $this->title = "SUCCESS MESSAGE";
                        $this->showdialog = true;
                        break;
                    case 2:
                        $this->title = "WARNING";
                        $this->showdialog2 = true;
                        $this->iscreditable = $result['IsCreditable'];
                        $this->remainingcount = $result['RemainingTickets'];
                        $this->batchID = $result['TicketBatchID'];
                        $this->vtype = self::TICKET;
                        break;
                }
                $this->message = $result['TransMsg'];
            }
        }
        $this->render('index', array('model'=>$model));
    }
    /**
     * Load Coupon/Ticket Batches via AJAX
     */
    public function actionLoadBatch()
    {
       $ticketbatch = new TicketBatchModel();
       $ticket      = new TicketModel();
       $couponbatch = new CouponBatchModel();
       $coupon      = new CouponModel();
      
       $vouchertype =  $_POST['vouchertype'];
       $batchlist   = "";
       //Coupon
       if ($vouchertype == self::COUPON)
       {
           $result = $couponbatch->getCouponBatch();
           
           foreach ($result as $row)
           {
               $batchlist .= CHtml::tag('option', array('value' => self::COUPON."-".$row['CouponBatchID']), $row['CouponBatchID']);
           }
       }
       //Ticket
       else if ($vouchertype == self::TICKET)
       {
           $result = $ticketbatch->getTicketBatch();
           ?>
                    <option value="">Please Select</option>
           <?php
           foreach ($result as $row)
           {
               $batchlist .= CHtml::tag('option', array('value' => self::COUPON."-".$row['TicketBatchID']), $row['TicketBatchID']);
           }
       }
       else
       {
           $batchlist = CHtml::tag('option', array('value' => 0, 'Please Select'));
       }
       echo $batchlist;
    }
    /**************************************
     * Change Status Controller
     * @author Mark Kenneth Esguerra
     */
    public function actionChangeCouponStatus()
    {
        $model          = new ChangeStatusModel();
        $couponbatch    = new CouponBatchModel();
        $accessrights   = new AccessRights();
        
        $this->vtype    = self::COUPON; 
        $vouchers       = array('1' => 'Coupon');
        $title          = "Change Coupon Status";
        
        if (isset($_POST['ChangeStatusModel']))
        {
            $model->attributes = $_POST['ChangeStatusModel'];

            $vouchertype    = $model->vouchertype;
            $batch          = $model->batch;
            $status         = $model->status;
            $validfrom      = $model->validfrom;
            $validto        = $model->validto;

            if ($vouchertype != "")
            {
                $user = Yii::app()->session['AID']; //Get AID

                $this->vtype = self::COUPON;
                if ($batch != "")
                {
                    //Get BatchID
                    $arrbatch = explode("-", $batch);
                    $batch = $arrbatch[1];

                    if ($status != -1)
                    {
                        if ($status == 1)
                        {
                            //check if valid  from and to date has values
                            if ($validfrom != "" && $validto != "")
                            {
                                //Check date range
                                if (strtotime($validfrom) < strtotime($validto))
                                {
                                    if (($validfrom && $validto) != "")
                                    {
                                        $isSuccess = 1;
                                    }
                                    else
                                    {
                                        $isSuccess = 4;
                                    }
                                }
                                else
                                {
                                    $isSuccess = 3;
                                }
                            }
                            else
                            {
                                $isSuccess = 2;
                            }
                        }
                        else 
                        {
                            $isSuccess = 1;
                        }
                    }
                    else
                    {
                        $isSuccess = 4;
                    }
                }
                else
                {
                    $isSuccess = 4;
                }

                //Change Status
                switch ($isSuccess)
                {
                    case 1:
                        $result = $couponbatch->changeStatus($batch, $status, $validfrom, $validto, $user);
                        switch($result['TransCode'])
                        {
                            case 1:
                                $this->showdialog = true;
                                $this->message = $result['TransMsg'];
                                $this->title = "SUCCESS MESSAGE";
                                break;
                            case 0:
                                $this->showdialog = true;
                                $this->message = $result['TransMsg'];
                                $this->title = "ERROR MESSAGE";
                                break;
                            default:
                                $this->showdialog = true;
                                $this->message = $result['TransMsg'];
                                $this->title = "MESSAGE";
                                break;
                        }
                        break;
                    case 2:
                        $this->showdialog = true;
                        $this->message = "Please fill up all fields";
                        $this->title = "ERROR MESSAGE";
                        break;
                    case 3:
                        $this->showdialog = true;
                        $this->message = "Invalid date range";
                        $this->title = "ERROR MESSAGE";
                        break;
                    case 4:
                        $this->showdialog = true;
                        $this->message = "Please fill up all fields";
                        $this->title = "ERROR MESSAGE";
                        break;
                }
                $this->refresh = true;
            }
            else
            {
                $this->showdialog = true;
                $this->message = "Please fill up all fields.";
                $this->title = "ERROR MESSAGE";
            }
            $this->refresh = true;
        }
        $this->render('changestatus', array('model'=>$model, 'vouchers' => $vouchers, 'title' => $title));
    }
    /******************************
     * Change Ticket Status Controller
     */
    public function actionChangeTicketStatus()
    {
        $tickets        = new TicketModel();
        $model          = new ChangeStatusModel();
        $accessrights   = new AccessRights();
        
        $this->vtype    = self::TICKET; 
        $vouchers       = array('2' => 'Ticket');
        $title          = "Change Ticket Status";
        $submenuID      = 31;
        //Check if has access rights
        $hasRight = $accessrights->checkSubMenuAccess(Yii::app()->session['AccountType'], $submenuID);
        if ($hasRight == true)
        {
            if (isset($_POST['ChangeStatusModel']))
            {
                $model->attributes  = $_POST['ChangeStatusModel'];

                $vouchertype        = $model->vouchertype;
                $status             = $model->status;
                $ticketcode         = $model->ticketcode;
                
                if ($vouchertype != "")
                {
                    //Check if Ticket Code is not blank
                    if ($ticketcode != "")
                    {
                        //Check if Ticket Code format is correct
                        if (strlen($ticketcode) == 7)
                        {
                            //Check if a Change Status has valid input
                            if ($status != -1)
                            {
                                $ticketdetails = $tickets->getTicketDetails($ticketcode);
                                //Check if ticket exist
                                if (count($ticketdetails) > 0)
                                {
                                    //get current status
                                    $currentstat = $ticketdetails['Status'];
                                    //Check status entered is already assigned in the ticket code
                                    if ($currentstat != $status)
                                    {
                                        //If expired, display active whether its active or void
                                        if ($currentstat == 7)
                                        {
                                            $dispstat = "Active";
                                        }
                                        else
                                        {
                                            $dispstat = $tickets::nameStatus($status);
                                        }
                                        $this->showconfirmdlg = true;
                                        $this->confirmmsg = "You wish to change the status of Ticket ".$ticketcode." from ".$tickets::nameStatus($currentstat)." to ".$dispstat.". ";
                                        $this->currentstat = $currentstat;
                                        $this->status = $status;
                                        $this->ticketcode = $ticketcode;
                                    }
                                    else
                                    {
                                        $this->showdialog = true;
                                        $this->message = "The Ticket ".$ticketcode." is already ".$tickets::nameStatus($currentstat)."";
                                        $this->title = "ERROR MESSAGE";
                                    }

                                }
                                else
                                {
                                    $this->showdialog = true;
                                    $this->message = "Ticket Code entered is invalid.";
                                    $this->title = "ERROR MESSAGE";
                                }
                            }
                            else
                            {
                                $this->showdialog = true;
                                $this->message = "Please select the status you wish to assign the ticket.";
                                $this->title = "ERROR MESSAGE";
                            }

                        }
                        else
                        {
                            $this->showdialog = true;
                            $this->message = "Please enter correct Ticket Code format.";
                            $this->title = "ERROR MESSAGE";
                        }
                    }
                    else
                    {
                        $this->showdialog = true;
                        $this->message = "Please enter Ticket Code.";
                        $this->title = "ERROR MESSAGE";
                    }
                }
                else
                {
                    $this->showdialog = true;
                    $this->message = "Please fill up all fields.";
                    $this->title = "ERROR MESSAGE";
                }
            }
        }
        else
        {
            $this->showalert = true;
            $this->messagealert = "User has no access rights to this page.";
        }
        $this->render('changestatus', array('model'=>$model, 'vouchers' => $vouchers, 'title' => $title));   
    }
    /**
     * Get Voucher info via AJAX
     */
    public function actionGetVoucherInfo()
    {
        $couponbatch    = new CouponBatchModel();
        $coupon         = new CouponModel();
        $ticketbatch    = new TicketBatchModel();
        $ticket         = new TicketModel();
        
        if ($_POST['batch'] != "")
        {
            $batch = explode("-",$_POST['batch']);
            $vtype = $batch[0];
            $batchID = $batch[1];
        }
        else
        {
            $vtype = 0;
            $batchID = "";
        }
        if ($vtype == self::COUPON)
        {
            $result = $couponbatch->getBatchStatus($batchID);
            //Status for coupon
            switch ($result['Status'])
            {
                case 0:
                    $status = "Inactive";
                    break;
                case 1:
                    $status = "Active";
                    break;
                case 2:
                    $status = "Deactivated";
                    break;
                case 3:
                    $status = "Used";
                    break;
                case 4:
                    $status = "Cancelled";
                    break;
                case 5:
                    $status = "Reimbursed";
                    break;
                default:
                    $status = "";
                    break;
            }
        }
        else if ($vtype == self::TICKET)
        {
            $result = $ticketbatch->getBatchStatus($batchID);
            //Status for ticket
            switch ($result['Status'])
            {
                case 1:
                    $status = "Active";
                    break;
                case 2:
                    $status = "Void";
                    break;
                case 3:
                    $status = "Used";
                    break;
                case 4:
                    $status = "Encashment";
                    break;
                case 5:
                    $status = "Cancelled";
                    break;
                case 6:
                    $status = "Reimbursed";
                    break;
                default:
                    $status = "";
                    break;
            }
        }
        else
        {
            $status = "";
        }
        $arrinfo['Status'] = $status;
        $result = $coupon->getVoucherInfo($batchID);
        if ($result['ValidFromDate'] != null && $result['ValidToDate'] != null)
        {
                $arrinfo['ValidFromDate'] = date("Y-m-d", strtotime($result['ValidFromDate']));
                $arrinfo['ValidToDate'] = date("Y-m-d", strtotime($result['ValidToDate']));
        }
        else
        {
            $arrinfo['ValidFromDate'] = "";
            $arrinfo['ValidToDate'] = "";
        }
        echo json_encode($arrinfo);
    }
    /*******************************************
     * Load Old Status in status dropdown
     * A
     * A -> C -> A
     * V -> C -> V
     * C -> A/V
     * @author Mark Kenneth Esguerra 
     * @date Febraury 25, 2014
     *******************************************/
    public function actionLoadTicketStatus()
    {
        $tickets = new TicketModel();
        
        $ticketcode = $_POST['ticketcode'];
        
        $result = array();
        //Check if ticket code is not blank
        if ($ticketcode != "")
        {
            //Check if ticket code is  already used, encash or reimbursed
            $ticketdetails = $tickets->getTicketDetails($ticketcode);
            //Check if ticket is not invalid
            if (count($ticketdetails) > 0)
            {
                $currenstat = $ticketdetails['Status'];
                //Check if there is a saved old status because that should be the
                //only option in status dropdown, else identify currentstat
                $oldstatus = $tickets->getOldStatus($ticketcode);
                if ($oldstatus != "")
                {
                    //if expired, can be change to active.
                    if ($currenstat == 7)
                    {
                        $result['Result'] = CHtml::tag('option', array('value' => $oldstatus), "Active");
                        $result['TransCode'] = 1;
                    }
                    else
                    {
                        $result['Result'] = CHtml::tag('option', array('value' => $oldstatus), $tickets::nameStatus($oldstatus));
                        $result['TransCode'] = 1;
                    }
                }
                else
                {
                    //Check if current stat is not used, reimbursed and encash
                    if ($currenstat == 1 || $currenstat == 2 || $currenstat == 7)
                    {
                        //Active and Void -> Cancelled
                        if ($currenstat == 1 || $currenstat == 2)
                        {
                            $result['Result'] = CHtml::tag('option', array('value' => 5), 'Cancelled').CHtml::tag('option', array('value' => 7), 'Expired');
                            $result['TransCode'] = 1;
                        }
                        //Cancelled tickets can't be changed
                        else if ($currenstat == 5)
                        {
                            $result['Result'] = "Changing of status is not allowed because Ticket is already ".$tickets::nameStatus($ticketdetails['Status']).".";
                            $result['TransCode'] = 2;
                        }
                    }
                    else
                    {
                        $result['Result'] = "Changing of status is not allowed because Ticket is already ".$tickets::nameStatus($ticketdetails['Status']).".";
                        $result['TransCode'] = 2;
                    }
                }
            }
            else
            {
                $result['TransCode'] = 1;
            }
        }
        else
        {
            $result['TransCode'] = 1;
        }
        echo json_encode($result);
    }
    /***************************************
     * Change Ticket Status. This function process after confirmation
     * @author Mark Kenneth Esguerra
     * @date Febraury 26, 2014
     ***************************************/
    public function actionConfChangeTicketStatus()
    {
        $tickets                = new TicketModel();
        $ticketstatushistory    = new TicketStatusHistoryModel();
        $model                  = new ChangeStatusModel();
        
        $title = "Change Ticket Status";
        $vouchers = array('2' => 'Ticket');
        
        if (isset($_POST['ChangeStatusModel']))
        {
                
            $postvars = $_POST['ChangeStatusModel'];
            
            $ticketcode     = $postvars['ticketcode'];
            $status         = $postvars['status'];
            $user           = Yii::app()->session['AID'];
            //get Current status
            $ticketdtls     = $tickets->getTicketDetails($ticketcode);
            $currentstat    = $ticketdtls['Status'];
            //Get old status
            $oldstatus = $tickets->getOldStatus($ticketcode);
            //Save current status in OldStatus field if the status entered is Cancelled
            //If the oldstatus will entered again, clear old status field
            if ($oldstatus != "")
            {
                if ($oldstatus == $status)
                {
                    $oldstat = null;
                }
                //else, retain the current status
            }
            else
            {
                $oldstat = $currentstat;
            }
            $result = $tickets->changeTicketStatus($user, $ticketcode, $oldstat, $status);
            //Display dialog box for result
            switch ($result['TransCode'])
            {
                case 0: 
                    $this->title = "ERROR MESSAGE";
                    $this->message = $result['TransMsg'];
                    break;
                case 1: 
                    //Record the changing of voucher status in Ticket Status history
                    $statushist = $ticketstatushistory->insertStatusHistory($ticketcode, $currentstat, $status, $user);
                    //Check if there is an error
                    if ($statushist['TransCode'] == 2)
                    {
                        $this->message = $statushist['TransMsg'];
                    }
                    else
                    {
                        $this->message = $result['TransMsg'];
                    }
                    $this->title = "MESSAGE";
                    break;
            }
            $this->showdialog = true;
        }
    
        $this->render('changestatus', array('model' => $model, 'vouchers' => $vouchers, 'title' => $title));
    }
    /**
     * Configure Ticket Auto-Generation
     */
    public function actionViewTicketConf()
    {
        $model = new GenerationToolModel();
        //Get pre-selected values in Db
        $this->setSelectedValues();
        //Get the List
        $arrlists = $this->getOptionsInList();
        //Set pre-selected values
        $arrparams = $this->setSelectedValues();
        $model->autogenjob      = $arrparams['AutoGenJob'];
        $model->thresholdlimit  = $arrparams['Threshold'];
        $model->autoticketcount = $arrparams['TicketCount'];
        $this->render('conf', array('model' => $model, 
                                    'thresholds' => $arrlists['Thresholds'], 
                                    'ticketcounts' => $arrlists['TicketCounts']));
    }
    /***********************************
     * Ticket Auto-Generation Input validation 
     * via AJAX
     */
    public function actionTicketAutoGenConf()
    {
        $ref_paramsmodel    = new RefParametersModel();
        //Submit Form
        $response = array();
        if (isset($_POST['autogenjob']) && isset($_POST['thresholdlimit']) && isset($_POST['autoticketcount']))
        {
            $autogenjob = $_POST['autogenjob'];
            //If auto generation is ON, get threshold and update, else update auto generation to OFF only
            if ($autogenjob == self::ON)
            {
                $thresholdlimit     = $_POST['thresholdlimit'];
                $ticketcount        = $_POST['autoticketcount'];
            
                //Check if there is a selected value for threshold limit
                if ($thresholdlimit != "")
                {
                    //Check if there is a selected value for auto ticket
                    if ($ticketcount != "")
                    {
                        if ($ticketcount >= $thresholdlimit)
                        {
                            $response['ResultCode'] = 1;
                            $response['Message']  = "You wish to set the auto-generation of ".$ticketcount." tickets upon reaching
                                                      ".$thresholdlimit." threshold limit.";
                            $response['Title']      = "MESSAGE";
                            $response['AutoGenJob'] = $autogenjob;
                            $response['Threshold']  = $thresholdlimit;
                            $response['TicketCount'] = $ticketcount;
                        }
                        else
                        {
                            $response['ResultCode'] = 0;
                            $response['Message']  = "Ticket count should not be less than threshold limit.";
                            $response['Title']      = "MESSAGE";
                        }
                    }
                    else
                    {
                        $response['ResultCode'] = 0;
                        $response['Message']  = "Please select Ticket Auto-Generation Count.";
                        $response['Title']      = "ERROR MESSAGE";
                    }
                }
                else
                {
                    $response['ResultCode'] = 0;
                    $response['Message']  = "Please select a value for threshold limit.";
                    $response['Title']      = "ERROR MESSAGE";
                }
            }
            else
            {
                //Get Current Threshold limit and Ticket Count
                $currthresholdlimit = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_THRESHHOLD);
                $currticketcount    = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_COUNT);
                
                $response['ResultCode'] = 1;
                $response['Message']  = "You wish to disable the auto-generation of tickets.";
                $response['Title']      = "MESSAGE";
                $response['AutoGenJob'] = $autogenjob;
                $response['Threshold']  = $currthresholdlimit;
                $response['TicketCount'] = $currticketcount;
            }
        }
        echo json_encode($response);
    }
    /**************************************
     * Confirmation in Ticket Auto-generation 
     * Configuration
     **********************************/
    public function actionConfirmConfigSetting()
    {
        $refparamsmodel             = new RefParametersModel();
        $model                      = new GenerationToolModel();
        $ticketautogenconfhistory   = new TicketAutoGenConfigHistory();
        
        if (isset($_POST['GenerationToolModel']))
        {   
            $postvars = $_POST['GenerationToolModel'];
            //Auto-gen job
            $autogenjob     = $postvars['hdn_autogenjob'];
            $threshold      = $postvars['hdn_threshold'];
            $ticketcount    = $postvars['hdn_ticketcount'];
            $user           = Yii::app()->session['AID'];
            //Set Ticket Auto-Gen Configuration
            $result = $refparamsmodel->setTicketAutoGenConfig($autogenjob, $threshold, $ticketcount);
            //Transaction result message
            switch ($result['TransCode'])
            {
                case 1: 
                    //Log to TicketAutoGenConfigHistory
                    $result_history = $ticketautogenconfhistory->insertConfigHistory($autogenjob, $threshold, $ticketcount, $user);
                    $this->title = "MESSAGE"; //Default dialog box title
                    switch ($result_history['TransCode'])
                    {
                        case 1:
                            $this->message = $result['TransMsg'];
                            break;
                        case 2: 
                            $this->message = $result['TransMsg']. "But an error occured while saving in Ticket Auto-Generation 
                                Configuration History";
                            $this->title = "ERROR MESSAGE";
                            break;
                        case 0:
                            $this->message = $result['TransMsg']. "But not successfully save in Ticket Auto-Generation Configuration 
                                History";
                            break;
                    }
                    break;
               case 2:
                   $this->title = "ERROR MESSAGE";
                   $this->message = $result['TransMsg'];
                   break;
               case 0:
                   $this->title = "MESSAGE";
                   $this->message = $result['TransMsg'];
                   break;
            }  
            $this->showdialog = true;
        }
        //Get the List
        $arrlists = $this->getOptionsInList();
        //Set pre-selected values
        $arrparams = $this->setSelectedValues();
        $model->autogenjob      = $arrparams['AutoGenJob'];
        $model->thresholdlimit  = $arrparams['Threshold'];
        $model->autoticketcount = $arrparams['TicketCount'];
        //Render auto-gen config view file
        $this->render('conf', array('model' => $model, 
                                    'thresholds' => $arrlists['Thresholds'], 
                                    'ticketcounts' => $arrlists['TicketCounts']));
    }
    /************************************
     * Get Dropdown list options
     *********************/
    private function getOptionsInList()
    {
        $ref_paramsmodel    = new RefParametersModel();
        
        //Initiate array
        $arrticketcounts    = array();
        $arrthresholds      = array();
        $arroptions         = array();
        //Get Threshold limits and ticketcount list
        $thresholdlimits    = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_THRESHHOLD_LIST);
        $autoticketcount    = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_COUNT_LIST);
        
        if ($thresholdlimits != "" && $autoticketcount != "")
        {
            $thresholds      = explode(",", $thresholdlimits);
            $ticketcounts    = explode(",", $autoticketcount);
            //Form an array of options
            for ($i = 0; count($thresholds) > $i; $i++)
            {
                $arrthresholds[] = array('Threshold' => $thresholds[$i], 'ThresholdText' => $thresholds[$i]);
            }
            for ($i = 0; count($ticketcounts) > $i; $i++)
            {
                $arrticketcounts[] = array('TicketCount' => $ticketcounts[$i], 'TicketCountText' => $ticketcounts[$i]);
            }
            //Create list data
            $thresholdlist = CHtml::listData($arrthresholds, 'Threshold', 'ThresholdText');
            $ticketcountslist = CHtml::listData($arrticketcounts, 'TicketCount', 'TicketCountText');
            
            $arroptions['Thresholds']   = $thresholdlist;
            $arroptions['TicketCounts'] = $ticketcountslist;
        }
        else
        {
            $arroptions['Thresholds']   = "";
            $arroptions['TicketCounts'] = "";
        }
        return $arroptions;
    }
    /******************************
     * Set the pre-selected values in dropdownlists
     *************************/
    private function setSelectedValues()
    {
        $ref_paramsmodel    = new RefParametersModel();
        
        $arrparams          = array();
        //get params    
        $autoticketjob      = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_AUTOGEN_JOB);
        $currthresholdlimit = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_THRESHHOLD);
        $currticketcount    = $ref_paramsmodel->getTicketsAutoGenParams(RefParametersModel::TICKET_COUNT);
        
        $arrparams['AutoGenJob']    = $autoticketjob;
        $arrparams['Threshold']     = $currthresholdlimit;
        $arrparams['TicketCount']   = $currticketcount;
        
        return $arrparams;
    }
}
?>
