<?php
Mirage::loadComponents(array('FrontendController'));
/**
 * Date Created 11 14, 11 2:19:57 PM <pre />
 * Description of ReloadSessionController
 * @author Bryan Salazar
 */
class ReloadSessionController extends FrontendController{
    public $title = 'Reload Session';
    
    public function overviewAction() {
        Mirage::loadModels(array('StartSessionFormModel','TerminalsModel'));
        $startSessionFormModel = new StartSessionFormModel();
        $terminalsModel = new TerminalsModel();
        
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveTerminals($this->site_id, $this->len);
        
        if(isset($_POST['StartSessionFormModel']) && $this->isAjaxRequest()) {
            Mirage::loadComponents('CasinoApi');
            Mirage::loadModels(array('TerminalSessionsModel','SiteDenominationModel'));  
            
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
                $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
                $terminalSessionsModel = new TerminalSessionsModel();
                $terminal_session_data = $terminalSessionsModel->getDataById($startSessionFormModel->terminal_id);
                $service_id = $terminal_session_data['ServiceID'];
                $this->_reload($startSessionFormModel,$service_id);
        }
        $this->render('reloadsession_overview',array('startSessionFormModel'=>$startSessionFormModel,
            'terminals'=>$terminals));
    }
}

