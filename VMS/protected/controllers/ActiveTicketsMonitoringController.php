<?php
/**
 * Active Tickets Monitoring Controller
 * @author Mark Kenneth Esguerra
 * @date March 26, 2014
 */
// Set timezone to Asia, Taipei and Philippnes has same timezone.
date_default_timezone_set('Asia/Taipei');
class ActiveTicketsMonitoringController extends VMSBaseIdentity
{
    public $showdialog = false;
    public $showalert = false;
    public $message;
    public $messagealert;

    /********************
     * Active Tickets Monitoring Entry point
     */
    public function actionIndex()
    {
        $model          = new ActiveTicketsMonitoringForm();
        $sites          = new SitesModel();
        $accessrights   = new AccessRights();

        $submenuID  = 33;
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
        if (!$autoselect)
        {
            array_unshift($arrsitecodes, array('SiteID' => null, 'SiteCode' => '-Please Select-'));
        }
        $sitecodelist = CHtml::listData($arrsitecodes, 'SiteID', 'SiteCode');

        $this->render('index', array('model' => $model, 'sitecodes' => $sitecodelist));
    }
    /**
     * Loads all tickets transactions
     */
    public function actionLoadAllTicketInfo()
    {
        $tickets = new TicketModel();

        if (isset($_POST['_sitecode']))
        {
            $response = array();

            $sitecode   = $_POST['_sitecode'];
            $page       = $_POST['page']; // get the requested page
            $limit      = $_POST['rows']; // get how many rows we want to have into the grid
            $sord       = $_POST['sord'];
            $sidx       = $_POST['sidx'];

            if ($sitecode != null)
            {
                //Get Tickets by SiteCodes
                $alltickets = $tickets->getActiveTicketsDetails($sitecode);
                //Get total active tickets
                $ticketcount = count($alltickets);

                if ($ticketcount > 0)
                {
                    if ($ticketcount > 0)
                    {
                        $total_pages = ceil($ticketcount / $limit);
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
                    if ($ticketcount == 0)
                        $start = 0;

                    if ($ticketcount > 0)
                    {
                        $i = 0;
                        $alltickets = $tickets->getActiveTicketsDetails($sitecode, $start, $limit);
                        foreach ($alltickets as $rows)
                        {
                            $validtodate    = date('Y-m-d H:i:s.u', strtotime($rows['ValidToDate']));
                            //$validitystat   = abs((strtotime($validtodate) - strtotime(date('Y-m-d'))))/86400;
                            $dateExpire = new DateTime($validtodate);
                            $now = new DateTime(date('Y-m-d H:i:s.u'));
                            $num_days = intval($now->diff($dateExpire)->format("%d"));
                            $num_hours= intval($now->diff($dateExpire)->format("%H"));
                            $num_minutes= intval($now->diff($dateExpire)->format("%i"));


                            if ($num_days > 1 && $num_days != 0){
                                $show_days = "days";
                            }  else {
                               $show_days = "day";
                            }

                            if ($num_hours > 1 && $num_hours != 0){
                                $show_hours = "hours";
                            }  else {
                               $show_hours = "hour";
                            }

                            if ($num_minutes > 1 && $num_minutes != 0){
                                $show_mins = "minutes";
                            }  else {
                               $show_mins = "minute";
                            }

                                $response["rows"][$i]['id'] = $rows['TicketID'];
                                $response["rows"][$i]['cell'] = array(
                                trim(str_replace("ICSA-", "", $rows['SiteCode'])),
                                $rows['TicketCode'],
                                $rows['DateCreated'],
                                number_format($rows['Amount'], 2),
                                date('Y-m-d', strtotime($rows['ValidToDate'])),
                                // Get the time difference between expiration date and date today
                                $now->diff($dateExpire)->format("%d $show_days, %H $show_hours and %i $show_mins")
                                //$dateExpire->format("%d days, %H hours and %i minutes") //Check Time Zone
                            );
                            $i++;
                        }

                        $response["page"]     = $page;
                        $response["total"]    = $total_pages;
                        $response["records"]  = $ticketcount;
                    }
                    else
                    {
                        $i = 0;
                        $response["page"]     = $page;
                        $response["total"]    = $total_pages;
                        $response["records"]  = $ticketcount;
                    }
                }
            }
            else
            {
                $response['TransCode']  = 2;
                $response['Result']     = "Please select a Site/PeGS Code";
            }
        }
        echo json_encode($response);
    }
    /**
     * Get Total Active Tickets and its value
     * @author Mark Kenneth Esguerra
     * @date May 22, 2014
     */
    public function actionGetTotalTickets()
    {
        $tickets = new TicketModel();

        $sitecode       = $_POST['_sitecode'];
        $totalamount    = 0;
        //Get Tickets by SiteCodes
        $alltickets = $tickets->getActiveTicketsDetails($sitecode);
        //Get total active tickets
        $ticketcount = (int)count($alltickets);
        //Get ticket amount
        foreach ($alltickets as $tickets)
        {
            $totalamount += $tickets['Amount'];
        }
        $response = array();

        $response['TotalCount']     = number_format($ticketcount);
        $response['TotalAmount']    = number_format($totalamount, "2",".", ",");

        //Log to audit trail
        $aid = Yii::app()->session['AID'];
        $transDetails = ' by AID: ' . $aid . ' SiteCode: ' . $sitecode;
        AuditLog::logTransactions(38, $transDetails);

        echo json_encode($response);
    }
    /**
     * Export to Excel (Tickets)
     * @date May 23, 2014
     * @author Mark Kenneth Esguerra
     */
    public function actionExporttoexcelticket()
    {

    }
}
?>
