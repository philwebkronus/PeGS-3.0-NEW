<?php

session_start();

$coreDir = dirname( __FILE__ ) . '/';
$rootDir = realpath( $coreDir . '../../' ) . '/';

define ( 'ROOT_DIR', $rootDir );

include ( ROOT_DIR . 'sys/config/web.config.php' );

if ( defined( 'LOCALE' ) )
{
    setlocale( LC_ALL, LOCALE );
}

if ( defined( 'TIME_ZONE' ) )
{
    date_default_timezone_set( TIME_ZONE );
}

if ( defined( 'ENVIRONMENT' ) && ( ENVIRONMENT == 'DEV' ) )
{   
    ini_set( 'display_startup_errors', 'on' );
    ini_set( 'display_errors', 'on' );
    ini_set( 'html_errors', 'on' );
    ini_set( 'log_errors', 'on' );
    ini_set( 'ignore_repeated_errors', 'off' );
    ini_set( 'ignore_repeated_source', 'off' );
    ini_set( 'report_memleaks', 'on' );
    ini_set( 'track_errors', 'on' );
    ini_set( 'docref_root', 0 );
    ini_set( 'docref_ext', 0 );
    ini_set( 'error_log', ROOT_DIR . 'sys/log/PHP_errors.log' );
    ini_set( 'error_reporting', -1 );
    ini_set( 'log_errors_max_len', 0 );
}
else
{
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
    ini_set( 'error_log', ROOT_DIR . 'sys/log/PHP_errors.log' );
    ini_set( 'error_reporting', -1 );
    ini_set( 'log_errors_max_len', 0 );
}

if ( defined( 'DEBUG' ) && ( DEBUG == TRUE ) )
{   
     error_reporting(E_ALL & ~E_DEPRECATED);
    //error_reporting( E_ALL ^ (E_NOTICE | E_WARNING) );
    if ( defined( 'CUSTOM_ERROR_HANDLER') && ( CUSTOM_ERROR_HANDLER == TRUE ) )
    {
        set_error_handler( 'custom_ErrorHandler' );
    }
}

function custom_ErrorHandler( $errno, $errstr, $errfile, $errline )
{
    if ( error_reporting() == 0 )
    {
        return;
    }

    writeToDebugLog( "Error [$errno] [$errstr] at $errline in $errfile" );

    // print("Error [$errno] [$errstr] at $errline in $errfile.<br>");
}

function writeToDebugLog( $string )
{
    // TODO
}

?>
