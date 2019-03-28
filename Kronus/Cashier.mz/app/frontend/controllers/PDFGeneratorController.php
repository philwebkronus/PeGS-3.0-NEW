<?php
Mirage::loadComponents('FrontendController');
/**
 * Date Created 12 8, 11 10:32:01 AM <pre />
 * Description of PDFGeneratorController
 * @author Bryan Salazar
 */
class PDFGeneratorController extends FrontendController{
    
    public function transactionHistoryAction() {
        if(!$this->isPostRequest())
            Mirage::app()->error404();
        Mirage::loadModels('TransactionSummaryModel');
        
        $transactionSummaryModel = new TransactionSummaryModel();
        $date = $_POST['hidselected_date'];
        $enddate = addOneDay($date);
        $rows = $transactionSummaryModel->getAllTransactionSummary($this->site_id, $this->site_code, $date, $enddate);
        Mirage::loadComponents('CTCPDF');
        $pdf = CTCPDF::c_getInstance();
        $pdf->c_commonReportFormat();
        $pdf->c_setHeader('Transaction History');
//        $pdf->html.='<div style="text-align:center;">As of ' . date('l') . ', ' .
//              date('F d, Y') . ' ' . date('H:i:s A') .'</div>';
        $pdf->html.='<div style="text-align:center;"><h3>Coverage '. date('l , F d, Y ',strtotime($date)) .' 06:00:01 AM to' . date('l , F d, Y ',strtotime($enddate)) .'06:00:00 AM </h3></div>';
        //<h3 id="coverage">Coverage <?php echo date('l , F d, Y ')  06:00:01 AM to  echo date('l , F d, Y ',mktime(0,0,0,date('m'),date('d') + 1, date('Y'))) 06:00:00 AM</h3>
        $pdf->SetFontSize(5);
        $pdf->c_tableHeader2(array(
                array('value'=>'Login'),
                array('value'=>'Time In'),
                array('value'=>'Time Out'),
                array('value'=>'Initial Deposit'),
                array('value'=>'Total Reload'),
                array('value'=>'Redemption'),
                array('value'=>'Gross Hold'),
             ));
        $total_initial_deposit= 0;
        $total_reload = 0;
        $total_withdraw = 0;
        $total_gross_hold = 0;
        foreach($rows as $row) {
            $date_started = explode('.', $row['DateStarted']);
            $d_started = date('Y-m-d h:i:s A',  strtotime($date_started[0]));
            $date_ended = explode('.', $row['DateEnded']);
            $d_ended = (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...');
            
            $total_initial_deposit += $row['Deposit'];
            $total_reload += $row['Reload'];
            $total_withdraw += $row['Withdrawal'];
            $total_gross_hold = $total_gross_hold + $row['Deposit'] + $row['Reload'] - $row['Withdrawal'];
            
            $pdf->c_tableRow2(array(
                array('value'=>$row['TerminalCode']),
                array('value'=> $d_started),
                array('value'=>$d_ended),
                array('value'=>toMoney($row['Deposit']),'align'=>'right'),
                array('value'=>toMoney($row['Reload']),'align'=>'right'),
                array('value'=>toMoney($row['Withdrawal']),'align'=>'right'),
                array('value'=>toMoney($row['Deposit'] + $row['Reload'] - $row['Withdrawal']),'align'=>'right'),
             ));
        }
        $pdf->c_tableRow3(array(
            array('value'=>'<b>GRAND TOTAL</b>','align'=>'center','colspan'=>3),
            array('value'=>toMoney($total_initial_deposit),'align'=>'right'),
            array('value'=>toMoney($total_reload),'align'=>'right'),
            array('value'=>toMoney($total_withdraw),'align'=>'right'),
            array('value'=>toMoney($total_gross_hold),'align'=>'right'),
        ));
        $pdf->c_tableEnd();
        $pdf->c_generatePDF('transhistory.pdf');
        $conn->close();        
    }
//    
//    public function transactionHistoryCashierAction() {
//        if(!$this->isPostRequest())
//            Mirage::app()->error404();
//        Mirage::loadModels('TransactionSummaryModel');
//        
//        $transactionSummaryModel = new TransactionSummaryModel();
//        $start_date = $_POST['hidselected_date'];
//        $end_date = addOneDay($start_date);
//        $rows = $transactionSummaryModel->getAllTransactionPerCashier($this->acc_id, $this->site_code, $start_date, $end_date);
//        Mirage::loadComponents('CTCPDF');
//        $pdf = CTCPDF::c_getInstance();
//        $pdf->c_commonReportFormat();
//        $pdf->c_setHeader('Transaction History Per Cashier');
//        $pdf->html.='<div style="text-align:center;"><h3>Coverage '. date('l , F d, Y ',strtotime($start_date)) .' 06:00:01 AM to' . date('l , F d, Y ',strtotime($end_date)) .'06:00:00 AM </h3></div>';
//        $pdf->SetFontSize(5);
//        $pdf->c_tableHeader2(array(
//                array('value'=>'Login'),
//                array('value'=>'Time In'),
//                array('value'=>'Time Out'),
//                array('value'=>'Initial Deposit'),
//                array('value'=>'Total Reload'),
//                array('value'=>'Redemption'),
//                array('value'=>'Gross Hold'),
//             ));
//        foreach($rows as $row) {
//            $date_started = explode('.', $row['DateStarted']);
//            $d_started = date('Y-m-d h:i:s A',  strtotime($date_started[0]));
//            $date_ended = explode('.', $row['DateEnded']);
//            $d_ended = (($date_ended[0])?date('Y-m-d h:i:s A',  strtotime($date_ended[0])):'Still playing ...');
//            
//            $total_initial_deposit += $row['Deposit'];
//            $total_reload += $row['Reload'];
//            $total_withdraw += $row['Withdrawal'];
//            $total_gross_hold = $total_gross_hold + $row['Deposit'] + $row['Reload'] - $row['Withdrawal'];
//            
//            $pdf->c_tableRow2(array(
//                array('value'=>$row['TerminalCode']),
//                array('value'=> $d_started),
//                array('value'=>$d_ended),
//                array('value'=>toMoney($row['Deposit']),'align'=>'right'),
//                array('value'=>toMoney($row['Reload']),'align'=>'right'),
//                array('value'=>toMoney($row['Withdrawal']),'align'=>'right'),
//                array('value'=>toMoney($row['Deposit'] + $row['Reload'] - $row['Withdrawal']),'align'=>'right'),
//             ));
//        }
//        $pdf->c_tableRow3(array(
//            array('value'=>'<b>GRAND TOTAL</b>','align'=>'center','colspan'=>3),
//            array('value'=>toMoney($total_initial_deposit),'align'=>'right'),
//            array('value'=>toMoney($total_reload),'align'=>'right'),
//            array('value'=>toMoney($total_withdraw),'align'=>'right'),
//            array('value'=>toMoney($total_gross_hold),'align'=>'right'),
//        ));
//        $pdf->c_tableRow3(array(
//            array('value'=>'<b>TOTAL SALES</b>','align'=>'center','colspan'=>3),
//            array('value'=>toMoney($total_initial_deposit+$total_reload),'align'=>'center','colspan'=>2),
//            array('value'=>'','align'=>'right','colspan'=>2),
//        ));
//        $pdf->c_tableEnd();
//        $pdf->c_generatePDF('transhistory.pdf');
//        $conn->close(); 
//    }
}

