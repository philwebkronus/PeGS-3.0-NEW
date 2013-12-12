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
        $report             = new ReportForm();
        $itemredemption     = new ItemRedemptionLogsModel();
        $couponredemption   = new CouponRedemptionLogsModel();
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
                $this->message = "Please select Report type";
                $this->filter = $filter;
            }
            else if (strlen($category) == 0 || $category == "")
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
            else if ($coverage == "" || strlen($coverage) == 0)
            {
                $this->showdialog = true;
                $this->message = "Please select Date Coverage";
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
                $result = $this->checkDateRange($coverage, $date_from, $date_to);
                if ($result['ErrorCode'] == 0)
                {
                    $this->showdialog = true;
                    $this->message = $result['ErrorMsg'];
                    $this->filter = $filter;
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
                        case self::QUARTERLY:
                            $start  = $result['StartQuarter'];
                            $end    = $result['EndQuarter'];
                            break;
                        case self::MONTHLY:
                            $start  = $result['StartMonth'];
                            $end    = $result['EndMonth'];
                            break;
                        case self::YEARLY:
                            $start  = null;     
                            $end    = null;
                            break;
                    }
                    //Generate Report
                    $success = $this->Generate($reporttype, $ret_query, $player, $coverage, $start, $end);
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
        $this->render('index',array('model'=>$report));
    }
    
    public function Generate($type, $query, $player, $coverage, $start = NULL, $end = NULL)
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
        //Check if the chosen Category (E-Coupons) is (Raffle or Rewards) or ALL
        //If ALL, there must be 2 queries. Else, only 1.
        if (count($query) == 1)
        {
            //Return false to display No result found if NULL
            if ($query[0] != NULL)
            {
                //Append other MySQL function for query
                $queryMonth = $query[0][0]." ".$dimensions[$d]['DateLabel'].", YEAR(a.DateCreated) as Year ".$query[0][1].
                                        "GROUP BY  YEAR(a.DateCreated), ".$dimensions[$d]['DateFunction']."(a.DateCreated) 
                                         ORDER BY (a.DateCreated) ASC";
                //var_dump($queryMonth);exit;
                $result = $itemredemption->runQuery($queryMonth, $player);
                //Check if there were results found
                if (count($result) > 0)
                {
                    //Get Array Keys
                    $arrkey = array_keys($result[0]);
                    $key1 = $arrkey[0];
                    $key2 = $arrkey[2]; //DateLabel - Index 1 is the dateCreated
                    $year = $arrkey[3];
                    
                    $quarter = $start;
                    $datay = array();
                    //Get maximum bars depending on date coverage
                    switch ($d)
                    {
                        case 0: $days = $start;$max = 7; break;
                        case 2: $month = $start;$max = 12; break; //Monthly
                        case 3: $quarter = $start; $max = 4; break; //Quarterly
                    }
                    if ($d != 4)
                    {
                        if ($start == $end)
                        {
                            if ($d == 2) //Month
                                $vbars = 12;
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
                        $vbars = 2;
                    }
                    //Get all retrieved data
                    //var_dump($vbars);var_dump($start);var_dump($end);exit;
                    for ($i = 0; $vbars > $i; $i++)
                    {
                        $isnull = false;
                        switch($d)
                        {
                            case 0: 
                                
                            //Monthly
                            case 2:
                                if (!isset($result[$i][$key2]))
                                {
                                    if (($vbars + 1) <= 11)
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
                                        $this->filter = false;
                                        $this->flip = false;
                                        $getData = 0;

                                        $isnull = true;
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
                                        $this->filter = false;
                                        $this->flip = false;
                                        $getData = $result[$i][$key1];
                                        
                                        $datay[]    = $getData; //Put the retrieved data in array dataY
                                        $lbl[]      = $getLabel;
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
                                            $this->filter = false;
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
                                    $month++;
                                }
                                break;
                            //Quarterly
                            case 3:
                                //If 0 data
                                if (!isset($result[$i][$key2]))
                                {
                                    //var_dump($quarter);var_dump($vbars);exit;
                                    if (($vbars + 1) <= 3)
                                    {
                                        if ($quarter > 4)
                                        {
                                            $quarter  = 1;
                                            $getLabel = $this->identifyQuarter($quarter)."\n".(($result[0][$year]) + 1);

                                        }
                                        else
                                        {
                                            $getLabel = $this->identifyQuarter($quarter)."\n".($result[0][$year] + 1);
                                        }
                                        $this->flip = false;
                                        $getData = 0;

                                        $isnull = true;
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
                                        $getLabel = $this->identifyQuarter($result[$i][$key2])."\n".$result[$i][$year];
                                        $this->flip = false;
                                        $getData = $result[$i][$key1];
                                        
                                        $datay[]    = $getData; //Put the retrieved data in array dataY
                                        $lbl[]      = $getLabel;
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
                                            
                                            $getLabel = $this->identifyQuarter($_quarter)."\n".$result[$i][$year];
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
                                            $getLabel = $this->identifyQuarter($result[$i][$key2])."\n".$result[$i][$year];
                                            $this->flip = false;
                                            $getData = $result[$i][$key1];

                                            $datay[]    = $getData; //Put the retrieved data in array dataY
                                            $lbl[]      = $getLabel;
                                            
                                            $quarter = $result[$i][$key2];
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
                                    $quarter++;
                                }
                                break;
                            case 4:
                                if (!isset($result[$i][$year]))
                                {
                                    $getData = 0;
                                    $getLabel = 0;
                                }
                                else
                                {
                                    $datay[]    = $result[$i][$key1];
                                    $lbl[]      = $result[$i][$year];
                                    $this->flip = false;
                                }
                                break;
                        }
                        if ($isnull)
                        {
                            $datay[]    = $getData; //Put the retrieved data in array dataY
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
        $graph = new Graph(700, 550);
        $graph->SetScale('textlin');

        // Add a drop shadow
        $graph->SetShadow();

        // Adjust the margin a bit to make more room for titles
        $graph->SetMargin(40,30,20,100);
        //Set Title      
        $graph->title->SetFont(FF_FONT1,FS_BOLD);
        $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD, 12);
        $graph->xaxis->SetTitleMargin(45);
        $graph->xaxis->SetTitle($reporttype." - ".$dimensions[$d]['Title'].' Statistics','middle'); 
        //Set Labels
        $graph->xaxis->SetTickLabels($lbl);
        $graph->xaxis->SetLabelAlign('center','center');
        $graph->xaxis->SetLabelMargin(25);
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
        $fileName = 'images/graph.png';
        $graph->img->Stream($fileName);
        //Clear Arrays!
        unset($datay);
        unset($lbl);
        unset($result);
        unset($queryMonth);
        unset(Yii::app()->session['inquiry']);
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
    /**
     * Get respective quarter by month
     * @param int $month Month in numeric
     * @return int Quarter
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
        $monthfrom  = substr($datefrom, 5, 2);
        $monthto    = substr($dateto, 5, 2);
        $yearfrom   = substr($datefrom, 0, 3);
        $yearto     = substr($dateto, 0, 3);
        switch($coverage)
        {
            //Daily
            case self::DAILY:
                if ($days > 7)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Daily Report coverage is only 7 days');
                else
                    return array('ErrorCode' => 1, 'StartDate' => $datefrom, 'EndDate' => $dateto);
                break;
            //Weekly
            case self::WEEKLY:
                if ($days > 70)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Weekly Report coverage is only 10 weeks');
                else
                    return array('ErrorCode' => 1);
                break;
            //Monthly
            case self::MONTHLY:
                if ($days > 365)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Monthly Report coverage is only 12 months');
                else
                    return array('ErrorCode' => 1, 'StartMonth' => $monthfrom, 'EndMonth' => $monthto);
                break;
            //Quarterly
            case self::QUARTERLY:
                if ($monthfrom == "01" || $monthfrom == "02" || $monthfrom ==  "03")
                {
                    if ($monthfrom == $monthto && $days <= 365)
                    {
                        return array('ErrorCode' => 1, 'StartQuarter' => 1, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                    else
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
                }
                else if($monthfrom == "04" || $monthfrom == "05" || $monthfrom == "06")
                {
                    if ($monthfrom == $monthto && $days <= 365)
                    {
                        return array('ErrorCode' => 1, 'StartQuarter' => 2, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                    else
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
                }
                else if($monthfrom == "07" || $monthfrom == "08" || $monthfrom == "09")
                {
                    if ($monthfrom == $monthto && $days <= 365)
                    {
                        return array('ErrorCode' => 1, 'StartQuarter' => 3, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                    else
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
                }
                else if ($monthfrom == "10" || $monthfrom == "11" || $monthfrom == "12")
                {
                    if ($monthfrom == $monthto && $days <= 365)
                    {
                        return array('ErrorCode' => 1, 'StartQuarter' => 4, 'EndQuarter' => $this->whatQuarter($monthto));
                    }
                    else
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
                }
            //Yearly
            case self::YEARLY:
                $years = $yearto - $yearfrom;
                if ($years > 1)
                    return array('ErrorCode' => 0, 'ErrorMsg' => 'Maximum date range in Yearly Report coverage is only 2 years');
                else
                    return array('ErrorCode' => 1);
                break;
            default:
                return array('ErrorCode' => 0, 'ErrorMsg' => 'Invalid Report Coverage');
                break;
            
        }
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
