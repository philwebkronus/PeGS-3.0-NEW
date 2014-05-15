<?php

/**
 * @author Noel Antonio
 * @dateCreated November 14, 2013
 */

class TicketEncashmentController extends VMSBaseIdentity
{
        public $autoOpen = false;
        public $confirm = false;
        public $dialogMsg;
        public $dialogTitle = 'ERROR!';
        
        
        /**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
         * It is also used to update the status of the ticket code 
         * and log the transaction, upon form post with several
         * validations.
	 */
	public function actionIndex()
	{
            $model = new TicketEncashmentForm();
            
            if (isset($_POST['TicketEncashmentForm']))
            {
                $model->attributes = $_POST['TicketEncashmentForm'];
                
                if($model->validate())
                {
                    $ticketCode = $model->ticketCode;
                    $memberCardNumber = $model->memberCardNumber;
                    
                    // Check the ticket code.
                    $ticketArray = $model->checkTicketCode($ticketCode);
                    if (is_array($ticketArray) && count($ticketArray) > 0)
                    {
                        $status = $ticketArray["Status"];
                        
                        if ($status == 1 || $status == 2) // Active or Void
                        {   
                            $ticketSiteId = $ticketArray["SiteID"];
                            $ticketMid = $ticketArray["MID"];
                            $ticketValidToDate = $ticketArray["ValidToDate"];
                            $ticketAmount = $ticketArray["Amount"];
                            
                            // same site checking
                            $boolSites = $this->sameSiteChecking($ticketSiteId);
                            
                            // same membership card number checking
                            $boolMid = $this->midChecking($memberCardNumber, $ticketMid);
                            
                            // check for ticket date expiration
                            $boolExpiry = $this->dateExpirationChecking($ticketValidToDate);
                            
                            
                            if ($boolSites)
                            {
                                if ($boolMid)
                                {
                                    if ($boolExpiry)
                                    {
                                        ($status == 1) ? $str = "Active" : $str = "Void";
                                        $this->dialogMsg = "Ticket status is " . $str . " with a total amount of " . number_format($ticketAmount, 2) . ". Do you want to proceed with the encashment?";
                                        $this->confirm = true;
                                    }
                                    else // Ticket already expired.
                                    {
                                        $this->dialogMsg = "Ticket has already expired.";
                                        $this->autoOpen = true;
                                    }
                                }
                                else // MID not equal or does not exist.
                                {
                                    $this->dialogMsg = "Membership Card Number and Ticket does not match.";
                                    $this->autoOpen = true;
                                }
                            }
                            else // not same site
                            {
                                $this->dialogMsg = "Ticket must be encash to the same site where ticket was printed.";
                                $this->autoOpen = true;
                            }
                        }
                        else if ($status == 3) // Used
                        {
                            $this->dialogMsg = "Ticket is already tagged as Used.";
                            $this->autoOpen = true;
                        }
                        else if ($status == 4) // Encashed
                        {
                            $this->dialogMsg = "Ticket is already tagged as Encashed.";
                            $this->autoOpen = true;
                        }
                        else if ($status == 5) // Cancelled
                        {
                            $this->dialogMsg = "Ticket is already tagged as Cancelled.";
                            $this->autoOpen = true;
                        }
                        else if ($status == 6) // Reimbursed
                        {
                            $this->dialogMsg = "Ticket is already tagged as Reimbursed.";
                            $this->autoOpen = true;
                        }
                    }
                    else
                    {
                        $this->dialogMsg = "Ticket code not found.";
                        $this->autoOpen = true;
                    }
                }
            }
            else if (isset($_POST['hidTicketCode']))
            {
                $aid = Yii::app()->session['AID'];
                $ticketCode = $_POST['hidTicketCode'];

                $model = new TicketEncashmentForm();

                $result = $model->processTicket($ticketCode, $aid);
                if ($result['transCode'] == 1)
                {
                    $result2 = $model->log($aid, "Ticket Code Encashment: " . $ticketCode, $_SERVER['REMOTE_ADDR']);
                    $this->dialogMsg = $result2['transMsg'];
                    $this->dialogTitle = 'SUCCESSFUL';
                }
                else
                {
                    $this->dialogMsg = $result['transMsg'];
                }

                $this->autoOpen = true;
            }
            
            $this->render('index', array('model'=>$model));
	}
        
        
        /**
         * This function is used to check same site 
         * validation of the ticket code, if enabled.
         * @param int $ticketSiteId Site ID recorded from the ticket code.
         * @return boolean <b>TRUE</b> if same site,
         * <b>FALSE</b> if not.
         */
        public function sameSiteChecking($ticketSiteId)
        {
            $siteChecking = Yii::app()->params['siteChecking'];
            $boolean = true;
            
            if ($siteChecking == 'enabled')
            {   
                $sitesModel = new SitesModel();
                $sitesArray = $sitesModel->getSiteId(Yii::app()->session['AID']);
                $cashierSiteID = $sitesArray["SiteID"];

                if ($ticketSiteId != $cashierSiteID)
                {
                    $boolean = false;
                }
            }
            
            return $boolean;
        }
        
        
        /**
         * This function is used to check same 
         * membership card number, if enabled.
         * @param string $memberCardNumber member card inputted.
         * @param int $ticketMid Member ID recorded in ticket.
         * @return boolean <b>TRUE</b> if same MID,
         * <b>FALSE</b> if not.
         */
        public function midChecking($memberCardNumber, $ticketMid)
        {
            $midChecking = Yii::app()->params['cardNumberChecking'];
            $boolean = true;
            
            if ($midChecking == 'enabled')
            {
                $model = new TicketEncashmentForm();
                $memberArray = $model->checkMemberCardNumber($memberCardNumber);
                
                if (count($memberArray) > 0)
                {
                    $memberMid = $memberArray[0]["MID"];
                    if ($ticketMid != $memberMid)
                    {
                        $boolean = false;
                    }
                }
                else // Membership Card Number not found.
                {
                    $boolean = false;
                }
            }
            
            return $boolean;
        }
        
        
        /**
         * This function is used to check the ticket
         * expiration date.
         * @param date $validToDate Ticket code valid to date.
         * @return boolean <b>TRUE</b> if it's still good,
         * <b>FALSE</b> if not.
         */
        public function dateExpirationChecking($validToDate)
        {
            $dateInterval = Yii::app()->params['dateIntervalEnabled'];
            $boolean = true;
            
            if ($dateInterval == 'enabled')
            {
                $dateToday = new DateTime(date('Y-m-d H:i:s'));
                $dateValidity = new DateTime($validToDate);                

                if ( $dateToday > $dateValidity ) {
                    $boolean = false;
                }
            }
            
            return $boolean;
        }
}
?>
