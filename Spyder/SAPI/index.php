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
$_DBName = "";
$_DBUser = "";
$_DBPassword = "";
$_SpyderConnTimeout = 3;
$_SpyderPort = 35000;

//ADDED JAV 02132019
$incrementCasinoID = 90; // Do not use for other casinoIDs; Dedicated for Habanero only

//for old sapi
$username = $_GET[ "UserName" ];
$password = $_GET[ "Password" ];
$casinoId = $_GET[ "CasinoID" ];

//ADDED JAV 02132019
if($casinoId == 29 || $casinoId == 25)
{
  $casinoId = $incrementCasinoID;
}

/** Do not edit beyond this line **/
if (!( isset( $_GET[ "TerminalName" ])&&
    (isset( $_GET[ "CommandID" ] ) )))
{
    echo 'TerminalName or CommandID missing';
} else {    
    $terminaName = $_GET[ "TerminalName" ];
    $commandId = $_GET[ "CommandID" ];
    $terminalConnectionInfo = getServerConnectionByTerminal( $terminaName );

    if ( $terminalConnectionInfo != null ) {
        $message = null;
		$oldmsg = null;
        if ( $commandId == "0" ) {//UNLOCK will go to lobby reqts: cardnumber
	    //$oldmsg =  "unlock|" . $username . "|" . $password . "|" . $casinoId;
	    $oldmsg =  "unlock|" . $username . "|" . $password . "|" . $casinoId;
            $info['Command'] = "UNLOCK";
            $message = json_encode($info);
        } else if ( $commandId == "1" || $commandId == "9" ) {//LOCK
	    $oldmsg = "lock|||";
            $info['Command'] = "LOCK";
            $message = json_encode($info);
        } else if ( $commandId == "2" ) {
            $casinoId = $_GET[ "CasinoID" ];
            $info['Command'] = "RemoveEGMSession";
            $message = json_encode($info);
        }else if ( $commandId == "3" ) {
            $info['Command'] = "RELOAD";
            $message = json_encode($info);
        
        }else if ( $commandId == "4" ) {
            $info['Command'] = "DISABLEBV";
            $message = json_encode($info);
        }else if ( $commandId == "5" ) {
            $info['Command'] = "ENABLEBV";
            $message = json_encode($info);
        }else if ( $commandId == "6" ) {
            $info['Command'] = "DISABLETP";
            $message = json_encode($info);
        }else if ( $commandId == "7" ) {
            $info['Command'] = "ENABLETP";
            $message = json_encode($info);
        }else if ( $commandId == "8" ) {
            $info['Command'] = "EXITEGM";
            $message = json_encode($info);
	}else if ( $commandId == "9" ) {
            $info['Command'] = "CLOSEGAME";
            $message = json_encode($info);
	}else if ( $commandId == "10" ) {
            $info['Command'] = "ENDSESSION";
            $message = json_encode($info);
        }else{
            $message = null;
            echo 'Invalid command id supplied';
        }
        if ( $message != null ) {
            //echo $message;
            $server = $terminalConnectionInfo->server;
            try {
                if (strpos($server, ':'))
                {
                    list($terminalConnectionInfo->server, $_SpyderPort) = explode(":", $server);
                }
				else{
					$message = $oldmsg;
				}
            }catch(Exception $ex){
                
            }
            sendMessage ( $terminalConnectionInfo->server, $_SpyderPort, $terminalConnectionInfo->channelid, $message );
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
        error_log( $PDOEx->getMessage() );
    } catch (Exception $ex) {
        error_log( $ex->getMessage() );
    }

    return $result;
}

function sendMessage( $spyderHost, $spyderPort, $channelId, $message ) {
    global $_SpyderConnTimeout;

    $delimeter = "\r\n";

    $command = "/send?clientid={$channelId}&msg={$message}" . $delimeter;

    try {
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
    } catch (Exception $ex) {
        error_log( $ex->getMessage() );
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
