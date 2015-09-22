<?php
Mirage::loadComponents(array('FrontendController'));
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LockController
 *
 * @author jdlachica
 */
class LockTerminalController extends FrontendController {
    
    public function overviewAction(){
        
         Mirage::loadModels(array('LockTerminalFormModel','TerminalsModel','SiteDenominationModel'));
        
        // instance of model
        $LTFormModel = new LockTerminalFormModel();
        $terminalsModel = new TerminalsModel();
        
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        if(isset($_POST['LockTerminalFormModel']) && $this->isAjaxRequest()) {
            $LTFormModel->setAttributes($_POST['LockTerminalFormModel']);
            if($LTFormModel->isValid(array('terminal_id','amount'),true)) {
                $this->_closeSession($LTFormModel);
            }
            $this->throwError('Invalid Input');
        }
        $this->renderPartial('lockterminal_overview',array('LTFormModel'=>$LTFormModel,'terminals'=>$terminals));
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
