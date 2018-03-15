<?php

Mirage::loadComponents(array('FrontendController'));

/**
 * Date Created 11 14, 11 2:19:57 PM <pre />
 * Description of ReloadSessionController
 * @author Bryan Salazar
 */
class ReloadSessionController extends FrontendController {

    public $title = 'Reload Session';

    public function overviewAction() {
        Mirage::loadModels(array('StartSessionFormModel', 'TerminalsModel', 'SitesModel'));
        $startSessionFormModel = new StartSessionFormModel();
        $terminalsModel = new TerminalsModel();
        $sitesModel = new SitesModel();
        $terminals = $terminalsModel->getAllActiveTerminals($this->site_id, $this->len);

        if (isset($_POST['StartSessionFormModel']) && $this->isAjaxRequest()) {
            Mirage::loadComponents('CasinoApi');
            //Mirage::loadModels(array('TerminalSessionsModel','SiteDenominationModel'));  
            Mirage::loadModels(array('TerminalSessionsModel'));  // removed SiteDenominationModel CCT 
            $startSessionFormModel->setAttributes($_POST['StartSessionFormModel']);
            $startSessionFormModel->amount = toInt($startSessionFormModel->amount);
            $terminalSessionsModel = new TerminalSessionsModel();
            $terminal_session_data = $terminalSessionsModel->getDataById($startSessionFormModel->terminal_id);
            $service_id = $terminal_session_data['ServiceID'];
            $this->_reload($startSessionFormModel, $service_id);
        }

        $eBingoDenomination = Mirage::app()->param['eBingoDepositDenom'];
        $eBingoDivisibleBy = Mirage::app()->param['eBingoDivisibleBy'];
        $eBingoMinDeposit = Mirage::app()->param['eBingoMinDeposit'];
        $eBingoMaxDeposit = Mirage::app()->param['eBingoMaxDeposit'];

        $siteAmountInfo = $sitesModel->getSiteAmountInfo($this->site_id);
        $this->render('reloadsession_overview', array('startSessionFormModel' => $startSessionFormModel,
            'terminals' => $terminals, 'siteAmountInfo' => $siteAmountInfo,
            'eBingoDenomination' => $eBingoDenomination, 'eBingoDivisibleBy' => $eBingoDivisibleBy,
            'eBingoMinDeposit' => $eBingoMinDeposit, 'eBingoMaxDeposit' => $eBingoMaxDeposit
        ));
    }

    public function reloadUbaccountAction() {
        Mirage::loadModels(array('ForceTFormModel', 'TerminalsModel'));
        $forceTFormModel = new ForceTFormModel();
        $terminalsModel = new TerminalsModel();
        $terminals = $terminalsModel->getAllActiveTerminals($this->site_id, $this->len);

        if (isset($_POST['ForceTFormModel']) && $this->isAjaxRequest()) {
            Mirage::loadModels(array('TerminalSessionsModel', 'SiteDenominationModel'));
            $forceTFormModel->setAttributes($_POST['ForceTFormModel']);
            $forceTFormModel->amount = toInt($forceTFormModel->amount);
            $service_id = Mirage::app()->param['UBCasinoServiceID'];
            $this->_reloadforcet($forceTFormModel, $service_id);
        }
        $this->render('reloadsession_overview', array('startSessionFormModel' => $forceTFormModel,
            'terminals' => $terminals));
    }

}

