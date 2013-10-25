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
    public function actionRewardsredemption()
    {
        $report             = new ReportForm();
        $itemredemption     = new ItemRedemptionLogsModel();
        $couponredemption   = new CouponRedemptionLogsModel();
        if (isset($_POST['ReportForm']))
        {
            $report->attributes = $_POST['ReportForm']; //Pass consolidated values into model attributes
            
            $category   = $report->category;
            $filter     = $report->filter_by;
            $particular = $report->particular;
            $player     = $report->player_segment;
            $date_from  = $report->date_from;
            $date_to    = $report->date_to;
            //Error Handling
            if (strlen($category) == 0 || $category == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Category";
                if ($filter != NULL)
                    $this->filter = $filter;
            }
            else if (strlen($filter) == 0 || $filter == "")
            {
                $this->showdialog = true;
                $this->message = "Please select a Filter By";
            }
            else if (strlen($particular) == 0 || $particular == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Choose Particular";
                $this->filter = $filter;
            }
            else if (strlen($player) == 0 || $player == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Player Segment";
                $this->filter = $filter;
            }
            else if ((strlen($date_from) == 0 || $date_from == "") || (strlen($date_to) == 0 || $date_to == ""))
            {
                $this->showdialog = true;
                $this->message = "Please select From/To Date";
                $this->filter = $filter;
            }
            else if (strtotime($date_from) > strtotime($date_to))
            {
                $this->showdialog = true;
                $this->message = "Invalid Date Range";
                $this->filter = $filter;
            }
            else
            {
                //Check whether Raffle, Rewards E-coupon or BOTH (ALL) has been chosen
                if ($category == RewardTypeModel::RAFFLE_E_COUPONS)
                {
                    //For Raffle E-Coupon
                    $ret_query[] = $couponredemption->inquiry(1, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::REWARDS_E_COUPONS)
                {
                    //For Rewards E-Coupons
                    $ret_query[] = $itemredemption->inquiry(1, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::ALL)
                {
                    //Both Raffle and Rewards E-Coupon
                    $ret_query[] = $itemredemption->inquiry(1, $filter, $particular, $player, $date_from, $date_to, 1);
                    $ret_query[] = $couponredemption->inquiry(1, $filter, $particular, $player, $date_from, $date_to, 1);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
            }
        }
        $this->render('rewardsredemption',array('model'=>$report));
    }
    /**
     * Unique Member Participation Controller
     * Sep-11-13
     */
    public function actionParticipation()
    {
        $report             = new ReportForm();
        $itemredemption     = new ItemRedemptionLogsModel();
        $couponredemption   = new CouponRedemptionLogsModel();
        if (isset($_POST['ReportForm']))
        {
            $report->attributes = $_POST['ReportForm']; //Pass consolidated values into model attributes
            $category   = $report->category;
            $filter     = $report->filter_by;
            $particular = $report->particular;
            $player     = $report->player_segment;
            $date_from  = $report->date_from;
            $date_to    = $report->date_to;
            //Error Handling
            if (strlen($category) == 0 || $category == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Category";
                if ($filter != NULL)
                    $this->filter = $filter;
            }
            else if (strlen($filter) == 0 || $filter == "")
            {
                $this->showdialog = true;
                $this->message = "Please select a Filter By";
            }
            else if (strlen($particular) == 0 || $particular == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Choose Particular";
                $this->filter = $filter;
            }
            else if (strlen($player) == 0 || $player == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Player Segment";
                $this->filter = $filter;
            }
            else if ((strlen($date_from) == 0 || $date_from == "") || (strlen($date_to) == 0 || $date_to == ""))
            {
                $this->showdialog = true;
                $this->message = "Please select From/To Date";
                $this->filter = $filter;
            }
            else if (strtotime($date_from) > strtotime($date_to))
            {
                $this->showdialog = true;
                $this->message = "Invalid Date Range";
                $this->filter = $filter;
            }
            else
            {
                //Check whether Raffle, Rewards E-coupon or BOTH (ALL) has been chosen
                if ($category == RewardTypeModel::RAFFLE_E_COUPONS)
                {
                    //For Raffle E-Coupon
                    $ret_query[] = $couponredemption->inquiry(2, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::REWARDS_E_COUPONS)
                {
                    //For Rewards E-Coupons
                    $ret_query[] = $itemredemption->inquiry(2, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::ALL)
                {
                    //Both Raffle and Rewards E-Coupon
                    $ret_query[] = $itemredemption->inquiry(2, $filter, $particular, $player, $date_from, $date_to, 1);
                    $ret_query[] = $couponredemption->inquiry(2, $filter, $particular, $player, $date_from, $date_to, 1);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
            }
        }
        $this->render('participation',array('model'=>$report));
    }
    /**
     * Reward Points Usage Controller
     */
    public function actionUsage()
    {
        $report             = new ReportForm();
        $itemredemption     = new ItemRedemptionLogsModel();
        $couponredemption   = new CouponRedemptionLogsModel();
        if (isset($_POST['ReportForm']))
        {
            $report->attributes = $_POST['ReportForm']; //Pass consolidated values into model attributes
            $category   = $report->category;
            $filter     = $report->filter_by;
            $particular = $report->particular;
            $player     = $report->player_segment;
            $date_from  = $report->date_from;
            $date_to    = $report->date_to;
            //Error Handling
            if (strlen($category) == 0 || $category == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Category";
                if ($filter != NULL)
                    $this->filter = $filter;
            }
            else if (strlen($filter) == 0 || $filter == "")
            {
                $this->showdialog = true;
                $this->message = "Please select a Filter By";
            }
            else if (strlen($particular) == 0 || $particular == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Choose Particular";
                $this->filter = $filter;
            }
            else if (strlen($player) == 0 || $player == "")
            {
                $this->showdialog = true;
                $this->message = "Please select Player Segment";
                $this->filter = $filter;
            }
            else if ((strlen($date_from) == 0 || $date_from == "") || (strlen($date_to) == 0 || $date_to == ""))
            {
                $this->showdialog = true;
                $this->message = "Please select From/To Date";
                $this->filter = $filter;
            }
            else if (strtotime($date_from) > strtotime($date_to))
            {
                $this->showdialog = true;
                $this->message = "Invalid Date Range";
                $this->filter = $filter;
            }
            else
            {
                //Check whether Raffle, Rewards E-coupon or BOTH (ALL) has been chosen
                if ($category == RewardTypeModel::RAFFLE_E_COUPONS)
                {
                    //For Raffle E-Coupon
                    $ret_query[] = $couponredemption->inquiry(3, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::REWARDS_E_COUPONS)
                {
                    //For Rewards E-Coupons
                    $ret_query[] = $itemredemption->inquiry(3, $filter, $particular, $player, $date_from, $date_to);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
                else if ($category == RewardTypeModel::ALL)
                {
                    //Both Raffle and Rewards E-Coupon
                    $ret_query[] = $itemredemption->inquiry(3, $filter, $particular, $player, $date_from, $date_to, 1);
                    $ret_query[] = $couponredemption->inquiry(3, $filter, $particular, $player, $date_from, $date_to, 1);
                    $success = $this->Generate($ret_query, $player);
                    if ($success)
                    {
                        //Redirect to VIEW 
                        $this->redirect('view');
                    }
                    else
                    {
                        //Show Dialog Box 'No Results Found'
                        $this->showdialog = true;
                        $this->message = "No Results Found";
                        $this->filter = $filter;
                    }
                }
            }
        }
        $this->render('usage',array('model'=>$report));
    }
    public function Generate($query, $player)
    {
        $this->flip = true;
        Yii::import('application.extensions.*');

        require_once('jpgraph-3.5.0b1/src/jpgraph.php');
        require_once('jpgraph-3.5.0b1/src/jpgraph_bar.php');
        
        $itemredemption = new ItemRedemptionLogsModel();
        //Check if what inquiry set by session
        $inq = Yii::app()->session['inquiry'];
        if ($inq != 3)
            $fn_inq = "COUNT(*) as Count,";
        else
            $fn_inq = "SUM(RedeemedPoints) as TotalRedeemedPoints,";
        //Dimensions
        //Every dimensions have different DATE_FORMAT for have 
        //proper labeling in the graph. Also in the file to be
        //generated 
        $dimensions = array('0' => array('Title'=>'Daily','DateFunction'=>'DATE',
                                         'FileName'=>'images/graph-daily.png', 
                                         'DateLabel' => 'DATE_FORMAT(a.DateCreated, "%b/%d") as DateLabel'),
                            '1' => array('Title'=>'Weekly','DateFunction'=>'WEEK',
                                         'FileName'=>'images/graph-weekly.png',
                                         'DateLabel' => "CONCAT(DATE_FORMAT(a.DateCreated,'%Y-%m-%d'), ' - ', DATE_FORMAT(a.DateCreated,'%Y-%m-%d') + INTERVAL 6 DAY) AS DateLabel"),
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
        $arrData = array();
        for ($d = 0; count($dimensions) > $d; $d++)
        {
            //Check if the chosen Category (E-Coupons) is (Raffle or Rewards) or ALL
            //If ALL, there must be 2 queries. Else, only 1.
            if (count($query) == 1)
            {
                //Return false to display No result found if NULL
                if ($query[0] != NULL)
                {
                    //Append other MySQL function for query
                    $queryMonth = $query[0][0]." ".$dimensions[$d]['DateLabel']." ".$query[0][1].
                                            "GROUP BY ".$dimensions[$d]['DateFunction']."(a.DateCreated) 
                                             ORDER BY (a.DateCreated)";
                    $result = $itemredemption->runQuery($queryMonth, $player);
                    //Check if there were results found
                    if (count($result) > 0)
                    {
                        //Get Array Keys
                        $arrkey = array_keys($result[0]);
                        $key1 = $arrkey[0];
                        $key2 = $arrkey[2]; //DateLabel - Index 1 is the dateCreated
                        //MONTH
                        if($d == 2)
                        {
                            for ($i = 0; 12 >= $i; $i++)
                            {
                                //Check if arrays associated with index $i are set.
                                //If not set, assign 0 to data and temporarily assign the index to label
                                //else, assign the gathered data and label
                                if (!isset($result[$i][$key2]) && !isset($result[$i][$key1]))
                                {
                                    $getLabel = $i;
                                    $getData = 0;
                                }
                                else
                                {
                                    $getLabel = $result[$i][$key2];
                                    $getData = $result[$i][$key1];
                                }
                                //Assign gathered data according to label (month)
                                switch($getLabel)
                                {
                                    case 1:
                                        $lbl[0] = "Jan";
                                        if (!isset($datay[0]))
                                            $datay[0] = $getData;
                                        break;
                                    case 2: 
                                        $lbl[1] = "Feb";
                                        if (!isset($datay[1]))
                                            $datay[1] = $getData;
                                        break;
                                    case 3: 
                                        $lbl[2] = "Mar";
                                        if (!isset($datay[2]))
                                            $datay[2] = $getData;
                                        break;
                                    case 4: 
                                        $lbl[3] = "Apr";
                                        if (!isset($datay[3]))
                                            $datay[3] = $getData;
                                        break;
                                    case 5: 
                                        $lbl[4] = "May";
                                        if (!isset($datay[4]))
                                            $datay[4] = $getData;
                                        break;
                                    case 6: 
                                        $lbl[5] = "Jun";
                                        if (!isset($datay[5]))
                                            $datay[5] = $getData;
                                        break;
                                    case 7: 
                                        $lbl[6] = "Jul";
                                        if (!isset($datay[6]))
                                            $datay[6] = $getData;
                                        break;
                                    case 8: 
                                        $lbl[7] = "Aug";
                                        if (!isset($datay[7]))
                                            $datay[7] = $getData;
                                        break;
                                    case 9: 
                                        $lbl[8] = "Sep";
                                        if (!isset($datay[8]))
                                            $datay[8] = $getData;
                                        break;
                                    case 10: 
                                        $lbl[9] = "Oct";
                                        if (!isset($datay[9]))
                                            $datay[9] = $getData;
                                        break;
                                    case 11: 
                                        $lbl[10] = "Nov";
                                        if (!isset($datay[10]))
                                            $datay[10] = $getData;
                                        break;
                                    case 12: 
                                        $lbl[11] = "Dec";
                                        if (!isset($datay[11]))
                                            $datay[11] = $getData;
                                        break;
                                }
                            }
                            //Complete the array
                            for ($x = 0; 12 > $x; $x++)
                            {
                                if (!isset($lbl[$x]) && !isset($lbl[$x]))
                                {
                                    $lbl[$x] = $this->getMonthName($x + 1);
                                    $datay[$x] = 0;
                                }
                            }
                        }
                        //QUARTER
                        else if ($d == 3)
                        {
                            for ($i = 0; 4 >= $i; $i++)
                            {
                                if (!isset($result[$i][$key2]) && !isset($result[$i][$key1]))
                                {
                                    $getLabel = $i;
                                    $getData = 0;
                                }
                                else
                                {
                                    $getLabel = $result[$i][$key2];
                                    $getData = $result[$i][$key1];
                                }
                                switch($getLabel)
                                {
                                    case 1: 
                                        $lbl[0] = $this->identifyQuarter($getLabel);
                                        if (!isset($datay[0]))
                                            $datay[0] = $getData;
                                        break;
                                    case 2: 
                                        $lbl[1] = $this->identifyQuarter($getLabel);
                                        if (!isset($datay[1]))
                                            $datay[1] = $getData;
                                        break;
                                    case 3: 
                                        $lbl[2] = $this->identifyQuarter($getLabel);
                                        if (!isset($datay[2]))
                                            $datay[2] = $getData;
                                        break;
                                    case 4: 
                                        $lbl[3] = $this->identifyQuarter($getLabel);
                                        if (!isset($datay[3]))
                                            $datay[3] = $getData;
                                        break;
                                }
                            }
                            for ($x = 0; 4 > $x; $x++)
                            {
                                if (!isset($lbl[$x]) && !isset($lbl[$x]))
                                {
                                    $lbl[$x] = $this->identifyQuarter($x + 1);
                                    $datay[$x] = 0;
                                }
                            }
                            $this->flip = false;
                        }
                        else
                        {
                            for ($i = 0; count($result) > $i; $i++)
                            {
                                switch($d)
                                {
                                    case 0: $getLabel = $result[$i][$key2];
                                        break;
                                    case 1: $getLabel = $result[$i][$key2];
                                        break;
                                    case 2: $getLabel = substr($result[$i][$key2], 0, 3);
                                            $this->filter = false;
                                            $this->flip = false;
                                        break;
                                    case 3: $getLabel = $result[$i][$key2];
                                            $this->filter = false;
                                            $this->flip = false;
                                        break;
                                    case 4: $getLabel = $result[$i][$key2];
                                            $this->filter = false;
                                            $this->flip = false;
                                        break;
                                }
                                $datay[]    = $result[$i][$key1]; //Put the retrieved data in array dataY
                                $lbl[]      = $getLabel;
                            }
                        }
//                        $arrData[] = array($dimensions[$d]['Title'], array($datay, $lbl));
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
            else if (count($query) == 2)
            {
                //Get the TWO (2) Queries -> Append GROUP BY AND ORDER BY function -> Run Query
                for($i = 0; count($query) > $i; $i++)
                {
                    if ($query[$i] != NULL)
                    {
                        $queryMonth[] = $query[$i][0]." ".$query[$i][1];
                    }
                }
                $finalQuery = "SELECT ".$fn_inq." ".$dimensions[$d]['DateLabel']." FROM (".$queryMonth[0]." UNION ALL ".$queryMonth[1].") AS a"
                               ." GROUP BY ".$dimensions[$d]['DateFunction']."(DateCreated)
                                  ORDER BY ".$dimensions[$d]['DateFunction']."(DateCreated)";
                $result = $itemredemption->runQuery($finalQuery, $player);
                //Count Result. Must have TWO (2) Array Results
                if (count($result) > 0)
                {
                    //Get Array Keys
                    $arrkey = array_keys($result[0]);
                    $key1 = $arrkey[0];
                    $key2 = $arrkey[1];
                    for ($i = 0; count($result) > $i; $i++)
                    {
                        //Get the LABEL according to the date dimension;
                        switch($d)
                        {
                            case 0: $getLabel = $result[$i][$key2];
                                break;
                            case 1: $getLabel = $result[$i][$key2];
                                break;
                            case 2: $getLabel = substr($result[$i][$key2], 0, 3);
                                    $this->filter = false;
                                    $this->flip = false;
                                break;
                            case 3: $getLabel = $this->identifyQuarter($result[$i][$key2]);
                                    $this->filter = false;
                                    $this->flip = false;
                                break;
                            case 4: $getLabel = $result[$i][$key2];
                                    $this->filter = false;
                                    $this->flip = false;
                                break;
                        }
                        $datay[]    = $result[$i][$key1]; //Put the retrieved data in array dataY
                        $lbl[]      = $getLabel;
                    }
                }
                else
                {
                    return false;
                }
            }

            // Create the graph. These two calls are always required
            $graph = new Graph(700, 500);
            $graph->SetScale('textlin');

            // Add a drop shadow
            $graph->SetShadow();

            // Adjust the margin a bit to make more room for titles
            $graph->SetMargin(40,30,20,70);
            //Set Title      
            $graph->title->SetFont(FF_FONT1,FS_BOLD);
            $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD, 12);
            $graph->xaxis->SetTitleMargin(30);
            $graph->xaxis->SetTitle($dimensions[$d]['Title'].' Statistics','middle'); 
            //Set Labels
            $graph->xaxis->SetTickLabels($lbl);
            $graph->xaxis->SetLabelAlign('center','center');
            $graph->xaxis->SetLabelMargin(20);
            $graph->yaxis->SetLabelMargin(0);
            $graph->yaxis->SetLabelFormatCallback('number_format');
            $graph->yaxis->SetLabelFormat('%s');
            $graph->xaxis->scale->ticks->Set(1, 2);

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
            $fileName = $dimensions[$d]['FileName'];
            $graph->img->Stream($fileName);
            //Clear Arrays!
            unset($datay);
            unset($lbl);
            unset($result);
            unset($queryMonth);
            unset(Yii::app()->session['inquiry']);
        }
        Yii::app()->session['arrdata'] = $arrData;
        unset($arrData);
        ///////////////////////////////////////////////////////////////////////////////////
        return true;
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
                    $arrParticulars = $rewarditems->selectRewardItems();

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
                    $arrParticulars[] = $rewarditems->selectRewardItems();
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
            case 1: $months = "Jan\nFeb\nMar";
                break;
            case 2: $months = "Apr\nMay\nJun";
                break;
            case 3: $months = "Jul\nAug\nSep";
                break;
            case 4: $months = "Oct\nNov\nDec";
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
