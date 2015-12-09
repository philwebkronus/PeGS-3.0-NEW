<?php
Mirage::loadComponents(array('FrontendController','TerminalMonitoringPager'));
/**
 * Date Created 12 27, 11 9:38:44 AM
 * Description of TerminalMonitoringController
 * @package 
 * @author Bryan Salazar <brysalazar12@gmail.com>
 */
class TerminalMonitoringController extends FrontendController{
    
    public $layout = '../sub_modules/monitoring/views/layout/main';
    
    public function overviewAction() {
        $siteid = $this->site_id; 
        $start = 0;
        $len = strlen($this->site_code) + 1;
        
        if(isset($_SESSION['page'])) {
            $start = $_SESSION['page'];
        }
        
        if(isset($_GET['page'])) {
            $start = ($_GET['page'] - 1) * 2;
            $_SESSION['current_page'] = $_GET['page'];
            $_SESSION['page'] = $start;
        }

        Mirage::loadModels(array('TerminalsModel','RefServicesModel'));
        
        $terminalModel = new TerminalsModel();
        $refservicesModel = new RefServicesModel();
        
        $total_terminal = $terminalModel->getNumberOfTerminalsPerSite($siteid);
        $terminals = $terminalModel->getTerminalPerPage($siteid, $start, (Mirage::app()->param['terminal_per_page'] * 4), $len);
        $services = $terminalModel->getServicesGroupByTerminal($siteid);
        $refservices = $refservicesModel->getAllRefServicesByKeyServiceId();

        if($this->isAjaxRequest()) {
            $json = new JsonTerminal();
            $json->terminals = $terminals;
            $json->services = $services;
            $json->refservices = $refservices;
            $json->current_page = $_GET['page'];
            $json->server_date = date('Y-m-d H:i:s');
            echo json_encode($json);
            Mirage::app()->end();
        } else {
            $this->render('terminalmonitoring_overview',array('total_terminal'=>$total_terminal,
                'terminals'=>$terminals,'services'=>$services,'refservices'=>$refservices,'server_date'=>date('Y-m-d H:i:s')));
        }        
    }
}

