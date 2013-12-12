<?php
/**
 * MysteryBoxController
 * @author Noel Antonio
 * @dateCreated 11-07-2013
 */

class MysteryBoxController extends Controller
{
    public $showDialog = false;
    public $dialogMsg;
    public $dialogMsg2;
    public $dialogMsg3;
    public $showDialog2 = false;
    public $showDialogSuccess;
    public $title;
    
    
    /**
     * Index page.
     * The landing page of the module -
     * Validates the mystery box reward items.
     */
    public function actionIndex()
    {
        $model = new MysteryBoxModel();
        
        if (isset($_POST['MysteryBoxModel']))
        {
            // Pass post values to model attributes.
            $model->attributes = $_POST['MysteryBoxModel'];
            
            $rewardItem = $model->rewardItem;
            $serialCode = $model->serialCode;
            $securityCode = $model->securityCode;
            
            // Check serial and security codes
            $itemRedemptionLogsModel = new ItemRedemptionLogsModel();
            $arrCheckCodes = $itemRedemptionLogsModel->checkSerialSecCodes($serialCode, $securityCode, $rewardItem);
            $count = count($arrCheckCodes);
            if($count > 0)
            {
                foreach ($arrCheckCodes as $row) 
                {
                    $rewardItem = $row['RewardItemID'];
                    $mid = $row['MID'];
                    $validFrom = $row['ValidFrom'];
                    $validTo = $row['ValidTo'];
                    $status = $row['Status'];
                    $serialCode = $row['SerialCode'];
                    $securityCode = $row['SecurityCode'];
                    $source = $row['Source'];
                }
                
                if ($status == 1) // success
                {
                        // Pass the values to sessions
                        Yii::app()->session['rewarditemid'] = $rewardItem;
                        Yii::app()->session['MID'] = $mid;
                        Yii::app()->session['serialcode'] = $serialCode;
                        Yii::app()->session['securitycode'] = $securityCode;
                        Yii::app()->session['source'] = $source;

                        // Check reward item expiry date
                        $dateToday = date("Y-m-d H:i:s"); 
                        $checkRange = $model->check_in_range($validFrom, $validTo, $dateToday);
                        $validFrom = date("d/m/Y", strtotime($validFrom));
                        $validTo = date("d/m/Y", strtotime($validTo));

                        if ($checkRange == true) // available
                        {
                            $this->showDialogSuccess = true;
                            $this->dialogMsg = "This e-Coupon " . $serialCode . " is valid."; 
                            $this->dialogMsg2 = "e-Coupon validity period is from " . $validFrom . " to " . $validTo . ".";
                            // $this->dialogMsg3 = "To record transaction click PROCEED.";
                        }
                        else // expired
                        {
                            $this->showDialog2 = true;
                            $this->dialogMsg = "This e-Coupon " . $serialCode . " has expired."; 
                            $this->dialogMsg2 = "e-Coupon validity period is from " . $validFrom . " to " . $validTo . "."; 
                        }
                }
                else if($status == 3) // already claimed or used
                {
                    $this->showDialog2 = true;
                    $this->dialogMsg = "This e-Coupon " . $serialCode . " is used."; 
                    $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
                }
            }
            else // Invalid e-Coupon
            {
                $this->showDialog2 = true;
                $this->dialogMsg = "This e-Coupon " . $serialCode . " does not match our records."; 
                $this->dialogMsg2 = "Review e-Coupon details in the previous page."; 
            }
        }
        
        // Render the page
        $this->render('index', array('model'=>$model));
    }
    
    
    
    /**
     * actionLogVerification()
     * Log the verified mystery box items.
     */
    public function actionLogVerification()
    {

        // Initialize the models to be used
        $model = new MysteryBoxModel(); // new VerifyRewardsForm();
        $rewardItemsModel = new RewardItemsModel();
        $refPartnerModel = new RefPartnerModel();
        $memberCardsModel = new MemberCardsModel();
        $memberInfoModel = new MemberInfoModel();
        $partnersModel = new PartnersModel(); 
        $verificationLogsModel = new VerificationLogsModel();
        $itemRedemptionLogsModel = new ItemRedemptionLogsModel();
        $auditTrailModel = new AuditTrailModel();
        
        // Pass the session to variables
        $partnerId = Yii::app()->session['partnerid'];
        $rewardItemId = Yii::app()->session['rewarditemid'];
        $mid = Yii::app()->session['MID'];   
        $serialCode = Yii::app()->session['serialcode'];
        $securityCode = Yii::app()->session['securitycode'];
        $source = Yii::app()->session['source'];
        $aid = Yii::app()->session['AID'];
        
        // Get Reward ID of the item
        $rewards = $rewardItemsModel->getRewardID($rewardItemId);
        foreach ($rewards as $value) {
            $rewardId = $value['RewardID'];
        }
        $date =  date("Y-m-d H:i:s");
        
        // Get Partner PID from the selected partner (e-Games)
        $partnerPIDArr = $partnersModel->getPartnerPID($partnerId);
        foreach ($partnerPIDArr as $value) 
        {
            $partnerPID = $value['PartnerPID'];
        }
        
        // Log used reward items to verification logs
        $is_success = $verificationLogsModel->logToVerificationLogs($rewardId, $partnerPID, $rewardItemId,
            $serialCode, $securityCode, $source, $date, $aid);
        
        if($is_success > 0)
        {
                // Get reward name
                $rewardName = $rewardItemsModel->getRewardName($rewardItemId);
                foreach ($rewardName as $var) {
                    $rewardNames = $var['ItemName'];
                }
                Yii::app()->session['rewardname'] = $rewardNames;
                
                
                // Get partner name
                $parterName = $refPartnerModel->getPartnerName($partnerId);
                foreach ($parterName as $var2) {
                    $partnerNames = $var2['PartnerName'];
                }
                Yii::app()->session['partnername'] = $partnerNames;
                
                
                // Get card number
                $arrCard = $memberCardsModel->getCardNumber($mid);
                foreach ($arrCard as $row) {
                    $cardNumber = $row['CardNumber'];
                }

                // Get member info
                $arrMemberName = $memberInfoModel->getMemberNameID($mid);
                foreach ($arrMemberName as $row2) {
                    $firstName = $row2['FirstName'];
                    $middleName = $row2['MiddleName'];
                    $lastName = $row2['LastName'];
                    $idName = $row2['IdentificationName'];
                }
                
                if (isset($cardNumber))
                {
                    // Log the transaction
                    Yii::app()->session['cardnumber'] = $cardNumber;
                    Yii::app()->session['membername'] = $firstName." ".$middleName." ".$lastName;
                    Yii::app()->session['identificationname'] = $idName;
                    
                    // $this->redirect(array('/mysteryBox/recordTrans'), array('model' => $model));
                    
                    // Update Item Redemption Logs to USED
                    $upsuccess = $itemRedemptionLogsModel->updateItemRedemptionLogs($rewardItemId, NULL, 
                            NULL, NULL, $mid, $aid, $securityCode, $serialCode);
                    
                    if ($upsuccess > 0)
                    {
                        // Log to audit trail
                        $logSuccess = $auditTrailModel->logEvent(RefAuditFunctionsModel::MARKETING_VERIFY_MYSTERY_REWARDS, 
                                "SerialCode: " . $serialCode . ";SecurityCode: " . $securityCode . ";Mystery Item: " . $rewardNames . ":successful", 
                                array('SessionID' => Yii::app()->session['SessionID'], 'AID' => Yii::app()->session['AID']));
                        
                        if ($logSuccess['TransCode'] == 1){
                            Yii::app()->session['rewarditemid']= '';
                            Yii::app()->session['partnerid']= '';
                            $this->redirect('index');
                        }
                    }
                }
                else
                {
                    // Check member status
                    $status = $memberCardsModel->checkStatus($mid);
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
        else // Verification failed
        {
            $this->showDialog2 = true;
            $this->dialogMsg = "Failed to verify e-Coupons."; 
            $this->dialogMsg2 = "Please try again.";
            // $this->render('index', array('model' => $model));
        }
        
        // Render the page
        $this->render('index', array('model' => $model));
        
    }
    
    
    
    /**
     * actionRecordTrans()
     * Record the transaction made in 
     * redeeming the mystery box item.
     * @deprecated 11-21-2013 This function was disabled for a while.
     */
    public function actionRecordTrans() 
    {
        // Initialize the models
        $model = new VerifyRewardsForm();
        $validationModel = new Validations();
        $itemRedemptionLogsModel = new ItemRedemptionLogsModel();
        $partnerInfoModel = new PartnerInfoModel();

        // Pass the session to variables
        $rewardItemId = Yii::app()->session['rewarditemid'];
        $mid = Yii::app()->session['MID'];
        $aid = Yii::app()->session['AID'];
        $partnerId = Yii::app()->session['partnerid'];
        $securityCode = Yii::app()->session['securitycode'];
        $serialCode = Yii::app()->session['serialcode'];

        // if credentials are NULL
        if ($rewardItemId == '' || $mid == '')
        {
            $this->redirect(array('/mysteryBox/index'));
        }
        else
        {
            // Form submitted
            if (isset($_POST['VerifyRewardsForm']))
            {
                $formInputs = $_POST['VerifyRewardsForm'];
     
                if (isset($_POST['Submit'])) 
                {
                    // Trim inputs - avoid trailing spaces
                    $partnerNameCashier = trim($formInputs['partnernamecashier']);
                    $branchDetails = trim($formInputs['branchdetails']);
                    $remarks = trim($formInputs['remarks']);
                        
                    if ($partnerNameCashier == "" || $branchDetails == "")
                    {
                        $this->showDialog2 = true;
                        $this->dialogMsg = "Fields with asterisk (*) are required"; 
                        $this->title = "ERROR MESSAGE";
                    }
                    else if (!$validationModel->validateAlphaNumeric($partnerNameCashier) || (!$validationModel->validateAlphaNumeric($branchDetails)))
                    {
                        $this->showDialog2 = true;
                        $this->dialogMsg = "Special characters are not allowed"; 
                        $this->title = "ERROR MESSAGE";
                    }
                    else if ($remarks != "" && !$validationModel->validateAlphaNumeric($remarks))
                    {
                        $this->showDialog2 = true;
                        $this->dialogMsg = "Special characters are not allowed"; 
                        $this->title = "ERROR MESSAGE";
                    }
                    else if($partnerNameCashier != '' && $branchDetails != '')
                    {
                        // Update Item Redemption Logs to USED
                        $upsuccess = $itemRedemptionLogsModel->updateItemRedemptionLogs($rewardItemId, $partnerNameCashier, 
                                $branchDetails, $remarks, $mid, $aid, $securityCode, $serialCode);

                        if($upsuccess > 0)
                        {       
                            // Setup email
                            $emails = array();
                            $emailArr = $partnerInfoModel->getPartnerEmailByCompany($partnerId);
                            foreach($emailArr as $email)
                            {
                                $emails[] = $email['Email'];
                            }
                            $marketingEmail = Yii::app()->params['marketingemail'][0];
                                
                            // Push marketing email in array
                            array_push($emails, $marketingEmail);
                                
                            $vcount = 0;        
                            $CC = '';
                            $partner = Yii::app()->session['partnername'];
                            $rewardName = Yii::app()->session['rewardname'];
                            $timeOfAvail = date("h:i:s A");
                            $dateAvailed = date("m-d-Y");
                            $memberName = Yii::app()->session['membername'];
                            $memberCard = Yii::app()->session['cardnumber'];
                                
                            while($vcount < count($emails))
                            {
                                $to = $emails[$vcount];
                                $result = $model->mailRecordReward($to, $partner, $rewardName, $serialCode, $securityCode, $timeOfAvail, $dateAvailed, $memberCard, $memberName, $partnerNameCashier, $CC);
                                $vcount++;
                            }
                                
                            if (!$result) // email failed
                            {
                                $this->showDialog2 = true;
                                $this->title = "ERROR MESSAGE";
                                $this->dialogMsg = "Email message did not send.";
                            }
                            else // email sent.
                            {
                                $this->showDialogSuccess = true;
                                $this->dialogMsg = "Reward transaction is recorded."; 
                                $this->dialogMsg2 = "Keep the e-Coupon as this should be forwarded to PhilWeb."; 

                                Yii::app()->session['rewarditemid']= '';
                                Yii::app()->session['partnerid']= '';
                            }
                        }
                        else // Error in updating itemRedemptionLogs
                        {
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
                else
                {
                    $this->showDialog2 = false;
                }
            }

            // Render the page.
            $this->render('recordtrans', array('model' => $model));
        }
    }
}
?>