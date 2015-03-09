<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-11-02
 * Company: Philweb
 * ***************** */
require_once("init.inc.php");
App::LoadCore("URL.class.php");
App::LoadModuleClass("Membership", "MemberSessions");
$module = "";
$classname = "";
$method = "";
$methodarguments = "";
$datasourcetext = "";
$datasourcevalue = "";
//App::LoadCore("File.class.php");
//$filename = dirname(__FILE__) . "/posts.txt";
//App::Pr($filename);
//$fp = new File($filename);
if(isset($_SESSION['sessionID']) || isset($_SESSION['MID'])){
    $sessionid = $_SESSION['sessionID'];
    $aid = $_SESSION['MID'];
}
else{
    $sessionid = 0;
    $aid = 0;
}
//Check restricted page

$_MemberSessions = new MemberSessions();

$sessioncount = $_MemberSessions->checkifsessionexist($aid, $sessionid);
foreach ($sessioncount as $value) {
    foreach ($value as $value2) {
        $sessioncount = $value2['Count'];
    }
}
if($sessioncount > 0)
{
    if (isset($_POST["Module"]) && isset($_POST["Class"]) && isset($_POST["Method"]))
    {
        $module = $_POST["Module"];
        $classname = $_POST["Class"];
        $method = $_POST["Method"];
        $methodarguments = $_POST["MethodArgs"];

        App::LoadModuleClass($module, $classname);
        eval("\$" . $classname . "= new " . $classname . "();");
        eval("\$retval = \$" . $classname . "->$method(\"$methodarguments\");");
        echo json_encode($retval);


    }
}
else 
{
    $msg = 'Session Expired';
    session_destroy();
    echo json_encode($msg);
}

?>