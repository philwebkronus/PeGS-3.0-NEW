<?php
ini_set('max_execution_time', 100);
/**
 * Coupon Generation Controller
 * @author Mark Kenneth Esguerra
 * @date July 14, 2014
 */
class CouponGenerationController extends VMSBaseIdentity
{
    public $exportTrue;
    public $filename;
    public $batchID;
    public $count;
    public $amount;
    public $creditable;
    public $distribtype;
    public $promoname;
    public $status;
    public $validfrom;
    public $validto;

    public function actionIndex()
    {
        $model = new CouponForm();
        $siteModel = new SitesModel();

        //get sites
        $arrsites = $siteModel->getSiteCodes();
        if (count($arrsites) > 0)
        {
            foreach ($arrsites as $site)
            {
                $sites[] = array('SiteID' => $site['SiteID'],
                                 'SiteCode' => trim(str_replace(Yii::app()->params['sitePrefix'], "", $site['SiteCode'])));
            }
            $sitelist = CHtml::listData($sites, "SiteID", "SiteCode");
        }
        else
        {
            $sitelist = array();
        }
        $this->render('index', array('model' => $model, 'sitelist' => $sitelist));
    }
    /**
     * Get Coupon Batches
     */
    public function actionGetCouponBatches()
    {
        if (isset($_POST['stop']))
        {
            die();
        }
        $couponBatchModel   = new CouponBatchModel();
        $couponModel        = new CouponModel();
        $accountsModel      = new AccountsModel();

        $response = array();

        $page       = $_POST['page']; // get the requested page
        $limit      = $_POST['rows']; // get how many rows we want to have into the grid

        $postvars['batchID']            = $this->sanitize($_POST['batchID']);
        $postvars['amount']             = $this->sanitize($_POST['amount']);
        $postvars['distributiontag']    = $this->sanitize($_POST['distributiontag']);
        $postvars['creditable']         = $this->sanitize($_POST['creditable']);
        $postvars['generatedfrom']      = $this->sanitize($_POST['generatedfrom']);
        $postvars['generatedto']        = $this->sanitize($_POST['generatedto']);
        $postvars['generatedby']        = $this->sanitize($_POST['generatedby']);
        $postvars['validfrom']          = $this->sanitize($_POST['validfrom']);
        $postvars['validto']            = $this->sanitize($_POST['validto']);
        $postvars['status']             = $this->sanitize($_POST['status']);
        $postvars['promoname']          = $this->sanitize($_POST['promoname']);

        //if batch ID is set
        if ($postvars['batchID'] != "")
        {
            $allcouponbatch = $couponBatchModel->getCouponBatch($postvars['batchID']);
            $withcoupons = false;
            $query = 1;

        }
        //if search using other fields
        else if ($postvars['amount'] != "" || $postvars['distributiontag'] != ""
            || $postvars['creditable'] != "" || $postvars['generatedby'] != "" || $postvars['generatedfrom'] != ""
            || $postvars['generatedto'] != "" || $postvars['validfrom'] != "" || $postvars['validto'] != ""
            || $postvars['status'] != "" || $postvars['promoname'] != "")
        {
            if ($postvars['generatedby'] != "")
            {
                $postvars['generatedby'] = $accountsModel->getAIDByUserName($postvars['generatedby']);
            }
            //check what field used to search coupons batch
            $i = 0;
            foreach ($postvars as $vars)
            {
                if ($vars != "")
                {
                    $search[] = array('FieldID' => $i, 'Value' => $vars);
                }

                $i++;
            }
            //construct WHERE for query
            $wherefx = $this->constructWhereFx($search);
            $allcouponbatch = $couponModel->searchCouponBatch($wherefx, $search);
            $withcoupons = true;
            $query = 2;
        }
        else
        {
            $allcouponbatch = $couponBatchModel->getCouponBatch();
            $withcoupons = false;
            $query = 3;
        }
        //Get total number of coupon batch
        $batchcount = count($allcouponbatch);

        if ($batchcount > 0)
        {
            if ($batchcount > 0)
            {
                $total_pages = ceil($batchcount / $limit);
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
            if ($batchcount == 0)
                $start = 0;
            if ($batchcount > 0)
            {
                //execute the used query for getting coupon batch to apply pagination
                switch ($query)
                {
                    case 1:
                        $allcouponbatch = $couponBatchModel->getCouponBatch($postvars['batchID'], $start, $limit);
                        break;
                    case 2:
                        $allcouponbatch = $couponModel->searchCouponBatch($wherefx, $search, $start, $limit);
                        break;
                    case 3:
                        $allcouponbatch = $couponBatchModel->getCouponBatch(null, null, null, null, null, null, null, null,
                                                                            null, null, null, $start, $limit);
                        break;
                    default:
                        $allcouponbatch = $couponBatchModel->getCouponBatch(null, null, null, null, null, null, null, null,
                                                                            null, null, null, $start, $limit);
                        break;

                }
                $i = 0;
                foreach ($allcouponbatch as $couponbatch)
                {
                    if ($withcoupons ==  false)
                    {
                        //get other details under coupons table
                        $details = $couponModel->getVoucherInfo($couponbatch['CouponBatchID']);

                        $iscredibtale = $details['IsCreditable'] == 1 ? "Yes" : "No";
                        $validfromdate = $details['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($details['ValidFromDate']));
                        $validto = $details['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($details['ValidToDate']));
                    }
                    else
                    {
                        $iscredibtale = $couponbatch['IsCreditable'] == 1 ? "Yes" : "No";
                        $validfromdate = $couponbatch['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($couponbatch['ValidFromDate']));
                        $validto = $couponbatch['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($couponbatch['ValidToDate']));
                    }
                    //get usernames
                    $createdby = $accountsModel->getUsername($couponbatch['CreatedByAID']);
                    $updatedby = $accountsModel->getUsername($couponbatch['UpdatedByAID']);

                    $response['rows'][$i]['id'] = $couponbatch['CouponBatchID'];
                    $response['rows'][$i]['cell'] = array(
                        $couponbatch['CouponBatchID'],
                        number_format($couponbatch['CouponCount']),
                        number_format($couponbatch['Amount'], 2, ".", ","),
                        $this->getDistributionTag($couponbatch['DistributionTagID']),
                        $iscredibtale,
                        $couponbatch['DateCreated'] == "" ? "" : date("M d, Y h:i A", strtotime($couponbatch['DateCreated'])),
                        $createdby,
                        $validfromdate,
                        $validto,
                        $this->stringStatus($couponbatch['Status']),
                        $couponbatch['PromoName'],
                        "<a href='#' style='color: #008AFF' class='mgmt' title='Edit' id='link-edit' BatchID='".$couponbatch['CouponBatchID']."'>Edit</a><a href='#' style='color: #008AFF' class='mgmt' title='List' id='link-list' BatchID='".$couponbatch['CouponBatchID']."'>List</a><a href='#' style='color: #008AFF' class='mgmt' title='Export' id='link-export' BatchID='".$couponbatch['CouponBatchID']."'>Export</a>",
                        $couponbatch['DateUpdated'] == "" ? "" : date("M d, Y h:i A", strtotime($couponbatch['DateUpdated'])),
                        $updatedby,

                    );

                    $i++;
                }
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $batchcount;
            }
            else
            {
                $i = 0;
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $batchcount;
            }
        }
        echo json_encode($response);
    }
    /**
     * Get Coupon Batch details via AJAX.
     * @author Mark Kenneth Esguerra
     * @date July 15, 2014
     */
    public function actionGetBatchDetails()
    {
        $couponBatchModel   = new CouponBatchModel();
        $couponModel        = new CouponModel();

        $postvars['batchID'] = $_POST['batchID'];
        $response = array();

        $arrbatchdetails = $couponBatchModel->getCouponBatch($postvars['batchID']);
        if ($arrbatchdetails != false)
        {
            foreach ($arrbatchdetails as $batchdetails)
            {
                $coupondetails = $couponModel->getVoucherInfo($postvars['batchID']);
                $response = array('ErrorCode' => 0,
                                  'BatchID' => $batchdetails['CouponBatchID'],
                                  'Count' => number_format($batchdetails['CouponCount']),
                                  'Amount' => number_format($batchdetails['Amount'], 2, ".", ","),
                                  'DistributionType' => $this->getDistributionTag($batchdetails['DistributionTagID']),
                                  'Status' => $batchdetails['Status'],
                                  'StringedStatus' => $this->stringStatus($batchdetails['Status']),
                                  'PromoName' => $batchdetails['PromoName'],
                                  'Creditable' => $coupondetails['IsCreditable'] == 2 ? "No" : "Yes",
                                  'ValidFrom' => $coupondetails['ValidFromDate'],
                                  'ValidTo' => $coupondetails['ValidToDate'],
                                  'ValidFromFormatted' => $coupondetails['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupondetails['ValidFromDate'])),
                                  'ValidToFormatted' => $coupondetails['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupondetails['ValidToDate'])));
            }
        }
        else
        {
            $response = array('ErrorCode' => 1,
                              'Message' => 'Invalid Coupon BatchID');
        }
        echo json_encode($response);
    }
    /**
     * Convert integer status into string
     * @param type $postvars['status']
     * @author Mark Kenneth Esguerra
     * @date July 14, 2014
     */
    private function stringStatus($status, $int = null)
    {
        if (is_null($int))
        {
            switch ($status)
            {
                case 0:
                    $_stat = "Inactive";
                    break;
                case 1:
                    $_stat = "Activated";
                    break;
                case 2:
                    $_stat = "Deactivated";
                    break;
                case 3:
                    $_stat = "Used";
                    break;
                case 4:
                    $_stat = "Cancelled";
                    break;
                case 5:
                    $_stat = "Reimbursed";
                    break;
                default:
                    $_stat = "Undefined";
                    break;
            }
        }
        else
        {
            switch ($status)
            {
                case "Inactive":
                    $_stat = 0;
                    break;
                case "Activated":
                    $_stat = 1;
                    break;
                case "Deactivated":
                    $_stat = 2;
                    break;
                case "Used":
                    $_stat = 3;
                    break;
                case "Cancelled":
                    $_stat = 4;
                case "Reimbursed":
                    $_stat = 5;
                    break;
                default:
                    $_stat = 0;
                    break;
            }
        }
        return $_stat;
    }
    /**
     * Convert integer DTag into string DTag
     * @param type $distribtag
     * @param int $int if null get the string, else get the ref_int
     * @return type
     * @author Mark Kenneth Esguerra
     * @date July 14, 2014
     */
    private function getDistributionTag($distribtag, $int = null)
    {
        if (is_null($int))
        {
            switch ($distribtag)
            {
                case 1:
                    $tag = "Print";
                    break;
                case 2:
                    $tag = "SMS";
                    break;
                case 3:
                    $tag = "Email";
                    break;
                default:
                    $tag = "Print";
                    break;
            }
        }
        else
        {
            switch ($distribtag)
            {
                case "Print":
                    $tag = 1;
                    break;
                case "SMS":
                    $tag = 2;
                    break;
                case "Email":
                    $tag = 3;
                    break;
                default:
                    $tag = 1;
                    break;
            }
        }


        return $tag;
    }
    /**
     * This function constructs <b>WHERE function</b> in SQL query depending on number <br />
     * of fields used to search couponbatch/coupons.
     * @param array $search array of fields
     * @return string WHERE statement
     * @author Mark Kenneth Esguerra
     * @date July 18, 2014
     */
    private function constructWhereFx($search, $searchcoupon = null)
    {
        $where_statement = "WHERE";
        $fieldsearched = count($search); //number of fields used to search

        //searching coupon batch
        if (is_null($searchcoupon))
        {
            for ($i = 0; $fieldsearched > $i; $i++)
            {
                //get condition
                switch ($search[$i]['FieldID'])
                {
                    case 1:
                        $condition = " cb.Amount = :amount";
                        break;
                    case 2:
                        $condition = " cb.DistributionTagID = :distribtag";
                        break;
                    case 3:
                        $condition = " c.Iscreditable = :creditable";
                        break;
                    case 4:
                        $condition = " cb.DateCreated >= :generatedfrom";
                        break;
                    case 5:
                        $condition = " cb.DateCreated < :generatedto";
                        break;
                    case 6:
                        $condition = " cb.CreatedByAID = :aid";
                        break;
                    case 7:
                        $condition = " (c.ValidFromDate >= :validfrom AND c.ValidFromDate < :validto)";
                        break;
                    case 8:
                        $condition = " (c.ValidToDate >= :validfrom AND c.ValidToDate < :validto)";
                        break;
                    case 9:
                        $condition = " cb.Status = :status";
                        break;
                    case 10:
                        $condition = " cb.PromoName = :promoname";
                        break;
                    default:
                        $condition = "";
                        break;
                }
                $where_statement = $where_statement.$condition;
                if (($fieldsearched - ($i + 1)) > 0)
                {
                    $where_statement = $where_statement." OR";
                }
            }
        }
        //searching coupons
        else if ($searchcoupon == 1)
        {
            for ($i = 0; $fieldsearched > $i; $i++)
            {
                //get condition
                switch ($search[$i]['FieldID'])
                {
                    case 0:
                        $condition = " c.CouponBatchID = :batchID";
                        break;
                    case 1:
                        $condition = " c.CouponCode = :couponcode";
                        break;
                    case 2:
                        $condition = " c.Status = :status";
                        break;
                    case 3:
                        $condition = " c.DateUpdated >= :transdatefrom AND c.DateUpdated <= :transdateto AND
                                       c.Status = 3";
                        break;
                    case 4:
                        $condition = " c.SiteID = :site";
                        break;
                    case 5:
                        $condition = " c.TerminalID = :terminal";
                        break;
                    case 6:
                        $condition = " c.Status = :source";
                        break;
                    case 7:
                        $condition = " cb.PromoName = :promoname";
                        break;
                    default:
                        $condition = "";
                        break;
                }
                $where_statement = $where_statement.$condition;
                if (($fieldsearched - ($i + 1)) > 0)
                {
                    $where_statement = $where_statement." AND";
                }
            }
        }

        return $where_statement;
    }
    /**
     * Displays all coupons. Also used for searching coupons
     */
    public function actionGetCoupons()
    {
        if (isset($_POST['stop']))
        {
            die();
        }
        $couponsModel   = new CouponModel();
        $sitesModel     = new SitesModel();
        $terminalModel  = new TerminalsModel();
        $accountsModel  = new AccountsModel();

        $postvars['batchID']        = $this->sanitize($_POST['batchID']);
        $postvars['couponcode']     = $this->sanitize($_POST['couponcode']);
        $postvars['status']         = $this->sanitize($_POST['status']);
        $postvars['transdatefrom']  = $this->sanitize($_POST['transdatefrom']);
        $postvars['siteID']         = $this->sanitize($_POST['site']);
        $postvars['terminalID']     = $this->sanitize($_POST['terminal']);
        $postvars['source']         = $this->sanitize($_POST['source']);
        $postvars['promoname']      = $this->sanitize($_POST['promoname']);

        $page           = $_POST['page']; // get the requested page
        $limit          = $_POST['rows']; // get how many rows we want to have into the grid
        //retrieve data
        if ($postvars['status'] != "" || $postvars['transdatefrom'] != ""
            || $postvars['siteID'] != ""  || $postvars['terminalID'] != ""
            || $postvars['source'] != "" || $postvars['promoname'] != ""
            || $postvars['couponcode'])
        {
            //check what field used to search coupons
            $i = 0;
            foreach ($postvars as $vars)
            {
                if ($vars != "")
                {
                    $search[] = array('FieldID' => $i, 'Value' => $vars);
                }

                $i++;
            }
            //get WHERE statement depending on fields used to search coupons
            $wherefx = $this->constructWhereFx($search, 1);
            $allcoupons = $couponsModel->searchCoupons($wherefx, $search);
            $query = 1;
        }
        else
        {
            $allcoupons = $couponsModel->getCouponsByBatchID($postvars['batchID']);
            $query = 2;
        }
        //Get total number of coupon batch
        $couponcount = count($allcoupons);

        if ($couponcount > 0)
        {
            if ($couponcount > 0)
            {
                $total_pages = ceil($couponcount / $limit);
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
            if ($couponcount == 0)
                $start = 0;

            if ($couponcount > 0)
            {
                //execute used to query to apply pagination
                if ($query == 1)
                {
                    $allcoupons = $couponsModel->searchCoupons($wherefx, $search, $start, $limit);
                }
                else
                {
                    $allcoupons = $couponsModel->getCouponsByBatchID($postvars['batchID'], $start, $limit);
                }
                $i = 0;
                foreach ($allcoupons as $coupon)
                {
                    //get usernames
                    $createdby = $accountsModel->getUsername($coupon['CreatedByAID']);
                    $reimbursedby = $accountsModel->getUsername($coupon['ReimbursedByAID']);
                    $site = $sitesModel->getSiteName($coupon['SiteID']);
                    $terminal = $terminalModel->getTerminalNamesUsingTerminalID($coupon['TerminalID']);
                    //if coupon is used get transdate(DateUpdated)
                    $transdate = "";
                    $source = "";
                    if ($coupon['Status'] == 3)
                    {
                        $transdate = date("M d, Y h:i A", strtotime($coupon['DateUpdated']));
                        $source = "Cashier";
                    }
                    $response['rows'][$i]['id'] = $coupon['CouponID'];
                    $response['rows'][$i]['cell'] = array(
                        $coupon['CouponBatchID'],
                        $coupon['CouponID'],
                        $coupon['CouponCode'],
                        number_format($coupon['Amount'], 2, ".", ","),
                        $this->getDistributionTag($coupon['DistributionTagID']),
                        $coupon['IsCreditable'] == 1 ? "Yes" : "No",
                        $coupon['DateCreated'] == "" ? "" : date("M d, Y h:i A", strtotime($coupon['DateCreated'])),
                        $createdby,
                        $coupon['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupon['ValidFromDate'])),
                        $coupon['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupon['ValidToDate'])),
                        $this->stringStatus($coupon['Status']),
                        isset($site[0]['SiteName']) == "" ? "" : $site[0]['SiteName'],
                        isset($terminal[0]['TerminalName']) == "" ? "" : $terminal[0]['TerminalName'],
                        $source,
                        $transdate,
                        $coupon['PromoName'] != NULL ? $coupon['PromoName'] : "",
                        $coupon['DateReimbursed'] == "" ? "" : date("M d, Y h:i A", strtotime($coupon['DateReimbursed'])),
                        $reimbursedby,
                    );

                    $i++;
                }
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $couponcount;
            }
            else
            {
                $i = 0;
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $couponcount;
            }
        }
        else
        {
            $i = 0;
            $response["page"]     = 0;
            $response["total"]    = 0;
            $response["records"]  = 0;
        }
        echo json_encode($response);
    }
    /**
     * Get Terminals of the Sites (AJAX)
     */
    public function actionGetSiteTerminals()
    {
        $terminalsModel = new TerminalsModel();

        $siteID = $_POST['siteID'];

        $terminals = $terminalsModel->getTerminalNamesUsingSiteID($siteID);

        $options = "";
        foreach ($terminals as $terminal)
        {
            $options = $options."<option value=".$terminal['TerminalID'].">".str_replace(Yii::app()->params['sitePrefix'], "", $terminal['TerminalCode'])."</option>";
        }

        echo $options;
    }
    /**
     * Check date range
     * @param type $from
     * @param type $to
     * @return type
     */
    public function checkDateRange($from, $to)
    {
        $response = array();
        if ($from != "" && $to != "")
        {
            //check if date from is less than date to
            if ($from < $to)
            {
                $response = array('ErrorCode' => 0);
            }
            else
            {
                $response = array('ErrorCode' => 1, 'Message' => 'Invalid Date range.');
            }
        }
        else
        {
            $response = array('ErrorCode' => 0);
        }
        return json_encode($response);exit;

    }
    /**
     * This function Generates Coupons
     */
    public function actionGenerateCoupons()
    {
        $couponbatchModel = new CouponBatchModel();

        $count          = $this->sanitize($_POST['count']);
        $amount         = $this->sanitize($_POST['amount']);
        $promoname      = $this->sanitize($_POST['promoname']);
        $distribtag     = $this->sanitize($_POST['distribtype']);
        $creditable     = $this->sanitize($_POST['creditable']);
        $status         = $this->sanitize($_POST['status']);
        $validfrom      = $this->sanitize($_POST['validfrom']);
        $validto        = $this->sanitize($_POST['validto']);
        $confirmed      = $this->sanitize($_POST['confirmed']);

        //check if all fields are not blank
        if ($count != "" && $amount != "" && $promoname != ""
            && $distribtag != "" && ($creditable != "" || $creditable != 0)
            && $status != "" && $validfrom != ""
            && $validto != "")
        {
            //check if validity date range
            $isvalid = $this->checkDateRange($validfrom, $validto);
            $r = (array)json_decode($isvalid);
            if ($r['ErrorCode'] == 0)
            {
                $amount = number_format(str_replace(",", "", $amount), 2, ".", "");
                $count  = number_format(str_replace(",", "", $count), 2, ".", "");

                $maxCouponCount     = (int)Yii::app()->params['maxCouponCount'];
                $maxCouponAmount    = (int)Yii::app()->params['maxCouponAmount'];
                $minCouponAmount    = (int)Yii::app()->params['minCouponAmount'];
                //check if count is less than or equal to 5000
                if ($count <= $maxCouponCount && $count >= 1)
                {
                    if ($amount <= $maxCouponAmount && $amount >= $minCouponAmount)
                    {
                        //check if already confirmed, if not prompt a confirmation message
                        if ($confirmed == 1)
                        {
                            //start generation
                            $user = Yii::app()->session['AID'];
                            $distribtag = $this->getDistributionTag($distribtag, 1);
                            $creditable = $creditable == "Yes" ? 1 : 2;
                            $stat = $this->stringStatus($status, 1);

                            $result = $couponbatchModel->insertCoupons($count, $amount, $distribtag, $creditable, $promoname, $user, $stat, $validfrom, $validto);
                            //check result
                            switch ($result['TransCode'])
                            {
                                case 0: //display error message
                                    $response = array('ErrorCode' => 1,
                                                      'Message' => $result['TransMsg']);
                                    break;
                                case 1: //display success message
                                    $response = array('ErrorCode' => 0,
                                                      'Message' => $result['TransMsg'],
                                                      'Count' => number_format($count),
                                                      'Amount' => number_format($amount, 2, ".", ","),
                                                      'PromoName' => $promoname,
                                                      'DistributionType' => $this->getDistributionTag($distribtag),
                                                      'Creditable' => $creditable == 1 ? "Yes" : "No",
                                                      'Status' => $this->stringStatus($stat),
                                                      'ValidFrom' => date("M d, Y h:i A", strtotime($validfrom)),
                                                      'ValidTo' => date("M d, Y h:i A", strtotime($validto)),
                                                      'ValidFromDate' => $validfrom, //original format
                                                      'ValidToDate' => $validto); //original format
                                    break;
                                case 2: //display retry message if there's a generated coupon duplicates another
                                    $response = array('ErrorCode' => 2,
                                                      'Message' => $result['TransMsg'],
                                                      'CouponBatchID' => $result['CouponBatchID'],
                                                      'RemainingCount' => $result['RemainingCoupon'],
                                                      'Amount' => $result['Amount'],
                                                      'Creditable' => $result['IsCreditable'],
                                                      'Status' => $this->stringStatus($result['Status']),
                                                      'ValidFromDate' => $result['ValidFrom'],
                                                      'ValidToDate' => $result['ValidTo']);
                            }
                        }
                        else
                        {
                           $response = array('ErrorCode' => 0,
                                            'Confirmed' => 0,
                                            'Count' => number_format($count),
                                            'Amount' => number_format($amount, 2, ".", ","),
                                            'PromoName' => $promoname,
                                            'DistributionType' => $this->getDistributionTag($distribtag),
                                            'Creditable' => $creditable == 1 ? "Yes" : "No",
                                            'Status' => $this->stringStatus($status),
                                            'ValidFrom' => date("M d, Y h:i A", strtotime($validfrom)),
                                            'ValidTo' => date("M d, Y h:i A", strtotime($validto)),
                                            'ValidFromDate' => $validfrom, //original format
                                            'ValidToDate' => $validto); //original format);
                        }
                    }
                    else
                    {
                        $response = array('ErrorCode' => 1,
                                  'Message' => 'Invalid coupon amount. Amount should be between 500 and 50,000 only.');
                    }

                }
                else
                {
                    $response = array('ErrorCode' => 1,
                                  'Message' => ' Invalid coupon count. Count should be between 1 and 5,000 only.');
                }

            }
            else
            {
                $response = array('ErrorCode' => 1,
                                  'Message' => $r['Message']);
            }
        }
        else
        {
            $response = array('ErrorCode' => 1,
                              'Message' => 'Please fill up all fields.');
        }
        echo json_encode($response);
    }
    public function actionRegenerateCoupons()
    {
        $couponsModel       = new CouponModel();
        $couponBatchModel   = new CouponBatchModel();

        $couponbatchID      = $this->sanitize($_POST['batchID']);
        $remainingcount     = $this->sanitize($_POST['remainingcount']);
        $amount             = $this->sanitize($_POST['amount']);
        $creditable         = $this->sanitize($_POST['creditable']);
        $status             = $this->sanitize($_POST['status']);
        $validfrom          = $this->sanitize($_POST['validfrom']);
        $validto            = $this->sanitize($_POST['validto']);
        $user               =  Yii::app()->session['AID'];

        $result = $couponsModel->regenerateCoupons($amount, $remainingcount, $couponbatchID, $creditable, $user, $status, $validfrom, $validto);

        //check result
        switch ($result['TransCode'])
        {
            case 0: //display error message
                $response = array('ErrorCode' => 1,
                                  'Message' => $result['TransMsg']);
                break;
            case 1: //display success message
                //retrieve details per coupon batch
                $couponbatchdtls = $couponBatchModel->getCouponBatch($couponbatchID);
                foreach ($couponbatchdtls as $batchdtls)
                {
                    $coupondtls = $couponsModel->getVoucherInfo($batchdtls['CouponBatchID']);
                }

                $response = array('ErrorCode' => 0,
                                  'Message' => $result['TransMsg'],
                                  'CouponBatchID' => $batchdtls['CouponBatchID'],
                                  'Count' => number_format($batchdtls['CouponCount']),
                                  'Amount' => number_format($batchdtls['Amount'], 2, ".", ","),
                                  'PromoName' => $batchdtls['PromoName'],
                                  'DistributionType' => $this->getDistributionTag($batchdtls['DistributionTagID']),
                                  'Creditable' => $coupondtls['IsCreditable'] == 1 ? "Yes" : "No",
                                  'Status' => $this->stringStatus($batchdtls['Status']),
                                  'ValidFromDate' => date("M d, Y h:i A", strtotime($coupondtls['ValidFromDate'])),
                                  'ValidToDate' => date("M d, Y h:i A", strtotime($coupondtls['ValidToDate'])));
                break;
            case 2: //display retry message if there's a generated coupon duplicates another
                $response = array('ErrorCode' => 2,
                                  'Message' => $result['TransMsg'],
                                  'CouponBatchID' => $result['CouponBatchID'],
                                  'RemainingCount' => $result['RemainingCoupon'],
                                  'Amount' => $result['Amount'],
                                  'Creditable' => $result['IsCreditable'],
                                  'Status' => $result['Status'],
                                  'ValidFromDate' => $validfrom,
                                  'ValidToDate' => $validto);
        }
        echo json_encode($response);
    }
    /**
     * Update Coupon Batch
     */
    public function actionUpdateCouponBatch()
    {
        $couponBatchModel   = new CouponBatchModel();
        $couponModel        = new CouponModel();

        $batchID    = $this->sanitize($_POST['batchID']);
        $status     = $this->sanitize($_POST['status']);
        $validfrom  = $this->sanitize($_POST['validfrom']);
        $validto    = $this->sanitize($_POST['validto']);
        $user       = Yii::app()->session['AID'];

        $response = array();
        if ($status != "" || $validfrom != "" || $validto != "")
        {
            //check if has valid date range
            $r = $this->checkDateRange($validfrom, $validto);
            if ($r['ErrorCode'] == 0)
            {
                //get current status and validity of the coupon
                $currdetails = $couponModel->getValidityOfCoupon($batchID);
                if ($currdetails['Status'] != $status || $currdetails['ValidFromDate'] != $validfrom
                                                      || $currdetails['ValidToDate'] != $validto)
                {
                    $result = $couponBatchModel->changeStatus($batchID, $status, $validfrom, $validto, $user);
                    if ($result['TransCode'] == 1 || $result['TransCode'] == 2)
                    {
                        //get batch details if transaction was successful
                        $batchdetails = $couponBatchModel->getCouponBatch($batchID);
                        foreach ($batchdetails as $details)
                        {
                            $otherdetails = $couponModel->getVoucherInfo($details['CouponBatchID']);

                            $response = array('ErrorCode' => 0,
                                              'Message' => $result['TransMsg'],
                                              'CouponBatchID' => $details['CouponBatchID'],
                                              'Count' => number_format($details['CouponCount']),
                                              'Amount' => number_format($details['Amount'], 2, ".", ","),
                                              'PromoName' => $details['PromoName'] != null ? $details['PromoName'] : "",
                                              'DistributionTagID' => $this->getDistributionTag($details['DistributionTagID']),
                                              'Creditable' => $otherdetails['IsCreditable'] == 1 ? "Yes" : "No",
                                              'Status' => $this->stringStatus($details['Status']),
                                              'ValidFromDate' => date("M d, Y h:i A", strtotime($otherdetails['ValidFromDate'])),
                                              'ValidToDate' => date("M d, Y h:i A", strtotime($otherdetails['ValidToDate'])));
                        }
                    }
                    else
                    {
                        $response = array('ErrorCode' => 1,
                                          'Message' => $result['TransMsg']);
                    }
                }
                else
                {
                    $response = array('ErrorCode' => 1,
                                  'Message' => "Coupon details unchanged.");
                }
            }
            else
            {
                $response = array('ErrorCode' => 1,
                                  'Message' => $r['Message']);
            }
        }
        else
        {
            $response = array('ErrorCode' => 1,
                              'Message' => 'Please fill up all required fields.');
        }
        echo json_encode($response);
    }
    public function actionExporttocsv()
    {
        $accountsModel      = new AccountsModel();
        $couponsModel       = new CouponModel();
        $couponBatchModel   = new CouponBatchModel();
        $sitesModel         = new SitesModel();
        $terminalModel      = new TerminalsModel();
        $model              = new CouponForm();

        if (isset($_POST['hdnbatchID']))
        {
            $batchID = $this->sanitize($_POST['hdnbatchID']);

            $filename = 'CouponBatchID'.'_'.Yii::app()->session['AID'].'.csv';
    //        header('Content-Description: File Transfer');
    //        header('Content-Type: text/csv');
    //        header('Content-Disposition: attachment; filename='.$filename);

            $couponbatch = $couponBatchModel->getCouponBatch($batchID); //get batch details
            $arrcoupons = $couponsModel->getCouponsByBatchID($batchID); //get coupons
            $validity = $couponsModel->getValidityOfCoupon($batchID);

            $fp = fopen($filename, 'w');
            $headers = array('Batch ID', 'Coupon ID', 'Coupon Code', 'Amount', 'Distribution Type', 'Creditable?', 'Date Generated',
                             'Generated By', 'Valid From Date', 'Valid To Date', 'Status', 'Site', 'Terminal',
                             'Source', 'Transaction Date', 'Promo Name');
            fputcsv($fp, $headers);

            foreach ($arrcoupons as $coupons) {
                $createdby = $accountsModel->getUsername($coupons['CreatedByAID']);
                $site = $sitesModel->getSiteName($coupons['SiteID']);
                $terminal = $terminalModel->getTerminalNamesUsingTerminalID($coupons['TerminalID']);

                $transdate = "";
                if ($coupons['Status'] == 3)
                {
                    $transdate = $coupons['DateUpdated'];
                }
                $rows = array('CouponBatchID' => $coupons['CouponBatchID'],
                              'CouponID' => $coupons['CouponID'],
                              'CouponCode' => $coupons['CouponCode'],
                              'Amount' => number_format($coupons['Amount'], 2, ".", ","),
                              'DistributionTagID' => $this->getDistributionTag($coupons['DistributionTagID']),
                              'IsCreditable' => $coupons['IsCreditable'] == 1 ? "Yes" : "No",
                              'DateCreated' => $coupons['DateCreated'] == "" ? "" : date("M d, Y h:i A", strtotime($coupons['DateCreated'])),
                              'CreatedByAID' => $createdby,
                              'ValidFromDate' => $coupons['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupons['ValidFromDate'])),
                              'ValidToDate' => $coupons['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($coupons['ValidToDate'])),
                              'Status' => $this->stringStatus($coupons['Status']),
                              isset($site[0]['SiteName']) == "" ? "" : $site[0]['SiteName'],
                              isset($terminal[0]['TerminalName']) == "" ? "" : $terminal[0]['TerminalName'],
                              'Source' => $coupons['Status'] == 3 ? "Cashier" : "",
                              'Transaction Date' =>  $transdate == "" ? "" : date("M d, Y h:i A", strtotime($transdate)),
                              'PromoName' => $coupons['PromoName']
                );
                fputcsv($fp, $rows);
                $iscreditable = $coupons['IsCreditable'] == 1 ? "Yes" : "No"; //get creditable
            }
            fclose($fp);

            $this->exportTrue = true;
            $this->filename = $filename;
            $this->batchID = $batchID;
            $this->count = number_format($couponbatch[0]['CouponCount']);
            $this->amount = number_format($couponbatch[0]['Amount'], 2, ".", ",");
            $this->distribtype = $this->getDistributionTag($couponbatch[0]['DistributionTagID']);
            $this->status = $couponbatch[0]['Status'];
            $this->promoname = $couponbatch[0]['PromoName'];
            $this->creditable = $iscreditable;
            $this->validfrom = $validity['ValidFromDate'] == "" ? "" : date("M d, Y h:i A", strtotime($validity['ValidFromDate']));
            $this->validto = $validity['ValidToDate'] == "" ? "" : date("M d, Y h:i A", strtotime($validity['ValidToDate']));
        }
        $this->render('index', array('model' => $model, 'sitelist' => array()));

    }
    /**
     * Get status and include checking of valid date range once validfrom
     * and validto are set.
     */
    public function actionGetStatus()
    {
        $status = $_POST['stat'];
        $validfrom = $_POST['validfrom'];
        $validto = $_POST['validto'];

        $response = array();
        if ($status != "")
        {
            if (isset($validfrom) && isset($validto))
            {
                $isvalid = $this->checkDateRange($validfrom, $validto);
                $r = (array)json_decode($isvalid);
                $status = $this->stringStatus($status);

                if ($r['ErrorCode'] > 0)
                {
                    $response = array('ErrorCode' => 1,
                                      'Message' => $r['Message'],
                                      'Status' => $status);
                }
                else
                {
                    $response = array('ErrorCode' => 0,
                                      'ValidFrom' => date("M d, Y h:i A", strtotime($validfrom)),
                                      'ValidTo' => date("M d, Y h:i A", strtotime($validto)),
                                      'Status' => $status);
                }
            }
            else
            {
                $response = array('ErrorCode' => 0, 'Status' => $status);
            }
        }
        echo json_encode($response);
    }
    public function actionDownload()
    {
        $filename = 'CouponBatchID'.'_'.Yii::app()->session['AID'].'.csv';

        $batchID = $_POST['batchID'];

        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="CouponBatchID'.$batchID.'_'.date('mdy').'.csv"');

        ob_clean();
        flush();
        readfile($filename);
        exit();
    }
}
?>
