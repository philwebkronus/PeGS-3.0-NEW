<?php

class VerifyRewardsController extends Controller
{
    
    public $showDialog = false;
    public $dialogMsg;
    public $dialogMsg2;
    public $dialogMsg3;
    public $showDialog2 = false;
    public $showDialogSuccess;
    public $title;


    public function actionIndex()
	{
        //for session checking
        $_AccountSessions = new SessionForm();

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
        else 
        {
		$model = new VerifyRewardsForm();
        
        
        $this->render('verifyrewards', array('model' => $model));
        }
	}

    public function actionVerifyrewards()
    {
    //for session checking
        $_AccountSessions = new SessionForm();
        $_PartnerSessions = new PartnerSessionModel();
        
        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        }
        else 
        {
            $sessionid = 0;
            $aid = 0;
        }
        //Check  if PartnerPID is set
        if (isset(Yii::app()->session['PartnerPID']))
        {
            $partnerPID = Yii::app()->session['PartnerPID'];
            $sessioncount = $_PartnerSessions->checkIfSessionExist($partnerPID, $sessionid); //Partner Account
        }
        else
        {
            $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid); //Admin Account
        }
        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } 
        else 
        {
            $model                  = new VerifyRewardsForm();
            $itemredemptionlogs     = new ItemRedemptionLogsModel();
            $couponredemptionlogs   = new CouponRedemptionLogsModel();

            if(isset($_POST['VerifyRewardsForm']))
            {
                $model->attributes = $_POST['VerifyRewardsForm'];

                $partnerid = $model->egamespartner;
                $reewarditemid = $model->rewarditem;
                $serialcode = $model->ecouponserial;
                $securitycode = $model->ecouponsecuritycode;

                if (isset($_POST['Submit'])) {
                        $check = $itemredemptionlogs->checkSerialSecCodes($serialcode,$securitycode,$reewarditemid);
                        $countcheck = count($check);
                        if($countcheck > 0){
                            foreach ($check as $row) {
                                $rewarditemid = $row['RewardItemID'];
                                $mid = $row['MID'];
                                $validdatefrom = $row['ValidFrom'];
                                $validdateto = $row['ValidTo'];
                                $status = $row['Status'];
                                $serial = $row['SerialCode'];
                                $security = $row['SecurityCode'];
                                $source = $row['Source'];
                            }

                            if($status == 1){
                                    Yii::app()->session['partnerid'] = $partnerid;
                                    Yii::app()->session['rewarditemid'] = $rewarditemid;
                                    Yii::app()->session['MID'] = $mid;
                                    Yii::app()->session['serialcode'] = $serial;
                                    Yii::app()->session['securitycode'] = $security;
                                    Yii::app()->session['source'] = $source;

                                    $datetoday = date("Y-m-d H:i:s"); 
                                    
                                    $checkrange = $model->check_in_range($validdatefrom, $validdateto, $datetoday);
                                  
                                    $validdatefrom = date("d/m/Y", strtotime($validdatefrom));
                                    $validdateto = date("d/m/Y", strtotime($validdateto));

                                    if($checkrange == true){

                                        $this->showDialogSuccess = true;
                                        $this->dialogMsg = "This e-Coupon ".$serialcode." is valid."; 
                                        $this->dialogMsg2 = "e-Coupon validity period is from ".$validdatefrom." to ".$validdateto.".";
                                        $this->dialogMsg3 = "To record transaction click PROCEED.";
                                    }
                                    else{
                                        $this->showDialog2 = true;
                                        $this->dialogMsg = "This e-Coupon ".$serialcode." has expired."; 
                                        $this->dialogMsg2 = "e-Coupon validity period is from ".$validdatefrom." to ".$validdateto."."; 
                                    }
                            }
                            else if($status == 3){
                                $this->showDialog2 = true;
                                $this->dialogMsg = "This e-Coupon ".$serialcode." is used."; 
                                $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
                            }

                        }
                        else
                        {
                            $this->showDialog2 = true;
                            $this->dialogMsg = "This e-Coupon ".$serialcode." does not match our records."; 
                            $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
                        }
                }
                else if (isset($_POST['Submit2'])) 
                {

                    $rafflepromo = $model->rafflepromo;
                    $serialcode2 = $model->ecouponserial2;
                    $securitycode2 = $model->ecouponsecuritycode2;

                    $check = $couponredemptionlogs->checkSerialSecCodes($serialcode2,$securitycode2,$rafflepromo);
                        $countcheck = count($check);
                        if($countcheck > 0){
                            foreach ($check as $row) {
                                $rewarditemid = $row['RewardItemID'];
                                $mid = $row['MID'];
                                $validdatefrom = $row['ValidFrom'];
                                $validdateto = $row['ValidTo'];
                                $status = $row['Status'];
                                $serial = $row['SerialCode'];
                                $security = $row['SecurityCode'];
                                $source = $row['Source'];
                            }

                            if($status == 1){

                                    $datetoday = date("Y-m-d H:i:s.u"); 
                                    
                                    $checkrange = $model->check_in_range($validdatefrom, $validdateto, $datetoday);

                                    $validdatefrom = date("d/m/Y", strtotime($validdatefrom));
                                    $validdateto = date("d/m/Y", strtotime($validdateto));

                                    if($checkrange == true){

                                        $result = $couponredemptionlogs->updateCouponLogsStatus($securitycode2, $serialcode2);
                                        if ($result['TransCode'] == 1)
                                        {
                                            $this->showDialog2 = true;
                                            $this->dialogMsg = "This e-Coupon ".$serialcode2." is valid."; 
                                            $this->dialogMsg2 = "e-Coupon validity period is from ".$validdatefrom." to ".$validdateto.".";
                                        }
                                        else
                                        {
                                            $this->showDialog2 = true;
                                            $this->dialogMsg = "An error occured while updating the status";
                                        }
                                    }
                                    else{
                                        $this->showDialog2 = true;
                                        $this->dialogMsg = "This e-Coupon ".$serialcode2." has expired."; 
                                        $this->dialogMsg2 = "e-Coupon validity period is from ".$validdatefrom." to ".$validdateto."."; 
                                    }

                            }
                            else{
                                $this->showDialog2 = true;
                                $this->dialogMsg = "This e-Coupon ".$serialcode2." is used."; 
                                $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
                            }

                        }
                        else{
                            $this->showDialog2 = true;
                            $this->dialogMsg = "This e-Coupon ".$serialcode2." does not match our records."; 
                            $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
                        }
                    }
            }
            else{
              $this->showDialogSuccess = false;
            }
        }
        $this->render('verifyrewards', array('model' => $model));
    }
    /**
     * Get Reward Items
     * @modified Mark Kenneth Esguerra
     */
    public function actionAjaxGetRewardItems() {
        
        $rewarditems = new RewardItemsModel();
        $partnerid = $_POST['VerifyRewardsForm_egamespartner'];
        
        $items = $rewarditems->getRewardItems($partnerid);
        array_unshift($items, array('RewardItemID' => '-1', 'ItemName' => '- Please Select -'));
        foreach ($items as $result)
        {
            ?>
            <option value="<?php echo $result['RewardItemID']; ?>"><?php echo $result['ItemName']; ?></option>
            <?php
        }
    }
    
    public function actionRecordrewardtrans() {
        $model = new VerifyRewardsForm();
        $validation = new Validations();
        $itemredemptionlogs = new ItemRedemptionLogsModel();
        $partners = new PartnersModel(); 
        $partnerinfo = new PartnerInfoModel();
      
            $rewarditemid = Yii::app()->session['rewarditemid'];
            $mid = Yii::app()->session['MID'];
            $aid = Yii::app()->session['AID'];
            $partnerid = Yii::app()->session['partnerid'];
            
            if($rewarditemid == '' || $mid == ''){
                $this->redirect(array('/verifyRewards/verifyrewards'));
            }
            else
            {   
                if(isset($_POST['VerifyRewardsForm']))
                {
                    $forminputs = $_POST['VerifyRewardsForm'];
     
                    if (isset($_POST['Submit'])) {
                        //Trim inputs - avoid trailing spaces
                        $partnernamecashier = trim($forminputs['partnernamecashier']);
                        $branchdetails = trim($forminputs['branchdetails']);
                        $remarks = trim($forminputs['remarks']);
                        
                        if (!$validation->validateAlphaNumeric($partnernamecashier) || (!$validation->validateAlphaNumeric($branchdetails)
                            ))
                        {
                            $this->showDialog2 = true;
                            $this->dialogMsg = "Special Characters are not allowed"; 
                            $this->title = "ERROR MESSAGE";
                        }
                        else if ($remarks != "" && !$validation->validateAlphaNumeric($remarks))
                        {
                            $this->showDialog2 = true;
                            $this->dialogMsg = "Special Characters are not allowed"; 
                            $this->title = "ERROR MESSAGE";
                        }
                        else if($partnernamecashier != '' && $branchdetails != '')
                        {

                            $upsuccess = $itemredemptionlogs->updateItemRedemptionLogs($rewarditemid, $partnernamecashier, 
                                    $branchdetails, $remarks, $mid, $aid);

                            if($upsuccess > 0){

                                $partnerpidarr = $partners->getPartnerPID($partnerid);
                                foreach ($partnerpidarr as $value) {
                                    $partnerpid = $value['RefPartnerID'];
                                }
                                $emails = array();
                                $emailarr = $partnerinfo->getPartnerEmailByCompany($partnerid);
                                foreach($emailarr as $email)
                                {
                                    $emails[] = $email['Email'];
                                }
                                $marketingemail = Yii::app()->params['marketingemail'][0];
                                //Push marketing email in array
                                array_push($emails, $marketingemail);
                                //Test if php can send email in client side
                                $to = "sample@someone.com";
                                $subject = "Test";
                                $message = "Sample Message";
                                if(mail($to,$subject,$message) != false)
                                {
                                    $vcount = 0;        
                                    $CC = '';
                                    $date = date('Y-m-d H:i:s');
                                    $partner = Yii::app()->session['partnername'];
                                    $securitycode = Yii::app()->session['securitycode'];
                                    $serialcode = Yii::app()->session['serialcode'];
                                    $rewarditem = Yii::app()->session['rewardname'];
                                    while($vcount < count($emails))
                                    {

                                        $to = $emails[$vcount];
                                        $model->mailRecordReward($to, $date, $partner, $rewarditem, $serialcode, $securitycode, $CC);
 
                                        $vcount++;
                                    }
                                    $this->showDialogSuccess = true;
                                    $this->dialogMsg = "Reward transaction is recorded."; 
                                    $this->dialogMsg2 = "Keep the e-Coupon as this should be forwarded to PhilWeb."; 

                                    Yii::app()->session['rewarditemid']= '';
                                    Yii::app()->session['partnerid']= '';
                                }
                                else
                                {
                                    $this->showDialog2 = true;
                                    $this->title = "ERROR MESSAGE";
                                    $this->dialogMsg = "Email message did not send.";
                                }
                                
                            }
                            else{
                                $this->showDialog2 = true;
                                $this->dialogMsg = "Reward transaction update Failed."; 
                                $this->dialogMsg2 = "Please Try again."; 
                            }

                            
                        }
                        else
                        {
                            $this->showDialog2 = true;
                            $this->dialogMsg = "Make sure that all required fields are filled out."; 
                        }
                    }
                    else{
                        $this->showDialog2 = false;
                    }
                }

                $this->render('recordrewardtrans', array('model' => $model));
  
            }
    }
    
    
    public function actionLogVerification() {
        $verifyrewards = new VerifyRewardsForm();
        $rewarditems = new RewardItemsModel();
        $refpartner = new RefPartnerModel();
        $rewarditem = new RewardItemsModel();
        $membercards = new MemberCardsModel();
        $memberinfo = new MemberInfoModel();
        
        $verificationlogs = new VerificationLogsModel();
        
        $partnerid = Yii::app()->session['partnerid'];
        $rewarditemid = Yii::app()->session['rewarditemid'];
        $mid = Yii::app()->session['MID'];
                    
        $serialcode = Yii::app()->session['serialcode'];
        $securitycode = Yii::app()->session['securitycode'];
        $source = Yii::app()->session['source'];
        
        $rewards = $rewarditems->getRewardID($rewarditemid);
        foreach ($rewards as $value) {
            $rewardid = $value['RewardID'];
        }
        $date =  date("Y-m-d H:i:s"); 
        $aid = Yii::app()->session['AID'];
        
        $is_success = $verificationlogs->logToVerificationLogs($rewardid, $partnerid, $rewarditemid,
            $serialcode, $securitycode, $source, $date, $aid);
        
        if($is_success > 0){
            
            $rewardname = $rewarditem->getRewardName($rewarditemid);

                foreach ($rewardname as $var) {
                    $rewardnames = $var['ItemName'];
                }
                Yii::app()->session['rewardname'] = $rewardnames;
                
                
                $partername = $refpartner->getPartnerName($partnerid);
                
                foreach ($partername as $var2) {
                    $partnernames = $var2['PartnerName'];
                }
                Yii::app()->session['partnername'] = $partnernames;
                
                $arrcard = $membercards->getCardNumber($mid);
                foreach ($arrcard as $row) {
                    $cardnumber = $row['CardNumber'];
                }

                $arrmembername = $memberinfo->getMemberNameID($mid);
                foreach ($arrmembername as $row2) {
                    $firstname = $row2['FirstName'];
                    $middlename = $row2['MiddleName'];
                    $lastname = $row2['LastName'];
                    $idname = $row2['IdentificationName'];
                }
                if (isset($cardnumber))
                {
                    Yii::app()->session['cardnumber'] = $cardnumber;
                    Yii::app()->session['membername'] = $firstname." ".$middlename." ".$lastname;
                    Yii::app()->session['identificationname'] = $idname;
                    
                    $this->redirect(array('/verifyRewards/recordrewardtrans'), array('model' => $verifyrewards));
                }
                else
                {
                    $status = $membercards->checkStatus($mid);
                    foreach($status as $row)
                    {
                        $stat = $row['Status'];
                    }
                    switch ($stat)
                    {
                        case 2:
                            $this->dialogMsg = "Membership Card is Deactivated"; 
                            break;
                        case 0:
                            $this->dialogMsg = "Membership Card is Inactive"; 
                            break;
                        case 8:
                            $this->dialogMsg = "Migrated Temporary Account"; 
                            break;
                        case 5:
                            $this->dialogMsg = "Active Temporary Account"; 
                            break;
                        case 9:
                            $this->dialogMsg = "Membership Card is Banned"; 
                            break;
                        case 7:
                            $this->dialogMsg = "Membership Card is already Migrated"; 
                            break;
                    }
                    $this->showDialog2 = true;
                    $this->dialogMsg2 = "Please try again.";
                }
        }
        else{
            $this->showDialog2 = true;
            $this->dialogMsg = "Failed to verify e-Coupons."; 
            $this->dialogMsg2 = "Please try again.";
            $this->render('verifyrewards', array('model' => $verifyrewards));
        }
        $this->render('verifyrewards', array('model' => $verifyrewards));
    }
    
    public function actionAutoLogout() {
        
        $page = $_POST['page'];
 
        if($page =='logout'){

            echo json_encode('logouts');
            //Force Logout even without clicking OK
            $aid = Yii::app()->session['AID'];
            $sessionmodel = new SessionForm();
            $sessionmodel->deleteSession($aid);
            Yii::app()->user->logout();
        } 
    }
	
}
