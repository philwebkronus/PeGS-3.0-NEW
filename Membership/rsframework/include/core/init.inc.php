<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
session_start();
$coredir = dirname(__FILE__) . "/";
$basepath = realpath($coredir . "../../") . "/";
$includedir = $basepath . "include/";
$templatesdir = $basepath . "templates/";
$settingsdir = $includedir . "settings/";
$classdir = $includedir . "classes/";
$dataclassdir = $includedir . "datalayer/";
$moduledir = $includedir . "Modules/";
$controlsdir = $includedir . "controls/";
$librarydir = $includedir . "lib/";

global $_CONFIG;
$_CONFIG["EnableMagicQuotes"] = true;
$_CONFIG["basepath"] = $basepath;
$_CONFIG["coredir"] = $coredir;
$_CONFIG["includedir"] = $includedir;
$_CONFIG["templatesdir"] = $templatesdir;
$_CONFIG["settingsdir"] = $settingsdir;
$_CONFIG["classdir"] = $classdir;
$_CONFIG["dataclassdir"] = $dataclassdir;
$_CONFIG["moduledir"] = $moduledir;
$_CONFIG["controlsdir"] = $controlsdir;
$_CONFIG["librarydir"] = $librarydir;

require_once($coredir . "App.class.php");
App::LoadSettings("settings.inc.php");
App::LoadCore("BaseObject.class.php");
App::LoadCore("BaseEntity.class.php");
App::LoadCore("BaseModule.class.php");
App::LoadCore("ArrayList.class.php");
App::LoadCore("DateSelector.class.php");
App::LoadCore("Pager.class.php");
App::LoadCore("QueryString.class.php");
App::LoadCore("URL.class.php");
App::LoadCore("DatabaseTypes.class.php");
App::LoadCore("EventListener.class.php");

if (App::getParam("devmode") == true)
{
    App::LoadSettings("dbsettingsdev.inc.php");
}
else
{
    App::LoadSettings("dbsettings.inc.php");
}

?>