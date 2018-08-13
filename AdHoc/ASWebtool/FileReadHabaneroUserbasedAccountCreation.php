<?php

//ini_set('display_errors', true);
//ini_set('log_errors', true);
date_default_timezone_set("Asia/Taipei");

if (isset($_GET['tail'])) {

    
    if (file_exists('log/HabaneroUserbasedAccountCreation/' . date('mdY') . '.txt')) {
        session_start();
        $handle = fopen('log/HabaneroUserbasedAccountCreation/' . date('mdY') . '.txt', 'r'); // I assume, a.txt is in the same path with file_read.php

        if (isset($_SESSION['offset'])) {
            $data = stream_get_contents($handle, -1, $_SESSION['offset']); // Second parameter is the size of text you will read on each request
            echo nl2br($data);
        } else {
            fseek($handle, 0, SEEK_END);
            $_SESSION['offset'] = ftell($handle);
        }
        exit();
    }
}
?>
