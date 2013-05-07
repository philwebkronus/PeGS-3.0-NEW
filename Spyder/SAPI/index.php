<?php

header("Cache-Control: no-cache, must-revalidate");
define('HOST', '192.168.30.135');
define('PORT', '5672');
define('USER', 'guest');
define('PASS', 'guest');
define('VHOST', '/');
define('AMQP_DEBUG', true);
require 'amqp.php';

function getroutingkey($terminal) {

    $con = mysqli_connect("192.168.28.149", "spyderapp", "password", "spyder");
    if (mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    $query = "select alias from connection where terminalname='$terminal'";

    $result = mysqli_query($con, $query);

    $row = mysqli_fetch_array($result);

    mysqli_close($con);

    return $row['alias'];
}

try {
    $terminalid = $_GET['TerminalName'];
    $mode = $_GET['CommandID'];
    $username = $_GET['UserName'];
    $password = $_GET['Password'];
    $type = $_GET['Type'];
    $casinoid = $_GET['CasinoID'];
    if (!isset($_GET['TerminalName']) || $terminalid==NULL) {
//        echo "99";  
        echo "0";
    } elseif (!isset($_GET['CommandID']) || $mode==NULL) {
//        echo "98";
        echo "0";
    } elseif (!isset($_GET['UserName']) || $username==NULL) {
//        echo "97";
        echo "0";
    } elseif (!isset($_GET['Password']) || $password==NULL) {
//        echo "96";
        echo "0";
    } elseif (!isset($_GET['Type']) || $type==NULL) {
//        echo "95";
        echo "0";
    } elseif (!isset($_GET['CasinoID']) || $casinoid==NULL) {
//        echo "94";
        echo "0";
    } else {
        $connection = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
        $channel = $connection->channel();

        $channel->exchange_declare('sapi_ex', 'direct', false, false, false);

//    $data = implode(' ', array_slice($argv, 1));
//    if (empty($data))
//        $data = "lock";

        if ($mode == "1") {
            $message = "lock|" . $username . "|" . $password . "|" . $casinoid;
            $msg = new AMQPMessage($message);
            $channel->basic_publish($msg, 'sapi_ex', getroutingkey($terminalid));
            echo "1";
        } elseif ($mode == "0") {
            $message = "unlock|" . $username . "|" . $password . "|" . $casinoid;
            $msg = new AMQPMessage($message);
            $channel->basic_publish($msg, 'sapi_ex', getroutingkey($terminalid));
            echo "1";
        }else
            echo "0";
        $channel->close();
        $connection->close();
    }
} catch (Exception $exc) {
    # $this->SMS->insertunsuccessfulsms($str);
    echo $exc;
}
?>