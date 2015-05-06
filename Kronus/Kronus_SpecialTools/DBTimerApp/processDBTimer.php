<?php

include "modelDBTimer.php";

function timer($time){
    $vcashier = new modelDBTimer('mysql:host=172.16.102.157;dbname=npos,pegsconn,pegsconnpass');
        echo date('h:i:s') . "<br>";

        //sleep for 5 seconds
        sleep($time);

        //start again
        echo date('h:i:s');
        $vcashier->close();
}

if(isset($_GET['time'])){
    $time = $_GET['time'];
        $vcashier = new modelDBTimer('mysql:host=172.16.102.157;dbname=npos,pegsconn,pegsconnpass');
        
        $connected = $vcashier->open();

        if($connected)
        {
            $result = $vcashier->getTime();
            
            $result = $result['NOW(6)'];
            $time = $time * 60;
            echo 'SELECT NOW(6) Result '.$result;
            echo "<br/>";
            timer($time);
            
            
        }
    
}
else{
   echo 'Please try again'; 
}


?>
