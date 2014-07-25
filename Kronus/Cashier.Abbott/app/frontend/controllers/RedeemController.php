<?php
Mirage::loadComponents('FrontendController');
/**
 * Date Created 11 14, 11 2:20:28 PM <pre />
 * Description of RedeemController
 * @author Bryan Salazar
 */
class RedeemController extends FrontendController{
    
    public $title = 'Redemption';
    
    public function overviewAction() {
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel'));
        $startSessionFormModel = new StartSessionFormModel();
        $terminalsModel = new TerminalsModel();
        
        $len = strlen($this->site_code) + 1;
        $terminals = $terminalsModel->getAllActiveTerminals($this->site_id, $len);        
        
        if(isset($_POST['StartSessionFormModel']) && $this->isAjaxRequest()) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            if($startSessionFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_redeem($startSessionFormModel);
            }
            $this->throwError('Invalid Input');
        }
        $this->render('redeemsession_overview',array('startSessionFormModel'=>$startSessionFormModel,'terminals'=>$terminals));
    }
    
    public function getDetailAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest()) 
            Mirage::app()->error404();
        
        Mirage::loadModels(array('TerminalSessionsModel','TransactionSummaryModel','TransactionDetailsModel'));
        //$transactionSummaryModel = new TransactionSummaryModel();
        $transactionDetailModel = new TransactionDetailsModel();
        $terminalSessionsModel = new TerminalSessionsModel();
        
        //$last_trans_summary_id = $transactionSummaryModel->getLastTransSummaryId($_POST['terminal_id'],$this->site_id);
        $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($_POST['terminal_id']);
        $trans_details = $transactionDetailModel->getSessionDetails($last_trans_summary_id);  
        
        echo json_encode(array('trans_details'=>$trans_details));
        Mirage::app()->end();
    } 

}