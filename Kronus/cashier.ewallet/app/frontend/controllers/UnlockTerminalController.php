<?php
Mirage::loadComponents(array('FrontendController'));
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UnlockTerminalController
 *
 * @author jdlachica
 */
class UnlockTerminalController extends FrontendController{
    
    public function overviewAction(){
        Mirage::loadModels(array('UnlockTerminalFormModel','TerminalsModel','SiteDenominationModel'));
        
        // instance of model
        $UTFormModel = new UnlockTerminalFormModel();
        $terminalsModel = new TerminalsModel();
        
        $terminals = $terminalsModel->getAllNotActiveForceTTerminals($this->site_id, $this->len);
        if(isset($_POST['UnlockTerminalFormModel']) && $this->isAjaxRequest()) {
            $UTFormModel->setAttributes($_POST['UnlockTerminalFormModel']);
            //$terminal_id = $UTFormModel->terminal_id;
            $unlockTerminalFM->amount = toInt($UTFormModel->amount);
            $this->_unlockSession($UTFormModel);
            $unlockTerminalFM->amount = toMoney($UTFormModel->amount);
        }
        
        $this->renderPartial('unlockterminal_overview',array('UTFormModel'=>$UTFormModel,'terminals'=>$terminals));
       
    }
}
