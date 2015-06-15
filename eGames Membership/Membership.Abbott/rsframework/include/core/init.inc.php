<?php
error_reporting(E_ALL);

session_start();
$datenow = date('Y_m_d');
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

define ( 'ROOT_DIR', $basepath );

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
    
    ini_set( 'display_startup_errors', 'on' );
    ini_set('display_errors','On');
    ini_set( 'html_errors', 'on' );
    ini_set( 'log_errors', 'on' );
    ini_set( 'ignore_repeated_errors', 'off' );
    ini_set( 'ignore_repeated_source', 'off' );
    ini_set( 'report_memleaks', 'on' );
    ini_set( 'track_errors', 'on' );
    ini_set( 'docref_root', 0 );
    ini_set( 'docref_ext', 0 );
    ini_set( 'error_log', $basepath . 'include/log/'.$datenow.'.log' );
}
else
{
    App::LoadSettings("dbsettings.inc.php");
    
    ini_set( 'display_startup_errors', 'off' );
    ini_set( 'display_errors', 'off' );
    ini_set( 'html_errors', 'off' );
    ini_set( 'log_errors', 'on' );
    ini_set( 'ignore_repeated_errors', 'off' );
    ini_set( 'ignore_repeated_source', 'off' );
    ini_set( 'report_memleaks', 'on' );
    ini_set( 'track_errors', 'on' );
    ini_set( 'docref_root', 0 );
    ini_set( 'docref_ext', 0 );
    ini_set( 'error_log', $basepath . 'include/log/'.$datenow.'.log' );
}

?>