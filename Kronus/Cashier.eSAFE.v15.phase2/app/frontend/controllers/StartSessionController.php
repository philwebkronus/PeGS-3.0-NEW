<?php
Mirage::loadComponents(array('FrontendController'));

/**
 * Date Created 11 14, 11 2:19:43 PM <pre />
 * Description of StartSessionController
 * @author Bryan Salazar
 */
class StartSessionController extends FrontendController {
    
    public $title = 'Start Session';
    
    /**
     * Description: Default page of stand alone start session
     */
    public function overviewAction() {
        // load required models
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel','SiteDenominationModel','BanksModel'));
        
        // instance of model
        $startSessionFormModel = new StartSessionFormModel();
        $terminalsModel = new TerminalsModel();
        $banksModel = new BanksModel();
        
        //$banks = $banksModel->generateBanks();
        
        $terminals = $terminalsModel->getAllNotActiveTerminals($this->site_id, $this->len);
        if(isset($_POST['StartSessionFormModel']) && $this->isAjaxRequest()) {
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
                Mirage::loadComponents('CasinoApi');
                $casinoApi = new CasinoApi();
                $result = $this->_startSession($startSessionFormModel);
                echo json_encode($result);
                Mirage::app()->end();
        }
        $this->render('startsession_overview',array('startSessionFormModel'=>$startSessionFormModel,'terminals'=>$terminals));
    }
}