<?php
Mirage::loadComponents(array('FrontendController'));
Mirage::loadModels('ForceTFormModel');
Mirage::loadModels('TerminalsModel');
Mirage::loadModels('BanksModel');
Mirage::loadModels('TerminalSessionsModel');

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForceTController
 *
 * @author jdlachica
 */
class ForceTController extends FrontendController {
    public $title = 'Load and Withdraw';
    
    public function overviewAction(){
        $FTModel = new ForceTFormModel();
        $transaction_type = array(
            ''=>'Please select transaction type',
            $this->createUrl('forcet/load')=>'Load',
            $this->createUrl('forcet/withdraw')=>'Withdraw',
        );
        $this->render('forcet_overview', array('FTModel'=>$FTModel, 'transaction_type'=>$transaction_type));
    }
    
    public function loadAction(){
        $FTModel = new ForceTFormModel();
        $banksModel = new BanksModel();
        $terminalsModel = new TerminalsModel();
        $terminalSessionModel = new TerminalSessionsModel();
        //$terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        $serviceid = Mirage::app()->param['UBCasinoServiceID'];
        $terminals = $terminalSessionModel->getEwalletTerminal($this->site_id,$serviceid);
        //$sel_terminals = array(0=>'Select terminal');
        $sel_terminals = array();
        foreach($terminals as $key=>$value){
            $tcode = $value['TerminalCode'];
            $terminalCode = str_replace($this->site_code, '', $tcode);
            $card = $value['LoyaltyCardNumber'];
            $sel_terminals[$key]['id']=$card.':'.$value['TerminalID'];
            $sel_terminals[$key]['code']=$terminalCode;
        }
        
        $banks = $banksModel->generateBanks();
        $this->renderPartial('forcet_load', array('FTModel'=>$FTModel, 'sel_terminals'=>$sel_terminals, 'banks'=>$banks));
    }
    
    public function withdrawAction(){
        $FTModel = new ForceTFormModel();
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        $this->renderPartial('forcet_withdraw', array('FTModel'=>$FTModel, 'terminals'=>$terminals));
    }
    
    public function geteSAFEtransSessionDetailsAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
            Mirage::loadComponents('CasinoApi');
            Mirage::loadModels(array('EWalletTransModel','TerminalSessionsModel'));
            $ewallettransModel = new EWalletTransModel();
            $terminalSessionsModel = new TerminalSessionsModel();
            $casinoApi = new CasinoApi();
            
            if($_POST['isbyterminal']){
                $terminal_session_data = $terminalSessionsModel->getDataById($_POST['terminal_id']);
            
                $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($_POST['terminal_id']);

                foreach ($casinoUBDetails as $val){
                    $casinoUsername = $val['UBServiceLogin'];
                    $casinoPassword = $val['UBServicePassword'];
                    $mid = $val['MID'];
                    $loyaltyCardNo = $val['LoyaltyCardNumber'];
                    $casinoUserMode = $val['UserMode'];
                    $casinoServiceID = $val['ServiceID'];
                }

                list ($terminal_balance) = $casinoApi->getBalanceUB($_POST['terminal_id'], $this->site_id, 'R', 
                            $casinoServiceID, $this->acc_id, $casinoUsername, $casinoPassword);

                $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($_POST['terminal_id']);
                $trans_details = $ewallettransModel->getSessionDetails($last_trans_summary_id);
                $terminal_session_data['DateStarted'] = date('Y-m-d h:i:s A',strtotime($terminal_session_data['DateStarted']));

                $deno_casino_min_max = array('with_session' => 1,'trans_details'=>$trans_details,
                    'terminal_session_data'=>$terminal_session_data,'terminal_balance'=>toMoney($terminal_balance));
            } else {
                $serviceid = Mirage::app()->param['UBCasinoServiceID'];
                $terminal_id = $terminalSessionsModel->checkeSAFECardSession($_POST['cardnumber'],$serviceid);
                
                if(!empty($terminal_id)){
                    $terminal_session_data = $terminalSessionsModel->getDataById($terminal_id);
                    $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminal_id);

                    foreach ($casinoUBDetails as $val){
                        $casinoUsername = $val['UBServiceLogin'];
                        $casinoPassword = $val['UBServicePassword'];
                        $mid = $val['MID'];
                        $loyaltyCardNo = $val['LoyaltyCardNumber'];
                        $casinoUserMode = $val['UserMode'];
                        $casinoServiceID = $val['ServiceID'];
                    }

                    list ($terminal_balance) = $casinoApi->getBalanceUB($terminal_id, $this->site_id, 'R', 
                                $casinoServiceID, $this->acc_id, $casinoUsername, $casinoPassword);

                    $last_trans_summary_id = $terminalSessionsModel->getLastSessSummaryID($terminal_id);
                    $trans_details = $ewallettransModel->getSessionDetails($last_trans_summary_id);
                    $terminal_session_data['DateStarted'] = date('Y-m-d h:i:s A',strtotime($terminal_session_data['DateStarted']));

                    $deno_casino_min_max = array('with_session' => 1,'trans_details'=>$trans_details,'terminal_session_data'=>$terminal_session_data,
                                                                                'terminal_balance'=>toMoney($terminal_balance),'terminal_id' => $terminal_id);
                } else {
                    $deno_casino_min_max = array('with_session' => 0,'trans_details'=>'','terminal_session_data'=>'');
                }
                
            }
        
        echo json_encode($deno_casino_min_max);
        Mirage::app()->end();
    }
}

