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
    public $message;
    public $title;
    public $iscreditable;
    public $amount;
    public $remainingcount;
    public $batchID;
    public $ctrl;
    public $vtype;
    
    CONST COUPON = 1;
    CONST TICKET = 2;
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
                    //Check if amount entered is divisible by 100
                    if ($amount != "" && $distributiontag != "")
                    {
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
                        $this->message = "Please fill up all fields";
                    }
                }
                else if ($vouchertype == self::TICKET) //Ticket
                {
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
      
       $vouchertype =  $_POST['ChangeStatusModel_vouchertype'];
       //Coupon
       if ($vouchertype == self::COUPON)
       {
           $result = $couponbatch->getCouponBatch();
           ?>
                    <option value="">Please Select</option>
           <?php
           foreach ($result as $row)
           {
               ?>
                    <option value="<?php echo self::COUPON."-".$row['CouponBatchID']?>"><?php echo $row['CouponBatchID']?></option>
               <?php
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
               ?>
                    <option value="<?php echo self::TICKET."-".$row['TicketBatchID']?>"><?php echo $row['TicketBatchID']?></option>
               <?php
           }
       }
       else
       {
           ?>
           <option value="">Please Select</option>
           <?php
       }
    }
    /**
     * Change Status Controller
     */
    public function actionChangeStatus()
    {
        $model          = new ChangeStatusModel();
        $couponbatch    = new CouponBatchModel();
        $ticketbatch    = new TicketBatchModel();

        if (isset($_POST['ChangeStatusModel']))
        {
            $model->attributes = $_POST['ChangeStatusModel'];
            
            $vouchertype    = $model->vouchertype;
            $batch          = $model->batch;
            $status         = $model->status;
            $validfrom      = $model->validfrom;
            $validto        = $model->validto;
            
            if (($vouchertype && $batch) != "" && $status != -1)
            {
                $user = Yii::app()->session['AID'];
                $arrbatch = explode("-", $batch);
                $batch = $arrbatch[1];
                //Coupon
                if ($vouchertype == 1)
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
                }
                //Ticket
                else if ($vouchertype == 2)
                {
                        $result = $ticketbatch->changeStatus($batch, $status, $user);
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
                    }
                }
                else
                {
                    $this->showdialog = true;
                    $this->message = "Please fill up all fields";
                    $this->title = "ERROR MESSAGE";
                }
            }
            $this->render('changestatus', array('model'=>$model));
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
        if ($result > 0)
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
}
?>
