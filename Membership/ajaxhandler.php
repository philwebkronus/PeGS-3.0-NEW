<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-11-02
 * Company: Philweb
 * ***************** */
require_once("init.inc.php");
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
?>