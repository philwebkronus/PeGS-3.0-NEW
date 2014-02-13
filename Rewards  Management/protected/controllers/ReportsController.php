<?php
/**
 * Reports Controller
 * Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Sep-03-2013
 * Philweb Corp.
 */
class ReportsController extends Controller
{
    public $showdialog = false;
    public $message;
    public $filter;
    public $flip;
    public $reporttype;
    public $itemtype;
    public $wizard;
    public $category;
    
    CONST DAILY = 0;
    CONST WEEKLY = 1;
    CONST MONTHLY = 2;
    CONST QUARTERLY = 3;
    CONST YEARLY = 4;

    public function actions()
    {
        return array(
        // captcha action renders the CAPTCHA image displayed on the contact page
        'captcha'=>array(
            'class'=>'CCaptchaAction',
            'backColor'=>0xFFFFFF,
        ),
        // page action renders "static" pages stored under 'protected/views/site/pages'
        // They can be accessed via: index.php?r=site/page&view=FileName
        'page'=>array(
            'class'=>'CViewAction',
            ),
        );
    }
    /**
     * Rewards Redemption Controller
     */
    public function actionIndex()
    {
        $report                     = new ReportForm();
        $itemredemption             = new ItemRedemptionLogsModel();
        $couponredemption           = new CouponRedemptionLogsModel();
        $playerclassification       = new PlayerClassificationModel();
        $refrewardtype              = new RewardTypeModel();
        $rewarditem                 = new RewardItemsModel();
        $refpartners                = new RefPartnerModel();
        $categorymodel              = new CategoryModel();
        $audittrailmodel            = new AuditTrailModel();

        if (isset($_POST['ReportForm']))
        {
            $report->attributes = $_POST['ReportForm']; //Pass consolidated values into model attributes

            $reporttype = $report->report_type;
            $category   = $report->category;
            $filter     = $report->filter_by;
            $particular = $report->particular;
            $player     = $report->player_segment;
            $date_from  = $report->date_from;
            $date_to    = $report->date_to;
            $coverage   = $report->date_coverage;
            //Error Handling
            if ($reporttype == "" || strlen($reporttype) == 0)
            {
                $this->showdialog = true;
                $this->message = "Please select a Report type";
                $this->filter = $filter;
                $this->wizard = 1;
                $this->category = $category;
            }
            else if (strlen($category) == 0 || $category == "")
            {
                $this->showdialog = true;
                $this->message = "Please select a Category";
                if ($filter != NULL)
                    $this->filter = $filter;
                $this->wizard = 2;
                $this->category = $category;
            }
            else if (strlen($filter) == 0 || $filter == "")
            {
                $this->showdialog = true;
                $this->message = "Please select a Filter";
                $this->wizard = 2;
                $this->category = $category;
            }
            else if ((strlen($particular) == 0 || $particular == "") && $category == 0)
            {
                $this->showdialog = true;
                $this->message = "Please choose a Particular";
                $this->filter = $filter;
                $this->wizard = 2;
                $this->category = $category;
            }
            else if ((strlen($player) == 0 || $player == "") && $category == 0)
            {
                $this->showdialog = true;
                $this->message = "Please select a Player Segment";
                $this->filter = $filter;
                $this->wizard = 2;
                $this->category = $category;
            }
            else if ($coverage == "" || strlen($coverage) == 0)
            {
                $this->showdialog = true;
                $this->message = "Please select Date Coverage";
                $this->filter = $filter;
                $this->wizard = 3;
                $this->category = $category;
            }
            else if ((strlen($date_from) == 0 || $date_from == "") || (strlen($date_to) == 0 || $date_to == ""))
            {
                $this->showdialog = true;
                $this->message = "Please select From/To Date";
                $this->filter = $filter;
                $this->wizard = 3;
                $this->category = $category;
            }
            else if (strtotime($date_from) > strtotime($date_to))
            {
                $this->showdialog = true;
                $this->message = "Invalid Date Range";
                $this->filter = $filter;
                $this->wizard = 3;
                $this->category = $category;
            }
            else
            {
                $result = $this->checkDateRange($coverage, $date_from, $date_to);
                if ($result['ErrorCode'] == 0)
                {
                    $this->showdialog = true;
                    $this->message = $result['ErrorMsg'];
                    $this->filter = $filter;
                    $this->category = $category;
                }
                else
                {
                    //Check whether Raffle, Rewards E-coupon or BOTH (ALL) has been chosen
                    if ($category == RewardTypeModel::RAFFLE_E_COUPONS)
                    {
                        //For Raffle E-Coupon
                        $ret_query[] = $couponredemption->inquiry($reporttype, $filter, $particular, $player, $date_from, $date_to);
                    }
                    else if ($category == RewardTypeModel::REWARDS_E_COUPONS)
                    {
                        //For Rewards E-Coupons
                        $ret_query[] = $itemredemption->inquiry($reporttype, $filter, $particular, $player, $date_from, $date_to);

                    }
                    else if ($category == RewardTypeModel::ALL)
                    {
                        //Both Raffle and Rewards E-Coupon
                        $ret_query[] = $itemredemption->inquiry($reporttype, $filter, $particular, $player, $date_from, $date_to, 1);
                        $ret_query[] = $couponredemption->inquiry($reporttype, $filter, $particular, $player, $date_from, $date_to, 1);

                    }
                    switch ($coverage)
                    {
                        case self::DAILY:
                            $start  = $result['StartDate'];
                            $end    = $result['EndDate'];
                            break;
                        case self::WEEKLY:
                            $start  = $result['StartDate'];
                            $end    = $result['EndDate'];
                            break;
                        case self::QUARTERLY:
                            $start  = $result['StartQuarter'];
                            $end    = $result['EndQuarter'];
                            break;
                        case self::MONTHLY:
                            $start  = $result['StartMonth'];
                            $end    = $result['EndMonth'];
                            break;
                        case self::YEARLY:
                            $start  = $result['StartYear'];
                            $end    = $result['EndYear'];
                            break;
                    }
                    //Get Player Class Description, category, filter
                    switch ($player)
                    {
                        case 0: $playerclass = "Regular"; break;
                        case 1: $playerclass = "VIP"; break;
                        case 2: $playerclass = "All"; break;
                        default: $playerclass = ""; break;
                    }
                    $rewardtype     = $refrewardtype->getRewardTypeDesp($category);
                    if ($rewardtype == "")
                    {
                        $rewardtype = "All";
                    }
                    //Get Particular
                    $particularID = substr($particular, 1);
                    //Check filter
                    switch ($filter)
                    {
                        case 0: //All
                            $filterby = "All";
                            //Get the specified particular name if All
                            $filtertype = substr($particular, 0, 1);
                            switch ($filtertype)
                            {
                                case "I": //Item
                                    $filterby = "Item";
                                    $arrrewardname = $rewarditem->getRewardName($particularID);
                                    $particularname = $arrrewardname['ItemName'];
                                    break;
                                case "P": //Partner
                                    $filterby = "Partner";
                                    $arrpartner = $refpartners->getPartnerName($particularID);
                                    $particularname = $arrpartner[0]['PartnerName'];
                                    break;
                                case "C":
                                    $filterby = "Category";
                                    $particularname = $categorymodel->getCategoryDescription($particularID);
                                    break;
                                case "A":
                                    $filterby = "All";
                                    $particularname = "All";
                                    break;
                            }
                            break;
                        case 1: //Item
                            $filterby = "Item";
                            if ($particularID != 0)
                            {
                                $arrrewardname = $rewarditem->getRewardName($particularID);
                                $particularname = $arrrewardname['ItemName'];
                            }
                            else
                            {
                                $particularname = "All";
                            }
                            break;
                        case 2: //Partner
                            $filterby = "Partner";
                            if ($particularID != 0)
                            {
                                $arrpartner = $refpartners->getPartnerName($particularID);
                                $particularname = $arrpartner[0]['PartnerName'];
                            }
                            else
                            {
                                $particularname = "All";
                            }
                            break;
                        case 3: //Category
                            $filterby = "Category";
                            if ($particularID != 0)
                            {
                                $particularname = $categorymodel->getCategoryDescription($particularID);
                            }
                            else
                            {
                                $particularname = "All";
                            }
                            break;

                    }
                    $arrAdditionalInfo = array();
                    $arrAdditionalInfo['RewardType']    = $rewardtype;
                    $arrAdditionalInfo['Filter']        = $filterby;
                    $arrAdditionalInfo['Particular']    = $particularname;
                    $arrAdditionalInfo['PlayerClass']   = $playerclass;
                    //Generate Report
                    $result = $this->Generate($reporttype, $ret_query, $player, $coverage, $arrAdditionalInfo, $date_from, $date_to, $start, $end);
                    if ($result['TransCode'] == 1)
                    {
                        //Log to Audit trail
                        if (Yii::app()->session['AccountType'] == 13)
                        {
                            switch ($reporttype)
                            {
                                case 1:
                                    $auditfunction = RefAuditFunctionsModel::MARKETING_RPT_REWARDS_REDEMPTION;
                                    $auditfunctionname = "MARKETING RPT Rewards Redemption";
                                    break;
                                case 2:
                                    $auditfunction = RefAuditFunctionsModel::MARKETING_RPT_UNIQUE_MEMBER_PARTICIPATION;
                                    $auditfunctionname = "MARKETING RPT Unique Member Participation";
                                    break;
                                case 3:
                                    $auditfunction = RefAuditFunctionsModel::MARKETING_RPT_REWARDS_POINTS_USAGE;
                                    $auditfunctionname = "MARKETING RPT Rewards Point Usage";
                                    break;
                            }
                        }
                        else if (Yii::app()->session['AccountType'] == 9)
                        {
                            switch ($reporttype)
                            {
                                case 1:
                                    $auditfunction = RefAuditFunctionsModel::AS_RPT_REWARDS_REDEMPTION;
                                    $auditfunctionname = "AS RPT Rewards Redemption";
                                    break;
                                case 2:
                                    $auditfunction = RefAuditFunctionsModel::AS_RPT_UNIQUE_MEMBER_PARTICIPATION;
                                    $auditfunctionname = "AS RPT Unique Member Participation";
                                    break;
                                case 3:
                                    $auditfunction = RefAuditFunctionsModel::AS_RPT_REWARDS_POINTS_USAGE;
                                    $auditfunctionname = "AS RPT Rewards Points Usage";
                                    break;
                            }
                        }
                        $audittrailmodel->logEvent($auditfunction, $auditfunctionname, array('SessionID' => Yii::app()->session['SessionID'],
                                                                         'AID' => Yii::app()->session['AID']));
                        //Redirect to VIEW
                        $this->render('view', array('reporttype' => $result['ReportType'],
                                                    'coverage' => $result['Coverage'],
                                                    'start' => $date_from,
                                                    'end' => $date_to,
                                                    'additionalInfo' => $arrAdditionalInfo, 
                                                    'exportHeader' => $result['ExcelHeader'], 
                                                    'exportValues' => $result['ExcelValues']
                                                    ));
                        exit();
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                        $this->category = $category;
                    }
                }
            }
        }
        $this->render('index',array('model'=>$report));
    }

    public function Generate($type, $query, $player, $coverage, $arrAdditionalInfo, $datefrom, $dateto, $start = NULL, $end = NULL)
    {
        $this->flip = true;
        Yii::import('application.extensions.*');

        require_once('jpgraph-3.5.0b1/src/jpgraph.php');
        require_once('jpgraph-3.5.0b1/src/jpgraph_bar.php');

        $itemredemption = new ItemRedemptionLogsModel();
        //Dimensions
        //Every dimensions have different DATE_FORMAT for have
        //proper labeling in the graph. Also in the file to be
        //generated
        $dimensions = array('0' => array('Title'=>'Daily','DateFunction'=>'DATE',
                                         'FileName'=>'images/graph-daily.png',
                                         'DateLabel' => 'DATE_FORMAT(a.DateCreated, "%Y-%m-%d") as DateLabel'),
                            '1' => array('Title'=>'Weekly','DateFunction'=>'DATE',
                                         'FileName'=>'images/graph-weekly.png',
                                         'DateLabel' => "DATE_FORMAT(a.DateCreated,'%Y-%m-%d') AS DateLabel"),
                            '2' => array('Title'=>'Monthly','DateFunction'=>'MONTH',
                                         'FileName'=>'images/graph-monthly.png',
                                         'DateLabel' => 'DATE_FORMAT(a.DateCreated, "%c") AS DateLabel'),
                            '3' => array('Title'=>'Quarterly','DateFunction'=>'QUARTER',
                                         'FileName'=>'images/graph-quarterly.png',
                                         'DateLabel' => "QUARTER(a.DateCreated) AS DateLabel"),
                            '4' => array('Title'=>'Yearly','DateFunction'=>'YEAR',
                                         'FileName'=>'images/graph-yearly.png',
                                         'DateLabel' => "YEAR(a.DateCreated) AS DateLabel")
        );
        //Name report type
        switch ($type)
        {
            case 1: $reporttype = "Rewards Redemption";
                break;
            case 2: $reporttype = "Unique Member Participation";
                break;
            case 3: $reporttype = "Rewards Points Usage";
                break;
            default: $reporttype = "";
                break;
        }

        $arrData = array();
        $d = $coverage;
        $from = $datefrom;
        $to = $dateto;
        
        //Check if the chosen Category (E-Coupons) is (Raffle or Rewards) or ALL
        //If ALL, there must be 2 queries. Else, only 1.
        if (count($query) == 1)
        {
            //if NULL, return false to display No result found
            if ($query[0] != NULL)
            {
                //Run Query for Daily, Monthly, Quarterly and Yearly since Weekly
                //coverage has special case
                if ($d != self::WEEKLY)
                {
                    //Append other MySQL function for query
                    $queryMonth = $query[0][0]." ".$dimensions[$d]['DateLabel'].", YEAR(a.DateCreated) as Year ".$query[0][1].
                                            " ".$query[0][2]." GROUP BY  YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated)
                                             ORDER BY YEAR(a.DateCreated), (a.DateCreated) ASC";
                    //var_dump($queryMonth);exit;
                    $result = $itemredemption->runQuery($queryMonth, $player);
                }
                //Weekly Coverage
                else
                {
                    $numweeks = (abs(strtotime($end) - strtotime($start))/86400) / 7;
                    if (is_float($numweeks))
                    {
                        $secondtolast = (int)$numweeks;
                        $numweeks = (int)round($numweeks);
                        if ($numweeks == $secondtolast)
                            $numweeks = $numweeks + 1;
                        //minimum number of week is 1 not 0
                        if ($numweeks == 0)
                            $numweeks = 1;
                    }
                    else
                    {
                        $numweeks = (int)round($numweeks);
                        $secondtolast = $numweeks;
                        //minimum number of week is 1 not 0
                        if ($numweeks == 0)
                            $numweeks = 1;

                    }
                    $result = array();
                    $datefrom = $start;
                    $nodata = 0;
                    for ($w = 0; $numweeks > $w; $w++)
                    {
                        if ($secondtolast == $w)
                        {
                            $dateto = $end;
                        }
                        else
                        {
                            $dateto = date("Y-m-d", strtotime("+6 day", strtotime($datefrom)));
                        }
//                        //Query
                        $queryWeek = $query[0][0]." ".$dimensions[$d]['DateLabel'].", YEAR(a.DateCreated) as Year ".$query[0][1].
                                            " a.DateCreated >= '".$datefrom." 00:00:00' AND a.DateCreated <= '".$dateto." 23:59:59'
                                            ORDER BY YEAR(a.DateCreated), (a.DateCreated) ASC";
                        $weekresult = $itemredemption->runQuery($queryWeek, $player);
                        $arrkey = array_keys($weekresult[0]);
                        $key1 = $arrkey[0];
                        //Check if retrieved count is zero
                        if ($weekresult[0][$key1] == 0)
                        {
                            $nodata++;
                        }
                        $result[] = $weekresult;
                        $arrWeeks[] = array('DateFrom' => date('m-d-y', strtotime($datefrom)),
                                            'DateTo' => date('m-d-y', strtotime($dateto)));
                        
                        $datefrom = date("Y-m-d", strtotime("+1 day", strtotime($dateto)));
                    }
                    //Check if all weeks has 0 values
                    if ($nodata == count($arrWeeks))
                    {
                        $result = array();
                    }
                }
            }
        }
        //ALL
        else if (count($query) == 2)
        {
            //For weekly Coverage
            if ($d != self::WEEKLY)
            {
                //Special case for getting the Unique Member Participation if filter is ALL
                //Get the distinct ID in both itemredemptionlogs and couponredemptionlogs table
                if ($type != 2)
                {
                    $finalQuery = "SELECT SUM(Total) as Count, a.DateCreated, ".$dimensions[$d]['DateLabel'].", YEAR(a.DateCreated) as Year"."
                                                                                       FROM (".$query[0][0]." ".
                                                                                               $query[0][1]." ".
                                                                                               $query[0][2]." 
                                                                                               GROUP BY YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated) 
                                                                                               UNION ALL "
                                                                                              .$query[1][0]." ".
                                                                                               $query[1][1]." ".
                                                                                               $query[1][2]." 
                                                                                               GROUP BY YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated)) AS a"
                                   ." GROUP BY YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated)
                                      ORDER BY YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated) ASC";
                }
                else
                {
                    $finalQuery = "SELECT COUNT(DISTINCT(Total)) as Total, DateCreated, DateLabel, Year FROM (
                                        SELECT Total, DateCreated, ".$dimensions[$d]['DateFunction']."(DateCreated) as DateLabel, YEAR(DateCreated) as Year"."
                                                                                       FROM (".$query[0][0]." ".
                                                                                               $query[0][1]." ".
                                                                                               $query[0][2]." 
                                                                                               UNION ALL "
                                                                                              .$query[1][0]." ".
                                                                                               $query[1][1]." ".
                                                                                               $query[1][2]." 
                                                                                               ) AS x"
                                   ." GROUP BY DateCreated) r GROUP BY ".$dimensions[$d]['DateFunction']."(DateCreated) 
                                      ORDER BY YEAR(DateCreated), ".$dimensions[$d]['DateFunction']."(DateCreated) ASC";
                }
                $result = $itemredemption->runQuery($finalQuery, $player);
            }
            else
            {
                $numweeks = (abs(strtotime($end) - strtotime($start))/86400) / 7;
                if (is_float($numweeks))
                {
                    $secondtolast = (int)$numweeks;
                    $numweeks = (int)round($numweeks);
                    if ($numweeks == $secondtolast)
                        $numweeks = $numweeks + 1;
                    //minimum number of week is 1 not 0
                    if ($numweeks == 0)
                        $numweeks = 1;
                }
                else
                {
                    $numweeks = (int)round($numweeks);
                    $secondtolast = $numweeks;
                    //minimum number of week is 1 not 0
                    if ($numweeks == 0)
                        $numweeks = 1;

                }
                $result = array();
                $datefrom = $start;
                $nodata = 0;
                for ($w = 0; $numweeks > $w; $w++)
                {
                    if ($secondtolast == $w)
                    {
                        $dateto = $end;
                    }
                    else
                    {
                        $dateto = date("Y-m-d", strtotime("+6 day", strtotime($datefrom)));
                    }
                    //Special case for getting the Unique Member Participation if filter is ALL
                    //Get the distinct ID in both itemredemptionlogs and couponredemptionlogs table
                    if ($type != 2)
                    {
                        $queryWeek = "SELECT SUM(Total) as Count, a.DateCreated, ".$dimensions[$d]['DateLabel'].", YEAR(a.DateCreated) as Year"."
                                                                                       FROM (".$query[0][0]." ".
                                                                                               $query[0][1]." ".
                                                                                               "a.DateCreated >= '".$datefrom." 00:00:00'
                                                                                                AND a.DateCreated <= '".$dateto." 23:59:59' UNION ALL "
                                                                                              .$query[1][0]." ".
                                                                                               $query[1][1]." ".
                                                                                               "a.DateCreated >= '".$datefrom." 00:00:00'
                                                                                                AND a.DateCreated <= '".$dateto." 23:59:59' ) AS a"
                                   ." ORDER BY YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated) ASC";
                        
                    }
                    else
                    {
                        $queryWeek = "SELECT COUNT(DISTINCT(Total)) as Total, DateCreated, DateLabel, Year FROM (
                                        SELECT Total, DateCreated, ".$dimensions[$d]['DateFunction']."(DateCreated) as DateLabel, YEAR(DateCreated) as Year"."
                                                                                       FROM (".$query[0][0]." ".
                                                                                               $query[0][1]." ".
                                                                                               "a.DateCreated >= '".$datefrom." 00:00:00'
                                                                                               AND a.DateCreated <= '".$dateto." 23:59:59' UNION ALL "
                                                                                              .$query[1][0]." ".
                                                                                               $query[1][1]." ".
                                                                                               "a.DateCreated >= '".$datefrom." 00:00:00'
                                                                                                AND a.DateCreated <= '".$dateto." 23:59:59' ) AS x"
                                   ." GROUP BY DateCreated) r";   
                    }
                    $weekresult = $itemredemption->runQuery($queryWeek, $player);
                    
                    $arrkey = array_keys($weekresult[0]);
                    $key1 = $arrkey[0];
                    //Check if retrieved count is zero
                    if ($weekresult[0][$key1] == 0)
                    {
                        $nodata++;
                    }
                    $result[] = $weekresult;
                    $arrWeeks[] = array('DateFrom' => date('m-d-y', strtotime($datefrom)),
                                        'DateTo' => date('m-d-y', strtotime($dateto)));

                    $datefrom = date("Y-m-d", strtotime("+1 day", strtotime($dateto)));
                }
                //Check if all weeks has 0 values
                if ($nodata == count($arrWeeks))
                {
                    $result = array();
                }
            }
        }
        if (count($result) > 0)
        {
            //Get Array Keys
            if ($d != self::WEEKLY)
                $arrkey = array_keys($result[0]);
            else
                $arrkey = array_keys($result[0][0]);
            $key1 = $arrkey[0];
            $key2 = $arrkey[2]; //DateLabel - Index 1 is the dateCreated
            $year = $arrkey[3];
            //Check if there were results found
            $datay = array();
            //Get Start date and max bars of each date coverage
            switch ($d)
            {
                case self::DAILY: //Daily
                    //Get all date/s in between the date range
                    $max = 7;
                    $arrdays = array();
                    $arrdays[] = $start;
                    do
                    {
                        $date = date("Y-m-d", strtotime("+1 day", strtotime($start)));
                        if (strtotime($date) <= strtotime($end))
                            $arrdays[] =  $date;
                        $start = $date;
                        $max--;
                    }
                    while ($max > 0);
                    if (count($arrdays) == 0)
                        $arrdays[] = $start;
                    break;
                case self::MONTHLY: //Monthly
                    $month = $start;
                    $max = 12;
                    break;
                case self::QUARTERLY: //Quarterly
                    $quarter = $start;
                    $max = 4;
                    break;
            }
            //Get maximum bars depending on date coverage
            if ($d != 4 && $d != 0 && $d != 1)
            {
                if ($start == $end)
                {
                    if ($d == 2) //Month
                        $vbars = 1;
                    else if ($d == 3) //Quarterly
                        $vbars = 1;
                }
                else if ($start > $end)
                {
                    $vbars = ($max - $start) + 1 + $end;
                }
                else
                {
                    $vbars = ($end - $start) + 1;
                }
            }
            else
            {
                if ($d == self::DAILY)
                {
                    $vbars = count($arrdays);
                    $date_i = 0;
                    $dd = 0;
                }
                else if ($d == self::WEEKLY)
                {
                    $vbars = count($arrWeeks);
                }
                else
                {
                    //if the date coverage is only on a year
                    if ($start == $end)
                        $vbars = 1;
                    else
                        $vbars = 2;
                }
            }
            //Start retrieving of Data and Label
            for ($i = 0; $vbars > $i; $i++)
            {
                $notnull = false;
                switch($d)
                {
                    //Daily
                    case self::DAILY:
                        if (!isset($result[$i][$key2]))
                        {
                            if (isset($arrdays[$date_i]))
                            {
                                $datay[]    = 0;
                                $lbl[]      = date("m-d-Y", strtotime($arrdays[$date_i]));
                            }
                            $date_i++;
                        }
                        else
                        {
                            if ($result[$i][$key2] == $arrdays[$date_i])
                            {
                                $datay[] = $result[$i][$key1];
                                $lbl[] = date("m-d-Y", strtotime($result[$i][$key2]));

                                $date_i++;
                            }
                            else
                            {
                                $datediff = round(abs(strtotime($result[$i][$key2]) - strtotime($arrdays[$date_i]))/86400);
                                $dd = 0;
                                while ($dd < $datediff)
                                {
                                    $datay[]    = 0;
                                    $lbl[]      = date("m-d-Y", strtotime($arrdays[$date_i]));

                                    $date_i++;
                                    $dd++;
                                }
                                $datay[] = $result[$i][$key1];
                                $lbl[] = date("m-d-Y",  strtotime($result[$i][$key2]));
                                $date_i++;
                            }
                            $this->flip = false;
                        }
                        break;
                    //Weekly
                    case self::WEEKLY:
                        if ($result[$i][0][$key1] == 0)
                        {
                            $lbl[] = $arrWeeks[$i]['DateFrom']."\n".$arrWeeks[$i]['DateTo'];
                            $datay[] = 0;
                        }
                        else
                        {
                            $lbl[] = $arrWeeks[$i]['DateFrom']."\n".$arrWeeks[$i]['DateTo'];
                            $datay[] = $result[$i][0][$key1];
                        }
                        $this->flip = false;
                        break;
                    //Monthly
                    case self::MONTHLY:
                        if (!isset($result[$i][$key2]))
                        {
                            if (count($lbl) < $vbars)
                            {
                                if ($month > 12)
                                {
                                    $month  = 1;
                                    $getLabel = $this->getMonthName($month)."\n".(($result[0][$year]) + 1);
                                }
                                else
                                {
                                    $getLabel = $this->getMonthName($month)."\n".($result[0][$year] + 1);
                                }
                                $this->flip = false;
                                $getData = 0;

                                $notnull = true;
                            }
                            $month++;
                        }
                        else
                        {
                            if ($month > 12)
                            {
                                $month = 1;
                            }
                            if ($result[$i][$key2] == $month)
                            {
                                $getLabel = $this->getMonthName($result[$i][$key2])."\n".$result[$i][$year];
                                $this->flip = false;
                                $getData = $result[$i][$key1];

                                $datay[]    = $getData; //Put the retrieved data in array dataY
                                $lbl[]      = $getLabel;

                                $month++;
                            }
                            else
                            {
                                if ($month > $result[$i][$key2])
                                {
                                    $blank = (12 - $month) + $result[$i][$key2];
                                }
                                else
                                {
                                    $blank = ($result[$i][$key2] - $month);
                                }
                                $_month = $month;
                                while ($blank > 0)
                                {
                                    $getLabel = $this->getMonthName($_month)."\n".$result[$i][$year];
                                    $this->flip = false;
                                    $getData    = 0; //No Data

                                    $datay[]    = $getData; //Put the retrieved data in array dataY
                                    $lbl[]      = $getLabel;

                                    $blank--;
                                    $_month++;
                                    if ($_month > 12)
                                        $_month = 1;
                                }
                                if ($month <= 12)
                                {
                                    $getLabel = $this->getMonthName($result[$i][$key2])."\n".$result[$i][$year];
                                    $this->filter = false;
                                    $this->flip = false;
                                    $getData = $result[$i][$key1];

                                    $datay[]    = $getData; //Put the retrieved data in array dataY
                                    $lbl[]      = $getLabel;

                                    $month = $result[$i][$key2];
                                    $month++;
                                }
                                else
                                {
                                    $month = 0;
                                }
                                if ($month > 12)
                                {
                                    $month = 1;
                                }
                            }
                        }
                        break;
                    //Quarterly
                    case self::QUARTERLY:
                        //If 0 data
                        if (!isset($result[$i][$key2]))
                        {
                            if (count($lbl) < $vbars)
                            {
                                if ($quarter > 4)
                                {
                                    $quarter  = 1;
                                    $getLabel = $this->identifyQuarter($quarter)."\n      ".(($result[0][$year]) + 1);

                                }
                                else
                                {
                                    $getLabel = $this->identifyQuarter($quarter)."\n      ".($result[0][$year] + 1);
                                }
                                $this->flip = false;
                                $getData = 0;

                                $notnull = true;
                            }
                            $quarter++;
                        }
                        else
                        {
                            if ($quarter > 4)
                            {
                                $quarter = 1;
                            }
                            if ($result[$i][$key2] == $quarter)
                            {
                                $getLabel = $this->identifyQuarter($result[$i][$key2])."\n      ".$result[$i][$year];
                                $this->flip = false;
                                $getData = $result[$i][$key1];

                                $datay[]    = $getData; //Put the retrieved data in array dataY
                                $lbl[]      = $getLabel;

                                $quarter++;
                            }
                            else
                            {
                                if ($quarter > $result[$i][$key2])
                                {
                                    $blank = (4 - $quarter) + $result[$i][$key2];
                                }
                                else
                                {
                                    $blank = ($result[$i][$key2] - $quarter);
                                }
                                $_quarter = $quarter;
                                while ($blank > 0)
                                {

                                    $getLabel = $this->identifyQuarter($_quarter)."\n      ".$result[$i][$year];
                                    $this->flip = false;
                                    $getData    = 0; //No Data

                                    $datay[]    = $getData; //Put the retrieved data in array dataY
                                    $lbl[]      = $getLabel;

                                    $blank--;
                                    $_quarter++;
                                    if ($_quarter > 4)
                                        $_quarter = 1;
                                }
                                if ($quarter <= 4)
                                {
                                    $getLabel = $this->identifyQuarter($result[$i][$key2])."\n      ".$result[$i][$year];
                                    $this->flip = false;
                                    $getData = $result[$i][$key1];

                                    $datay[]    = $getData; //Put the retrieved data in array dataY
                                    $lbl[]      = $getLabel;

                                    $quarter = $result[$i][$key2];
                                    $quarter++;
                                }
                                else
                                {
                                    $quarter = 0;
                                }
                                if ($quarter > 4)
                                {
                                    $quarter = 1;
                                }
                            }
                        }
                        break;
                    //Yearly
                    case self::YEARLY:
                        if (!isset($result[$i][$year]))
                        {
                            if (count($datay) < 2)
                            {
                                $datay[] = 0;
                                $lbl[] = $start;
                            }
                            $start++;
                        }
                        else
                        {
                            if ($result[$i][$year] == $start)
                            {
                                $datay[]    = $result[$i][$key1];
                                $lbl[]      = $result[$i][$year];
                                $this->flip = false;
                                
                                $start++;
                            }
                            else
                            {
                                $datay[]    = 0;
                                $lbl[]      = $start;
                                $this->flip = false;
                                
                                $start++;
                                
                                $datay[]    = $result[$i][$key1];
                                $lbl[]      = $start;
                            }
                        }
                        break;
                }
                if ($notnull)
                {
                    $datay[]    = $getData; //Put the retrieved data in array dataY
                    $lbl[]      = $getLabel;
                }
            }
        }
        else
        {
            return false;
        }
        // Create the graph. These two calls are always required
        $graph = new Graph(710, 600);
        $graph->SetScale('textint');

        // Add a drop shadow
        $graph->SetShadow();

        // Adjust the margin a bit to make more room for titles
        $graph->SetMargin(100,30,20,100);
        //Set Title
        $graph->title->SetFont(FF_FONT1,FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD, 12);
        
        $graph->xaxis->SetTitleMargin(45);
        $graph->xaxis->SetTitle($reporttype." - ".$dimensions[$d]['Title'].' Statistics','middle');
        //Set Labels
        $graph->xaxis->SetTickLabels($lbl);
        $graph->xaxis->SetLabelAlign('center','center');
        $graph->xaxis->SetLabelMargin(25);
        $graph->xaxis->scale->ticks->Set(1, 2);

        $graph->yaxis->SetLabelMargin(5);
        $graph->yaxis->SetLabelFormatCallback('number_format');
        $graph->yaxis->SetLabelFormat('%s');
        
        $graph->yaxis->HideLine(false);
        //Check if flip is true
        if ($this->flip)
        {
            $graph->xaxis->SetLabelAngle(90);
        }
        $graph->yaxis->scale->SetGrace(10);
        // Create a bar pot
        $bplot = new BarPlot($datay);
        // Adjust fill color
        $bplot->SetFillColor('orange');
        $graph->Add($bplot);
        // Display the graph
        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
        $fileName = 'images/graph_'.Yii::app()->session['AID'].'.png';
        $graph->img->Stream($fileName);

        //------------------------Export to Excel------------------------------------------//
        $completevalues = array();
        $rewardtype = "";
        $filter = "";
        $particular = "";
        $playerclass = "";
        $total = 0;
        //distribute filters info
        if (is_array($arrAdditionalInfo))
        {
            $rewardtype     = $arrAdditionalInfo['RewardType'];
            $filter         = $arrAdditionalInfo['Filter'];
            $particular     = $arrAdditionalInfo['Particular'];
            $playerclass    = $arrAdditionalInfo['PlayerClass'];
        }
        //Header
        $header = array($dimensions[$d]['Title']." Statistics", "");
        //New Line
        $newline = array(0 => "", 1 => "");

        $datecoverage = array (0 => 'Date Coverage',
                              1 => $dimensions[$d]['Title']." Statistics");
        array_push($completevalues, $datecoverage);
        $daterange = array (0 => 'Date Range: ', 
                            1 => date("m-d-Y", strtotime($from))."  -  ".date("m-d-Y", strtotime($to)));
        array_push($completevalues, $daterange);
        array_push($completevalues, $newline);
        $arrrewardtype = array (0 => 'Reward Type: ', 
                                1 => $rewardtype);
        array_push($completevalues, $arrrewardtype);
        $arrfilter = array (0 => 'Filter By: ', 
                            1 => $filter);
        array_push($completevalues, $arrfilter);
        $arrparticular = array (0 => 'Particular: ', 
                                1 => $particular);
        array_push($completevalues, $arrparticular);
        $arrplayerclass = array (0 => 'Player Classification: ', 
                                 1 => $playerclass);
        array_push($completevalues, $arrplayerclass);
        array_push($completevalues, $newline);
        
        //Retrieve array data and join to array
        for ($c = 0; count($datay) > $c; $c++)
        {
            $arrresult = array( 0 => $lbl[$c], 
                                1 => number_format($datay[$c]));
            $total = $total + $datay[$c];
            array_push($completevalues, $arrresult);
            
            unset($arrresult);
        }
        array_push($completevalues, $newline);
        //Exclude Total in excel in the Unique Member Participation
        if ($type != 2)
        {
            $arrtotal = array (0 => 'Total', 
                               1 => number_format($total));
            array_push($completevalues, $arrtotal);
        }
        return array('TransCode' => 1,
                     'ReportType' => $reporttype,
                     'Coverage' => $dimensions[$d]['Title'],
                     'StartDate' => $start,
                     'EndDate' => $end, 
                     'ExcelHeader' => $header, 
                     'ExcelValues' => $completevalues);
        
        //Clear Arrays!
        unset($completevalues);
        unset($arrrewardtype);
        unset($arrparticular);
        unset($arrplayerclass);
        unset($arrfilter);
        unset($datecoverage);
        unset($daterange);
        unset($arrresult);
        unset($datay);
        unset($lbl);
        unset($result);
        unset($queryMonth);
        unset(Yii::app()->session['inquiry']);
        unset($arrData);
    }
    public function actionView()
    {
        $this->render('view');
    }
    public function actionExport()
    {
        $this->render('export');
    }
    //Load Particulars for Choose Particular DropDownList
    public function actionLoadParticulars()
    {
        $itemtype = $_POST['itemtype'];
            $this->itemtype = $itemtype;
        echo "<option value=''>Select Particular</option>";
        //$rewards        = new RewardsForm();
        $category       = new CategoryModel();
        $ref_partners   = new RefPartnerModel();
        $rewarditems    = new RewardItemsModel();

        $type = $_POST['ReportForm_filter_by'];
        //Check if type is blank. Exit if blank.
        if ($type != '')
        {
            //Indicate the indexes by type since they
            //have different field names.
            switch($type)
            {
                case 1:
                    $index['value'] = "RewardItemID";
                    $index['text'] = "ItemName";
                    $append = "I";
                    //Select all active items
                    $arrParticulars = $rewarditems->selectRewardItems($itemtype);

                    break;
                case 2:
                    $index['value'] = "PartnerID";
                    $index['text'] = "PartnerName";
                    $append = "P";
                    //Select all active partners
                    $arrParticulars = $ref_partners->selectPartners();

                    break;
                case 3:
                    $index['value'] = "CategoryID";
                    $index['text'] = "Description";
                    $append = "C";
                    //Select all active categories
                    $arrParticulars = $category->selectCategories();
                    break;
                case 0:
                    //If all is chosen merge all active items, partners and categories
                    $arrParticulars[] = $rewarditems->selectRewardItems($itemtype);
                    $arrParticulars[] = $ref_partners->selectPartners();
                    $arrParticulars[] = $category->selectCategories();
                    break;
            }
        }
        else
        {
            exit();
        }
        //Create options for Dropdownlist
        //If the filter is ALL
        if ($type == 0)
        {
            //Add ALL option for the Dropdownlist
            array_unshift($arrParticulars, array(array('ParticularID'=>'0','ParticularName'=>'All')));
            //Append a letter in a value to make all values unique depending on the Array key IDs
            for ($i = 0; count($arrParticulars) > $i; $i++)
            {
                $arrkeys = array_keys($arrParticulars[$i][0]);
                if ($arrkeys[0] == "RewardItemID")
                {
                    $append = "I";
                }
                else if ($arrkeys[0] == "PartnerID")
                {
                    $append = "P";
                }
                else if ($arrkeys[0] == "CategoryID")
                {
                    $append = "C";
                }
                else if ($arrkeys[0] == "ParticularID")
                {
                    $append = "A";
                }
                else
                {
                    echo "An error occured!";
                }
                //Add options on the DropDownList
                for ($x = 0; count($arrParticulars[$i]) > $x; $x++)
                {
                    //ADD [LETTER] in every Name to classify the respective Filter
                    //If ALL is selected in FILTER, unappend [A] in ALL option
                    if ($append != "A")
                    {
                        ?>
                        <option value="<?php echo $append.$arrParticulars[$i][$x][$arrkeys[0]]?>">
                            <?php echo "[".$append."] ".$arrParticulars[$i][$x][$arrkeys[1]]; ?>
                        </option>
                        <?php
                    }
                    else
                    {
                        ?>
                        <option value="<?php echo $append.$arrParticulars[$i][$x][$arrkeys[0]]?>">
                            <?php echo $arrParticulars[$i][$x][$arrkeys[1]]; ?>
                        </option>
                        <?php
                    }
                }
            }
        }
        //If the user choose a specific filter
        else
        {
            //Add ALL option in DropDownList
            array_unshift($arrParticulars, array($index['value']=>'0',$index['text']=>'All'));
            //Add the options on the DropDownlist
            for ($i = 0; count($arrParticulars) > $i; $i++)
            {
                ?><option value="<?php echo $append.$arrParticulars[$i][$index['value']]?>">
                    <?php echo $arrParticulars[$i][$index['text']]; ?>
                  </option>
                <?php
            }
        }
    }
    /**
     * Get the months by the correspoding Quarter
     * @param type $quarter the number return by mysql
     * @date Sep-17-13
     */
    private function identifyQuarter($quarter)
    {
        switch($quarter)
        {
            case 1: $months = "Jan, Feb, Mar";
                break;
            case 2: $months = "Apr, May, Jun";
                break;
            case 3: $months = "Jul, Aug, Sep";
                break;
            case 4: $months = "Oct, Nov, Dec";
                break;
        }
        return $months;
    }
    private function getMonthName($numeric)
    {
        switch($numeric)
        {
            case 1: $month = "Jan";
                break;
            case 2: $month = "Feb";
                break;
            case 3: $month = "Mar";
                break;
            case 4: $month = "Apr";
                break;
            case 5: $month = "May";
                break;
            case 6: $month = "Jun";
                break;
            case 7: $month = "Jul";
                break;
            case 8: $month = "Aug";
                break;
            case 9: $month = "Sep";
                break;
            case 10: $month = "Oct";
                break;
            case 11: $month = "Nov";
                break;
            case 12: $month = "Dec";
                break;
        }
        return $month;
    }
    /**
     * Get respective quarter by month
     * @param int $month Month in numeric
     * @return int Quarter
     * @author mgesguerra
     */
    public function whatQuarter($month)
    {
        if ($month == "01" || $month == "02" || $month == "03")
        {
            $quarter = 1;
        }
        else if ($month == "04" || $month == "05" || $month == "06")
        {
            $quarter = 2;
        }
        else if ($month == "07" || $month == "08" || $month == "09")
        {
            $quarter = 3;
        }
        else if ($month == "10" || $month == "11" || $month == "12")
        {
            $quarter = 4;
        }
        return $quarter;
    }
    /**
     * Check date range depending on chosen report coverage
     * @param int $coverage Report coverage
     * @param date $datefrom From date
     * @param date $dateto To date
     * @return array ErrorCode and ErrorMsg
     * @author Mark Kenneth Esguerra
     * @date November 18, 2013
     */
    function checkDateRange($coverage, $datefrom, $dateto)
    {
        $days       = round(abs(strtotime($datefrom) - strtotime($dateto))/86400);
        $weeks      = (round(abs(strtotime($datefrom) - strtotime($dateto))/86400) + 1) / 7;
        $monthfrom  = substr($datefrom, 5, 2);
        $monthto    = substr($dateto, 5, 2);
        $yearfrom   = substr($datefrom, 0, 4);
        $yearto     = substr($dateto, 0, 4);
        switch($coverage)
        {
            //Daily
            case self::DAILY:
                if ($days >= 7)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Daily Report coverage is only 7 days');
                else
                    return array('ErrorCode' => 1, 'StartDate' => $datefrom, 'EndDate' => $dateto);
                break;
            //Weekly
            case self::WEEKLY:
                if ($weeks > 10)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Weekly Report coverage is only 10 weeks');
                else
                    return array('ErrorCode' => 1, 'StartDate' => $datefrom, 'EndDate' => $dateto);
                break;
            //Monthly
            case self::MONTHLY:
                if ($days >= 365 || ($monthfrom == $monthto && $yearfrom < $yearto))
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Monthly Report coverage is only 12 months');
                else
                    return array('ErrorCode' => 1, 'StartMonth' => $monthfrom, 'EndMonth' => $monthto);
                break;
            //Quarterly
            case self::QUARTERLY:
                if ($monthfrom == "01" || $monthfrom == "02" || $monthfrom ==  "03")
                {
                    if ((1 >= $this->whatQuarter($monthto)) && $yearto > $yearfrom)
                    {
                        if (($monthto == "01" || $monthto == "02" || $monthto == "03") || ($days > 365))
                       {
                           return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Quarterly Report coverage must output only 4 quarters');
                       }
                       else
                       {
                           return array('ErrorCode' => 1, 'StartQuarter' => 1, 'EndQuarter' => $this->whatQuarter($monthto));
                       }

                    }
                    else
                    {
                       return array('ErrorCode' => 1, 'StartQuarter' => 1, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                }
                else if($monthfrom == "04" || $monthfrom == "05" || $monthfrom == "06")
                {
                    if ((2 >= $this->whatQuarter($monthto)) && $yearto > $yearfrom)
                    {
                       if (($monthto == "04" || $monthto == "05" || $monthto == "06") || ($days > 365))
                       {
                           return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Quarterly Report coverage must output only 4 quarters');
                       }
                       else
                       {
                           return array('ErrorCode' => 1, 'StartQuarter' => 2, 'EndQuarter' => $this->whatQuarter($monthto));
                       }
                    }
                    else
                    {
                       return array('ErrorCode' => 1, 'StartQuarter' => 2, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                }
                else if($monthfrom == "07" || $monthfrom == "08" || $monthfrom == "09")
                {
                    if ((3 >= $this->whatQuarter($monthto)) && $yearto > $yearfrom)
                    {
                       if (($monthto == "07" || $monthto == "08" || $monthto == "09") || ($days > 365))
                       {
                           return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Quarterly Report coverage must output only 4 quarters');
                       }
                       else
                       {
                           return array('ErrorCode' => 1, 'StartQuarter' => 3, 'EndQuarter' => $this->whatQuarter($monthto));
                       }
                    }
                    else
                    {

                       return array('ErrorCode' => 1, 'StartQuarter' => 3, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                }
                else if ($monthfrom == "10" || $monthfrom == "11" || $monthfrom == "12")
                {
                    if ((4 >= $this->whatQuarter($monthto)) && $yearto > $yearfrom)
                    {
                        if (($monthto == "10" || $monthto == "11" || $monthto == "12") || ($days > 365))
                        {
                            return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Quarterly Report coverage must output only 4 quarters');
                        }
                        else
                        {
                            return array('ErrorCode' => 1, 'StartQuarter' => 4, 'EndQuarter' => $this->whatQuarter($monthto));
                        }
                    }
                    else
                    {
                        return array('ErrorCode' => 1, 'StartQuarter' => 4, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                }
            //Yearly
            case self::YEARLY:
                $years = $yearto - $yearfrom;
                if ($years > 1)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Yearly Report coverage is only 2 years');
                else
                    return array('ErrorCode' => 1, 'StartYear' => $yearfrom, 'EndYear' => $yearto);
                break;
            default:
                return array('ErrorCode' => 0, 'ErrorMsg' => 'Invalid Report Coverage');
                break;

        }
    }
    /**
     * Get Reward Item Type (AJAX)
     * @author Mark Kenneth Esguerra
     * @date December 16, 2013
     * 
     */
    public function actionGetRewardItemType()
    {
        $itemtype = $_POST['ReportForm_category'];
        switch ($itemtype)
        {
            case RewardTypeModel::REWARDS_E_COUPONS:
                $this->itemtype = 0;
                break;
            case RewardTypeModel::RAFFLE_E_COUPONS:
                $this->itemtype = 1;
                break;
            default:
                $this->itemtype = null;
                break;
        }
        echo $this->itemtype;
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
?>
