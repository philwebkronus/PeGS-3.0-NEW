<?php
$dbhost="172.16.102.158";
$dbusername="spyderconn";
$dbpassword="4PAUREbNHj=mJ0&W";
$dbname="spyder";
$spyderhost="172.16.102.111";
$spyderport="35000";

function getroutingkey($terminal) {

    global $dbhost,$dbusername,$dbpassword,$dbname,$spyderhost,$spyderport;
    $con = new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
    if ($con->connect_errno) {
        echo "Failed to connect to MySQL: (" . $con->connect_errno . ") " . $con->connect_error;
    }

//    $query = "SELECT a.ChannelID as channelid,b.ExtBindAddress as ip, b.ExtPort as port 
//        FROM terminalconnections as a INNER JOIN servers as b ON a.ServerID = b.ServerID and a.TerminalID = ?";

    $query = "SELECT ipaddress FROM connection where terminalname=?";
    
    if (!($stmt = $con->prepare($query))) {
        echo "Prepare failed: (" . $con->errno . ") " . $con->error;
    }

    if (!$stmt->bind_param("s", $terminal)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    $stmt->bind_result($district);

    /* fetch value */
    $stmt->fetch();
    $stmt->close();
    $con->close();
    return $district;
}

function sendtospyder($str, $channel) {
//Connect to Server
    global $spyderhost,$spyderport;
    $command="/send?clientid={$channel}&msg={$str}\r\n";

	echo $command;

    $socket = stream_socket_client("ssl://{$spyderhost}:{$spyderport}", $errno, $errstr, 30);
    if ($socket) {

        //Start SSL

        //stream_set_blocking($socket, true);

        //stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT);

        //stream_set_blocking($socket, false);
        //Send a command

        fwrite($socket, $command);

        $buf = null;

        //Receive response from server. Loop until the response is finished
        while (!feof($socket)) {

            $buf .= fread($socket, 20240);
        }

        //close connection
	sleep(5);
        fclose($socket);
        //echo our command response

        //echo $buf;
    } else {
        echo $errno;
    }
}

try {
    $terminalid = $_GET['TerminalName'];
    $mode = $_GET['CommandID'];
    $username = $_GET['UserName'];
    $password = $_GET['Password'];
    $type = $_GET['Type'];
    $casinoid = $_GET['CasinoID'];
    if (!isset($_GET['TerminalName']) || $terminalid == NULL) {
//        echo "99";  
        echo "0";
    } elseif (!isset($_GET['CommandID']) || $mode == NULL) {
//        echo "98";
        echo "0";
    } elseif (!isset($_GET['UserName']) || $username == NULL) {
//        echo "97";
        echo "0";
    } elseif (!isset($_GET['Password']) || $password == NULL) {
//        echo "96";
        echo "0";
    } elseif (!isset($_GET['Type']) || $type == NULL) {
//        echo "95";
        echo "0";
    } elseif (!isset($_GET['CasinoID']) || $casinoid == NULL) {
//        echo "94";
        echo "0";
    } else {
//        list($channelid,$ip,$port)=getroutingkey($terminalid);
        $ip=getroutingkey($terminalid);
        if ($mode == "1") {
//            $arrtype = array('Header' => 'Type', 'Data' => '0');
//            $arrmessage = array('Header' => 'Message', 'Data' => 'lock');
//            $arrusername = array('Header' => 'Username', 'Data' => $username);
//            $arrpassword = array('Header' => 'Password', 'Data' => $password);
//            $arrcasinoid = array('Header' => 'CasinoID', 'Data' => $casinoid);
//            $payload = array($arrtype, $arrmessage, $arrusername, $arrpassword, $arrcasinoid);
//            $arr = array('Command' => "lock", 'Version' => "1.0.0", 'PayLoad' => $payload, 'PayLoadCount' => count($payload));
//            $message = json_encode($arr);
//            sendtospyder($message,$ip,$port,$channelid);
            $message="lock|" . $username . "|" . $password . "|" . $casinoid;
            sendtospyder($message,$ip);
            echo "1";
        } elseif ($mode == "0") {
//            $arrtype = array('Header' => 'Type', 'Data' => '1');
//            $arrmessage = array('Header' => 'Message', 'Data' => 'unlock');
//            $arrusername = array('Header' => 'Username', 'Data' => $username);
//            $arrpassword = array('Header' => 'Password', 'Data' => $password);
//            $arrcasinoid = array('Header' => 'CasinoID', 'Data' => $casinoid);
//            $payload = array($arrtype, $arrmessage, $arrusername, $arrpassword, $arrcasinoid);
//            $arr = array('Command' => "unlock", 'Version' => "1.0.0", 'PayLoad' => $payload, 'PayLoadCount' => count($payload));
//            $message = json_encode($arr);
//            sendtospyder($message,$ip,$port,$channelid);
            $message = "unlock|" . $username . "|" . $password . "|" . $casinoid;
            sendtospyder($message,$ip);
            echo "1";
        }else
            echo "0";
    }
} catch (Exception $exc) {
    # $this->SMS->insertunsuccessfulsms($str);
    echo $exc;
}
?>