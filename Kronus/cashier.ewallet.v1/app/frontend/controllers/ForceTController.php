<?php
Mirage::loadComponents(array('FrontendController'));
Mirage::loadModels('ForceTFormModel');
Mirage::loadModels('TerminalsModel');
Mirage::loadModels('BanksModel');

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
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        $banks = $banksModel->generateBanks();
        $this->renderPartial('forcet_load', array('FTModel'=>$FTModel, 'terminals'=>$terminals, 'banks'=>$banks));
    }
    
    public function withdrawAction(){
        $FTModel = new ForceTFormModel();
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveForceTTerminals($this->site_id, $this->len);
        $this->renderPartial('forcet_withdraw', array('FTModel'=>$FTModel, 'terminals'=>$terminals));
    }
}

