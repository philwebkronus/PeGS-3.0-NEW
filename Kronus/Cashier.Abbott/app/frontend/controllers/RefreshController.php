<?php
Mirage::loadComponents('FrontendController');
/**
 * Date Created 11 21, 11 1:06:18 PM <pre />
 * Description of RefreshController
 * @author Bryan Salazar
 */
class RefreshController extends FrontendController{
    
    protected $_is_terminal_monitoring = true;
    
    public function getBalancePerPageAction() {
        if(!$this->isAjaxRequest() || !$this->isPostRequest())
            Mirage::app()->error404();
        
        Mirage::loadComponents('CasinoApi');
        Mirage::loadModels(array('TerminalsModel','TerminalSessionsModel'));
        $terminalsModel = new TerminalsModel();
        $casinoApi = new CasinoApi();
        $terminalSessionsModel = new TerminalSessionsModel();
        
        $len = strlen($this->site_code) + 1;
        if(!isset($_SESSION['current_page']))
            $_SESSION['current_page'] = 1;
        
        $start = ($_SESSION['current_page'] - 1) * 2;
        
        $terminals = $terminalsModel->getAllActiveTerminalPerPage($this->site_id, $start, (Mirage::app()->param['terminal_per_page'] * 4), $len);
        
        foreach($terminals as $terminal) {
            
            if($terminal['lastbalance'] != null) {
                
                $casinoUBDetails = $terminalSessionsModel->getLastSessionDetails($terminal['TerminalID']);
                $casinoUserMode = '';
                foreach ($casinoUBDetails as $val){
                    $casinoUsername = $val['UBServiceLogin'];
                    $casinoPassword = $val['UBServicePassword'];
                    $mid = $val['MID'];
                    $loyaltyCardNo = $val['LoyaltyCardNumber'];
                    $casinoUserMode = $val['UserMode'];
                    $casinoServiceID = $val['ServiceID'];
                }

                if($casinoUserMode == 0)
                    $casinoApi->getBalanceContinue($terminal['TerminalID'], $this->site_id, 'R', $terminal['usedServiceID'],$this->acc_id);

                if($casinoUserMode == 1)
                    $casinoApi->getUBBalanceContinue($terminal['TerminalID'], $this->site_id, 'R', 
                                $casinoServiceID, $this->acc_id, $casinoUsername, $casinoPassword);
            }
        }
        echo true;
    }
}

