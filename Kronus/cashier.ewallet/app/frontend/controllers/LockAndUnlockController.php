<?php
Mirage::loadComponents('FrontendController');
Mirage::loadModels('LockAndUnlockFormModel');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Lock&UnlockController
 *
 * @author jdlachica
 */
class LockAndUnlockController extends FrontendController {
    
    public function overviewAction(){
        $LockAndUnlockFormModel = new LockAndUnlockFormModel();
        $transaction_type = array(
            ''=>'Please select transaction type',
            $this->createUrl('lock')=>'Lock',
            $this->createUrl('unlock')=>'Unlock',
        );
        $this->render('lockandunlock_overview', array('LockAndUnlockFormModel'=>$LockAndUnlockFormModel, 'transaction_type'=>$transaction_type));
    }
}
