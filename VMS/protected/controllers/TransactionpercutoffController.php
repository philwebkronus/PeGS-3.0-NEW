<?php
/**
 * Revised Transaction per cut off for Tickets 
 * @author Mark Kenneth Esguerra
 * @date May 22, 2014
 */
class TransactionpercutoffController extends VMSBaseIdentity
{
    public $showdialog;
    public $message;
    
    CONST ACTIVE = 1;
    CONST USED = 3;
    CONST ENCASHED = 4;
    
    public function actionTicket()
    {
        $model          = new TransactionpercutoffForm();
        $sites          = new SitesModel();
        $accessrights   = new AccessRights();
        
        $submenuID  = 32;
        $hasRight   = $accessrights->checkSubMenuAccess(Yii::app()->session['AccountType'], $submenuID);
        $autoselect = false;
        if ($hasRight)
        {
            //If the user is either SiteSup, SiteOps or Cashier, get only the sites under them
            if (Yii::app()->session['AccountType'] == 2 || 
                Yii::app()->session['AccountType'] == 3 || 
                Yii::app()->session['AccountType'] == 4 )
            {
                $aid = Yii::app()->session['AID'];
                
                $siteIDs = $sites->getSiteIDs($aid);
                $arrSiteID = array();
                foreach($siteIDs as $s_id)
                {
                    $arrSiteID[] = $s_id['SiteID'];
                }
                $autoselect = true;
            }
            else
            {
                $arrSiteID = null;
            }
            //Get Site Codes
            $sitecodes = $sites->getSiteCodes($arrSiteID);
            if (count($sitecodes) > 0)
            {
                foreach ($sitecodes as $sitecode)
                {
                    $arrsitecodes[] = array('SiteID' => $sitecode['SiteID'], 'SiteCode' => trim(str_replace("ICSA-", "", $sitecode['SiteCode'])));
                }
            }
            else
            {
                $arrsitecodes[] = array();
            }
        }
        else
        {
            $arrsitecodes = array();
            $this->showalert = true;
            $this->messagealert = "User has no access right to this page";
        }
        
        array_unshift($arrsitecodes, array('SiteID' => 'All', 'SiteCode' => 'All'));
        $sitecodelist = CHtml::listData($arrsitecodes, 'SiteID', 'SiteCode');
        
        $this->render('ticket', array('model' => $model, 'sitecodes' => $sitecodelist));
    }
    
    public function actionGetTicketCuOffSummary()
    {
        $ticketModel = new TicketModel();
        
        $vouchertype        = $_POST['vouchertype'];
        $sitecode           = $_POST['sitecode'];
        $transactiondate    = $_POST['transactiondate'];
            
        $response = array();
        //Check if site code is not blank
        if ($sitecode != "")
        {
            //check if selected date is greater than today
            if ($transactiondate <= date('Y-m-d'))
            {
                $date = strtotime($transactiondate);
                $currdate = strtotime(date('Y-m-d'));

                $dateTo = date("Y-m-d", strtotime("+1 day", $date));
                //for running active tickets, less 2 days in current transaction date (date today)
                $less1day = date("Y-m-d", strtotime("-1 day", $currdate));
                $less2days = date("Y-m-d", strtotime("-2 day", $currdate));
                
                $totalPrintedTickets    = $ticketModel->getNumberOfPrintedTickets($transactiondate, $dateTo, $sitecode); //select printed tickets within the cut off
                $totalUsedTickets       = $ticketModel->getNumberOfUsedTickets($transactiondate, $dateTo, $sitecode);//select used tickets within the cutoff
                $totalEncashedTickets   = $ticketModel->getNumberOfEncashedTickets($transactiondate, $dateTo, $sitecode);//select encashed tickets within the cutoff
                //get the total number of used tickets (UT = PT - UT - ET)
                $unusedTickets      = $totalPrintedTickets['PrintedTickets'] - ($totalUsedTickets['UsedTickets'] + $totalEncashedTickets['EncashedTickets']);
                $unusedTicketsVal   = $totalPrintedTickets['Value'] - ($totalUsedTickets['Value'] + $totalEncashedTickets['Value']);
                //get running active tickets
                if ($transactiondate <= $less2days) //if date selected less than 2 days of current date
                {
                    $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
                    $totalrunningactive     = (int)$runningactive1['SumCount'];
                    $totalrunningactiveval  = $runningactive1['SumValue'];
                }
                else if ($transactiondate == $less1day) //if date selected less than a day of current date
                {
                    $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
                    $runningactive2 = $this->getDayCutOff($less1day, $sitecode); //previous day

                    $totalrunningactive     = (int)$runningactive1['SumCount'] + $runningactive2['SumCount'];
                    $totalrunningactiveval  = $runningactive1['SumValue'] + $runningactive2['SumValue'];
                }
                else if ($transactiondate == date('Y-m-d'))//if date selected is the current date
                {
                    $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
                    $runningactive2 = $this->getDayCutOff($less1day, $sitecode); //previous day
                    $runningactive3 = $this->getDayCutOff($transactiondate, $sitecode); //date today

                    $totalrunningactive     = (int)$runningactive1['SumCount'] + $runningactive2['SumCount'] + $runningactive3['SumCount'];
                    $totalrunningactiveval  = $runningactive1['SumValue'] + $runningactive2['SumValue'] + $runningactive3['SumValue'];
                }

                $response['PrintedTickets']         = $totalPrintedTickets['PrintedTickets'];
                $response['PrintedTicketsValue']    = number_format(($totalPrintedTickets['Value'] == "") ? '0.00' : $totalPrintedTickets['Value'], "2", ".", ",");

                $response['UsedTickets']            = $totalUsedTickets['UsedTickets'];
                $response['UsedTicketsValue']       = number_format(($totalUsedTickets['Value'] == "") ? '0.00' : $totalUsedTickets['Value'], "2", ".", ",");

                $response['EncashedTickets']        = $totalEncashedTickets['EncashedTickets'];
                $response['EncashedTicketsValue']   = number_format(($totalEncashedTickets['Value'] == "") ? '0.00' : $totalEncashedTickets['Value'], "2", ".", ",");

                $response['UnusedTickets']          = $unusedTickets;
                $response['UnusedTicketsValue']     = number_format(($unusedTicketsVal == "") ? '0.00' : $unusedTicketsVal, "2", ".", ",");

                $response['RunningActiveCount']     = $totalrunningactive;
                $response['RunningActiveValue']     = number_format($totalrunningactiveval, 2, ".", ",");

                $response['ErrorCode']              = 0;
            }   
            else
            {
                $response['ErrorCode']  = 1;
                $response['Message']    = "Transaction date cannot be greater than date today.";
            }
        }
        
        echo json_encode($response);
    }
    public function actionGetTicketRedemptions()
    {
        $ticketModel = new TicketModel();
        
        $transactiondate    = $_POST['_transdate'];
        $sitecode           = $_POST['_sitecode'];
        $page               = $_POST['page']; // get the requested page
        $limit              = $_POST['rows']; // get how many rows we want to have into the grid
        //get DateTo for cut off
        $date = strtotime($transactiondate);
        $dateTo = date("Y-m-d", strtotime("+1 day", $date));
        //get all tickets transactions (used and encashed)
        $getTicketRedemptions = $ticketModel->getTicketRedemptions($transactiondate, $dateTo, $sitecode);
        $redemptioncounts = count($getTicketRedemptions);
        $response = array();

        if ($redemptioncounts > 0)
        {
            //jQgrid formula
            if ($redemptioncounts > 0)
            {
                $total_pages = ceil($redemptioncounts / $limit);
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
            if ($redemptioncounts > 0)
            {
                //fetch all gathered data if it has.
                $i = 0;
                foreach ($getTicketRedemptions as $rows)
                {
                    //classify status
                    if ($rows['Status'] == self::USED)
                    {
                        $status = 'Used';
                    }
                    else if ($rows['Status'] == self::ENCASHED)
                    {
                        $status = 'Encashed';
                    }
                    //classify date processed
                    if ($rows['DateEncashed'] == NULL)
                    {
                        $dateProcessed = $rows['DateUpdated'];
                    }
                    else
                    {
                        $dateProcessed = $rows['DateEncashed'];
                    }
                    
                    $response['rows'][$i]['id'] = $rows['TicketID'];
                    $response['rows'][$i]['cell'] = array(
                        trim(str_replace(Yii::app()->params['sitePrefix'], "", $rows['SiteCode'])), 
                        $rows['TerminalName'], 
                        $rows['TicketCode'], 
                        $rows['DateCreated'], 
                        number_format($rows['Amount'], 2, ".", ","), 
                        date("Y-m-d", strtotime($rows['ValidToDate'])), 
                        $status, 
                        $dateProcessed
                    );
                    $i++;
                }
            }
            else
            {
                $start = 0;
                $i = 0;
                
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $redemptioncounts;
            }
        }
        echo json_encode($response);
    }
    /**
     * Export to Excel
     * @author Mark Kenneth Esguerra
     * @date May 23, 2014
     */
    public function actionExporttoexcelticket()
    {
        $ticketModel = new TicketModel(); 
                
        $sitecode   = $_POST['sitecode'];
        $transactiondate  = $_POST['transdate'];
        
        //Transaction Summary
        $date = strtotime($transactiondate);
        $currdate = strtotime(date('Y-m-d'));
        
        $dateTo = date("Y-m-d", strtotime("+1 day", $date));
        //for running active tickets, less 2 days in current transaction date (date today)
        $less1day = date("Y-m-d", strtotime("-1 day", $currdate));
        $less2days = date("Y-m-d", strtotime("-2 day", $currdate));
        //select printed tickets within the cut off
        $totalPrintedTickets = $ticketModel->getNumberOfPrintedTickets($transactiondate, $dateTo, $sitecode);
        //select used tickets within the cutoff
        $totalUsedTickets = $ticketModel->getNumberOfUsedTickets($transactiondate, $dateTo, $sitecode);
        //select encashed tickets within the cutoff
        $totalEncashedTickets = $ticketModel->getNumberOfEncashedTickets($transactiondate, $dateTo, $sitecode);
        //get the total number of used tickets (UT = PT - UT - ET)
        $unusedTickets      = $totalPrintedTickets['PrintedTickets'] - ($totalUsedTickets['UsedTickets'] + $totalEncashedTickets['EncashedTickets']);
        $unusedTicketsVal   = $totalPrintedTickets['Value'] - ($totalUsedTickets['Value'] + $totalEncashedTickets['Value']);
                
        $printedTicketsCount    = $totalPrintedTickets['PrintedTickets'];
        $printedTicketsValue    = ($totalPrintedTickets['Value'] == "") ? '0.00' : $totalPrintedTickets['Value'];

        $usedTicketsCount       = $totalUsedTickets['UsedTickets'];
        $usedTicketsValue       = ($totalUsedTickets['Value'] == "") ? '0.00' : $totalUsedTickets['Value'];

        $encashedTicketsCount   = $totalEncashedTickets['EncashedTickets'];
        $encashedTicketsValue   = ($totalEncashedTickets['Value'] == "") ? '0.00' : $totalEncashedTickets['Value'];

        $unusedTicketsCount     = $unusedTickets;
        $unusedTicketsValue     = ($unusedTicketsVal == "") ? '0.00' : $unusedTicketsVal;
    
        //get running active tickets
        if ($transactiondate <= $less2days)
        {
            $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
            $totalrunningactive     = (int)$runningactive1['SumCount'];
            $totalrunningactiveval  = $runningactive1['SumValue'];
        }
        else if ($transactiondate == $less1day)
        {
            $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
            $runningactive2 = $this->getDayCutOff($less1day, $sitecode); //previous day

            $totalrunningactive     = (int)$runningactive1['SumCount'] + $runningactive2['SumCount'];
            $totalrunningactiveval  = $runningactive1['SumValue'] + $runningactive2['SumValue'];
        }
        else if ($transactiondate == date('Y-m-d'))
        {
            $runningactive1 = $this->getLess2DaysCutOff($less2days, $sitecode); //previuos 2 days
            $runningactive2 = $this->getDayCutOff($less1day, $sitecode); //previous day
            $runningactive3 = $this->getDayCutOff($transactiondate, $sitecode); //date today

            $totalrunningactive     = (int)$runningactive1['SumCount'] + $runningactive2['SumCount'] + $runningactive3['SumCount'];
            $totalrunningactiveval  = $runningactive1['SumValue'] + $runningactive2['SumValue'] + $runningactive3['SumValue'];
        }
        $getTicketRedemptions = $ticketModel->getTicketRedemptions($transactiondate, $dateTo, $sitecode);
        //Generate Excel
        $header = array("TRANSACTION PER CUT OFF FOR TICKETS", "");
        $arrValues = array();
        
        $arrspace = array(0 => "", 
                           1 => "");
        array_push($arrValues, $arrspace);
        //Title Cut-off summary
        $arrSummaryTitle = array(0 => 'TICKET TRANSACTION PER CUT OFF SUMMARY');
        array_push($arrValues, $arrSummaryTitle);
        //TicketTransactionSummaryHeader
        $arrSummaryHeader = array(0 => " ", 
                                  1 => 'NO. OF TICKETS', 
                                  2 => 'VALUE');
        array_push($arrValues, $arrSummaryHeader);
        //Printed tickets
        $arrPrintedTickets = array(0 => 'Printed Tickets Total', 
                                   1 => $printedTicketsCount, 
                                   2 => number_format($printedTicketsValue, 2, ".", ","));
        array_push($arrValues, $arrPrintedTickets);
        //Unused tickets
        $arrUnusedTickets = array(0 => '    Active Tickets for the Day', 
                                   1 => $unusedTicketsCount, 
                                   2 => number_format($unusedTicketsValue, 2, ".", ","));
        array_push($arrValues, $arrUnusedTickets);
        //Running Active tickets
        $arrRunningActive = array(0 => '    Running Active Tickets', 
                                   1 => $totalrunningactive, 
                                   2 => number_format($totalrunningactiveval, 2, ".", ","));
        array_push($arrValues, $arrRunningActive);
        //ticket redemptions title
        $arrTicketRedemptions = array(0 => '    Ticket Redemptions', 
                                   1 => " ", 
                                   2 => " ");
        array_push($arrValues, $arrTicketRedemptions);
        $arrUsedTickets = array(0 => '        Used (Deposit/Reload)', 
                                   1 => $usedTicketsCount, 
                                   2 => number_format($usedTicketsValue, 2, ".", ","));
        array_push($arrValues, $arrUsedTickets);
        $arrEncashedTickets = array(0 => '        Encashed', 
                                   1 => $encashedTicketsCount, 
                                   2 => number_format($encashedTicketsValue, 2, ".", ","));
        array_push($arrValues, $arrEncashedTickets);
        $arrspace = array(0 => " ", 
                           1 => " ");
        array_push($arrValues, $arrspace);
        $arrTicketDetailsHeader = array(0 => "Site/PeGS Code", 
                                  1 => "Terminal Name", 
                                  2 => "Ticket Code", 
                                  3 => "Date and Time Created", 
                                  4 => "Amount", 
                                  5 => "Expiration Date", 
                                  6 => "Status", 
                                  7 => "Date and Time Processed"
        );
        array_push($arrValues, $arrTicketDetailsHeader);
        $arrTicketDetails = array();
        foreach($getTicketRedemptions as $ticketRedemptions)
        {
            $arrTicketDetails = array(0 => $ticketRedemptions['SiteCode'], 
                                      1 => $ticketRedemptions['TerminalName'], 
                                      2 => "'".$ticketRedemptions['TicketCode']."'", 
                                      3 => "'".$ticketRedemptions['DateCreated']."'", 
                                      4 => number_format($ticketRedemptions['Amount'], "2", ".", ","), 
                                      5 => "'".$ticketRedemptions['ValidToDate']."'", 
                                      6 => ($ticketRedemptions['Status'] == 3) ? 'Used' : 'Encashed', 
                                      7 => ($ticketRedemptions['DateEncashed'] == NULL) ? "'".$ticketRedemptions['DateUpdated']."'" : "'".$ticketRedemptions['DateEncashed']."'"
            );
            array_push($arrValues, $arrTicketDetails);
        }
        
        
        //Force Download to Excel File
        $title = str_replace(" ", "_", date('Y-m-d'));
        $excel_obj = new ExportExcel("$title.xls");
        $excel_obj->setHeadersAndValues($header, $arrValues);
        unset($header);
        unset($arrValues);
        $excel_obj->GenerateExcelFile(); //now generate the excel file with the data and headers set
        
        exit();
    }
    /**
     * Get Running Active Tickets in Site Gross Hold in previous 2 days
     * @param type $transdate
     * @param type $sitecode
     * @return type
     */
    private function getLess2DaysCutOff($transdate, $sitecode)
    {
        $siteGHCutOff = new SiteGrossHoldCutOff();

        //Cut Off Date To
        $date = strtotime($transdate);
        $dateTo = date("Y-m-d", strtotime("+1 day", $date));
        
        $getRunningActive = $siteGHCutOff->getActiveTicketsByDate($transdate, $dateTo, $sitecode);
        
        $sumCount = 0;
        $sumValue = 0.00;
        if (count($getRunningActive) > 0)
        {
            foreach ($getRunningActive as $runningActive)
            {
                $sumCount = (int)$sumCount + (int)$runningActive['RunningActiveTicketCount'];
                $sumValue = (float)$sumValue + (float)$runningActive['RunningActiveTickets'];
            }
        }
        return array("SumCount" => $sumCount, "SumValue" => $sumValue);
    }
    /**
     * Get Running Active Tickets in Tickets Table in previous day
     * @param type $transdate
     * @param type $sitecode
     * @return type
     */
    private function getDayCutOff($transdate, $sitecode)
    {
        $ticketModel = new TicketModel();

        //Cut Off Date To
        $date = strtotime($transdate);
        $dateTo = date("Y-m-d", strtotime("+1 day", $date));
        
        $getPrintedTickets  = $ticketModel->getNumberOfPrintedTickets($transdate, $dateTo, $sitecode);
        $getUsedTickets     = $ticketModel->getNumberOfUsedTickets($transdate, $dateTo, $sitecode, 1);
        $getEncashedTickets = $ticketModel->getNumberOfEncashedTickets($transdate, $dateTo, $sitecode, 1);

        $sumCount       = $getPrintedTickets['PrintedTickets'] - ($getUsedTickets['UsedTickets'] + $getEncashedTickets['EncashedTickets']);
        $sumValue       = $getPrintedTickets['Value'] - ($getUsedTickets['Value'] + $getEncashedTickets['Value']);
        
        return array("SumCount" => $sumCount, "SumValue" => $sumValue);
    }
}
?>