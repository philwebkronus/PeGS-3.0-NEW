<?php
/**
 * Ticket Auto-Generation Configuration History Controller
 */
class TicketAutoGenConfigHistoryController extends VMSBaseIdentity
{
    CONST ON    = 1;
    CONST OFF   = 2;
    /***********************
     * Entry point of Ticket Auto-generation Configuration History
     *****************/
    public function actionIndex()
    {
        $model = new TicketAutoGenConfHistoryForm();
        
        $option = array();
        //Check accoun type, if PegsOps autofill Tickets, if Marketing autofill Coupons
        $accountype = Yii::app()->session['AccountType'];
        if ($accountype == 8) //Pegs Ops -> Tickets
        {
            $option[] = array('VoucherID' => 2, 'VoucherName' => 'Tickets');
        }
        else if ($accountype == 13)
        {
            $option[] = array('VoucherID' => 1, 'VoucherName' => 'Coupons');
        }
        //Provide list data
        $vouchers = CHtml::listData($option, 'VoucherID', 'VoucherName');
        $this->render('index', array('model' => $model, 'vouchertype' => $vouchers));
    }
    public function actionGetConfigurationHistory()
    {
        $ticketAutoGenConfigHist    = new TicketAutoGenConfigHistory();
        $accounts                   = new AccountTypes();
        
        $vouchertype    = $_POST['vouchertype'];
        $datefrom       = $_POST['date_from'];
        $dateto         = $_POST['date_to'];
        
        $response = array();
        //Check validity in date range
        if (strtotime($datefrom) < strtotime($dateto))
        {
            $history = $ticketAutoGenConfigHist->selectConfigurationHistory($datefrom, $dateto);

            $page = $_POST['page']; // get the requested page
            $limit = $_POST['rows']; // get how many rows we want to have into the grid

            $count = count($history);
            $response = array();

            if ($count > 0)
            {
                $total_pages = ceil($count / $limit);
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
            if ($count == 0)
                $start = 0;

            if ($count > 0)
            {
                $i = 0;
                foreach ($history as $rows)
                {
                    if ($rows['AutoGenerate'] == self::ON)
                    {
                        $autogen = "ON";
                    }
                    else
                    {
                        $autogen = "OFF";
                    }
                    $response["rows"][$i]['id'] = $rows['TicketAutoGenID'];
                    $response["rows"][$i]['cell'] = array(
                        $rows['DateCreated'], 
                        $autogen, 
                        $rows['TicketThresholdLimit'], 
                        $rows['TicketCount'], 
                        $accounts->getUsername($rows['CreatedByAID'])
                    );
                    $i++;
                }

                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $count;
            }
            else
            {
                $i = 0;
                $response["page"]     = $page;
                $response["total"]    = $total_pages;
                $response["records"]  = $count;
            }
            
        }
        echo json_encode($response);
    }
}
?>
