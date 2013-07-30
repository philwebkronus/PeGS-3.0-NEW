<?php

/**
 * SAPI for Lock/Unlock Terminal Function
 *
 * WEBiTS R&D
 * Copyright 2013. PhilWeb Corporation
 *
 */

/** Configuration Section **/

$_DBHost = "";
$_DBName = "spyder";
$_DBUser = "";
$_DBPassword = "";

$_SpyderConnTimeout = 3;
$_SpyderPort = 35000;

/** Do not edit beyond this line **/

$terminaName = $_GET[ "TerminalName" ];
$commandId = $_GET[ "CommandID" ];
$username = $_GET[ "UserName" ];
$password = $_GET[ "Password" ];
$type = $_GET[ "Type" ];
$casinoId = $_GET[ "CasinoID" ];

if ( !isset( $_GET[ "TerminalName" ] ) ||
    !isset( $_GET[ "CommandID" ] ) ||
    !isset( $_GET[ "UserName" ] ) ||
    !isset( $_GET[ "Password" ] ) ||
    !isset( $_GET[ "Type" ] ) ||
    !isset( $_GET[ "CasinoID" ] ) )
{
    echo 'Required parameters missing';
} else {
    $terminalConnectionInfo = getServerConnectionByTerminal( $terminaName );

    if ( $terminalConnectionInfo != null ) {
        $message = null;

        if ( $commandId == "0" ) {
            $message = "unlock|" . $username . "|" . $password . "|" . $casinoId;
        } else if ( $commandId == "1" ) {
            $message = "lock|||";
        }

        if ( $message != null ) {
            sendMessage ( $terminalConnectionInfo->server, $_SpyderPort, $terminalConnectionInfo->channelid, $message );
        } else {
            echo 'Invalid command id supplied';
        }
    } else {
        echo 'No terminal connection info found';
    }
}

function getServerConnectionByTerminal( $terminalName ) {
    $db = null;
    $result = null;

    try {
        $sql = "SELECT server, ipaddress channelid, state FROM connection WHERE terminalname = :terminalName AND server IS NOT NULL";

        $db = getConnection();
        $stmt = $db->prepare( $sql );
	$stmt->setFetchMode( PDO::FETCH_OBJ );
        $stmt->bindParam( "terminalName", $terminalName );
        $stmt->execute();
        $result = $stmt->fetchObject();
    } catch(PDOException $PDOEx) {

    } catch (Exception $ex) {

    }

    return $result;
}

function sendMessage( $spyderHost, $spyderPort, $channelId, $message ) {
    global $_SpyderConnTimeout;

    $delimeter = "\r\n";

    $command = "/send?clientid={$channelId}&msg={$message}" . $delimeter;

    $socket = stream_socket_client( "tcp://$spyderHost:$spyderPort", $errno, $errstr, $_SpyderConnTimeout );

    if ( $socket ) {
        fwrite( $socket, $command, strlen( $command ) );

        $response = null;

        while ( !feof( $socket ) ) {
                $response .= fread( $socket, 20240 );
        }

        fclose( $socket );

        echo $response;
    } else {
        echo "$errstr ($errno)<br />\n";
    }
}

function getConnection() {
    global $_DBHost;
    global $_DBName;
    global $_DBUser;
    global $_DBPassword;

    $dbh = new PDO( "mysql:host=$_DBHost;dbname=$_DBName", $_DBUser, $_DBPassword );
    $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

    return $dbh;
}

?>