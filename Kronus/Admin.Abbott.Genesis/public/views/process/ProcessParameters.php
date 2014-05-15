<?php

/**
 * Created By: JunJun S. Hernandez
 * Created On: March 18, 2014
 * Purpose: Parameters Maintenance
 */
include __DIR__ . "/../sys/class/Parameters.class.php";
require __DIR__ . "/../sys/core/init.php";

$aid = 0;
if (isset($_SESSION['sessionID'])) {
    $new_sessionid = $_SESSION['sessionID'];
} else {
    $new_sessionid = '';
}
if (isset($_SESSION['accID'])) {
    $aid = $_SESSION['accID'];
}

$oparameters = new Parameters($_DBConnectionString[0]);
$connected = $oparameters->open();

$nopage = 0;
if ($connected) {
    $vipaddress = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $vdate = $oparameters->getDate();
    /*     * ************* SESSION CHECKING *************** */
    $isexist = $oparameters->checksession($aid);
    if ($isexist == 0) {
        session_destroy();
        $msg = "Not Connected";
        $oparameters->close();
        if ($oparameters->isAjaxRequest()) {
            header('HTTP/1.1 401 Unauthorized');
            echo "Session Expired";
            exit;
        }
        header("Location: login.php?mess=" . $msg);
    }
    $isexistsession = $oparameters->checkifsessionexist($aid, $new_sessionid);
    if ($isexistsession == 0) {
        session_destroy();
        $msg = "Not Connected";
        $oparameters->close();
        header("Location: login.php?mess=" . $msg);
    }
    /*     * ************* END SESSION CHECKING *************** */

    //checks if account was locked 
    $islocked = $oparameters->chkLoginAttempts($aid);
    if (isset($islocked['LoginAttempts'])) {
        $loginattempts = $islocked['LoginAttempts'];
        if ($loginattempts >= 3) {
            $oparameters->deletesession($aid);
            session_destroy();
            $msg = "Not Connected";
            $oparameters->close();
            header("Location: login.php?mess=" . $msg);
            exit;
        }
    }

    if (isset($_POST['ParametersList'])) {
        $paramData = $oparameters->getAllParameters();
        $page = $_POST['page']; // get the requested page
        $limit = $_POST['rows']; // get how many rows we want to have into the grid
        $count = count($paramData);
        //this is for computing the limit
        if ($count > 0) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) {
            $page = $total_pages;
        }

        $start = $limit * $page - $limit;
        $limit = (int) $limit;

        if ($count > 0) {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;



            foreach ($paramData as $value) {
                $paramID = $value['ParamID'];
                $paramName = $value['ParamName'];
                $value = $value['ParamValue'];
                $description = $paramData[$i]['ParamDesc'];
                $responce->rows[$i]['id'] = $paramID;
                $responce->rows[$i]['cell'] = array($paramName, $value, $description, "<input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='../views/parametersupdate.php?pid=$paramID';\"/>");
                $i++;
            }
        } else {
            $i = 0;
            $responce->page = $page;
            $responce->total = $total_pages;
            $responce->records = $count;
            $msg = "Parameters: No returned result.";
            $responce->msg = $msg;
        }
        echo json_encode($responce);
    } else if (isset($_POST['AddNewParameter'])) {
        if (isset($_POST['AddNewParameter'])) {
            $paramName = trim($_POST['ParamName']);
            $paramValue = trim($_POST['ParamValue']);
            $paramDescription = trim($_POST['ParamDescription']);

            if ($paramName != '' && $paramValue != '' && $paramDescription != '') {
                $paramNameData = $oparameters->getParamNameByParamName($paramName);
                if ($paramName == $paramNameData) {
                    $responce = array('msg' => 'Param Name already exists!', 'ErrorCode' => 4);
                } else {
                $addNewParameter = $oparameters->addNewParameter($paramName, $paramValue, $paramDescription);
                if($addNewParameter > 0) {
                    $vtransdetails = "Success Adding. Parameter name: ".$paramName;
                    $vauditfuncID = 79;
                    $oparameters->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                    $responce = array('msg' => 'Success in adding new parameter!', 'ErrorCode' => 0);
                } else {
                    $vtransdetails = "Failed Adding. Parameter name: ".$paramName;
                    $vauditfuncID = 79;
                    $oparameters->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                    $responce = array('msg' => 'Failed to add new parameter!', 'ErrorCode' => 1);
                }
                }
            } else {
                $responce = array('msg' => 'Failed! One or more parameters are blank.', 'ErrorCode' => 2);
            }
        } else {
            $responce = array('msg' => 'Failed! One or more parameters are blank.', 'ErrorCode' => 3);
        }
        echo json_encode($responce);
    } else if (isset($_POST['UpdateParameter'])) {
            $paramName = trim($_POST['ParamName']);
            $paramValue = trim($_POST['ParamValue']);
            $paramDescription = trim($_POST['ParamDescription']);
            $paramID = trim($_POST['ParamID']);

            if ($paramName != '' && $paramValue != '' && $paramDescription != '') {
                $updateParameter = $oparameters->updateParameter($paramID, $paramValue, $paramDescription);

                if($updateParameter == 1) {
                    $vtransdetails = "Success Updating. Parameter ID: ".$paramID;
                    $vauditfuncID = 80;
                    $oparameters->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                    $responce = array('msg' => 'Success in updating parameter!', 'ErrorCode' => 0);
                } else if($updateParameter == 2) {
                    $vtransdetails = "Details unchanged in updating. Parameter ID: ".$paramID;
                    $vauditfuncID = 80;
                    $oparameters->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                    $responce = array('msg' => 'Parameter details unchanged!', 'ErrorCode' => 0);
                } else {
                    $vtransdetails = "Failed Updating. Parameter ID: ".$paramID;
                    $vauditfuncID = 80;
                    $oparameters->logtoaudit($new_sessionid, $aid, $vtransdetails, $vdate, $vipaddress, $vauditfuncID); //insert in audittrail
                    $responce = array('msg' => 'Failed to update parameter!', 'ErrorCode' => 1);
                }
            } else {
                $responce = array('msg' => 'Failed! One or more parameters are blank.', 'ErrorCode' => 2);
            }
        echo json_encode($responce); 
    } else if (isset($_POST['GetParamData'])) {
            $paramID = trim($_POST['ParamID']);
            $paramData = $oparameters->getParamDataByParamID($paramID);
            $paramName = $paramData['ParamName'];
            $paramValue = $paramData['ParamValue'];
            $paramDescription = $paramData['ParamDesc'];
            if(!empty($paramData)) {
                $responce = array('ParamID' => $paramID, 'ParamName' => $paramName, 'ParamValue' => $paramValue, 'ParamDescription' => $paramDescription, 'msg' => '', 'ErrorCode' => 0);
            } else {
                $responce = array('msg' => 'Error retrieving data!', 'ErrorCode' => 1);
            }
        echo json_encode($responce); 
    } else {
        $oparameters->close();
    }
} else {
    $msg = "Not Connected";
    header("Location: login.php?mess=" . $msg);
}
?>
