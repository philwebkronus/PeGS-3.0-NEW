<?php

$dbhost = "";

$dbusername = "";

$dbpassword = "";

$dbname = "";

$spyderhost = "";

$spyderport = "";

function getroutingkey($terminal) {

    global $dbhost, $dbusername, $dbpassword, $dbname;

    $query = "SELECT ipaddress FROM connection where terminalname='{$terminal}'";

    $con = mysqli_connect($dbhost, $dbusername, $dbpassword, $dbname);

    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $result = mysqli_query($con, $query);

    while ($row = mysqli_fetch_array($result)) {
        $ipaddress = $row['ipaddress'];
    }

    return $ipaddress;
}

function sendtospyder($str, $channel) {

    global $spyderhost, $spyderport;

    $command = "/send?clientid={$channel}&msg={$str}\r\n";

    $socket = stream_socket_client("ssl://$spyderhost:$spyderport", $errno, $errstr, 30);

    if ($socket) {

        fwrite($socket, $command);

        $buf = null;

        while (!feof($socket)) {
            $buf .= fread($socket, 20240);
        }

        //close connection

        sleep(5);

        fclose($socket);
    } else {
        echo $errstr;
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
//        list($channelid, $ip, $port) = getroutingkey($terminalid);

        $ip = getroutingkey($terminalid);

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

            $message = "lock|" . $username . "|" . $password . "|" . $casinoid;

            sendtospyder($message, $ip);

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

            sendtospyder($message, $ip);

            echo "1";
        }
        else
            echo "0";
    }
} catch (Exception $exc) {
    echo $exc;
}
?>
