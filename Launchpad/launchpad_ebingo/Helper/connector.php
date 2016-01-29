<?php //

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Helper/Logger.class.php';
include_once '../controllers/LobbyController.php';

$function = $_POST["fn"];
if(!empty($function)){
    $lobbycontroller = new LobbyController();
    switch ($function) {
        case "getUserBaseLogin":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->getUserBaseLogin($terminalCode);
            break;
        case "getTerminalBaseLogin":
            $terminalCode = $_POST["TerminalCode"];
            $serviceID = $_POST["ServiceID"];
            $result = $lobbycontroller->getTerminalBaseLogin($terminalCode,$serviceID);
            break;
        case "getAllCasinos":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->getAllCasinos($terminalCode);
            break;
        case "casinoServiceClick":
            $result = $lobbycontroller->casinoServiceClick();
            break;
        case "getServiceID":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->getServiceID($terminalCode);
            break;
        
        case "getTerminalType":
             $terminalCode = $_POST['TerminalCode'];
              
             $code = $lobbycontroller->getTerminalType($terminalCode);
             
             $result['Terminaltype'] =(int)$code[0]['TerminalType'];
             $result['TerminaltypeVIP'] =(int)$code[1]['TerminalType'];
             $result['SiteID'] = (int)$code[0]['SiteID'];
             $result['SiteClassID'] = (int)$code[0]['SiteClassificationID']; //dale 01/21/16
             
            break;
        
        case "getTerminalUserMode":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->getTerminalUserMode($terminalCode);
            break;
        
        case "getTerminalSiteClassification":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->getTerminalSiteClassification($terminalCode);
            break;
        
        case "countServices":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->countServices($terminalCode);
            break;
        
        case "checkForExistingSession":
            $terminalCode = $_POST["TerminalCode"];
            $result = $lobbycontroller->checkForExistingSession($terminalCode);
            break;
        
        default:
            $result = '';
            break;
    }
    echo json_encode($result);
}

?>
