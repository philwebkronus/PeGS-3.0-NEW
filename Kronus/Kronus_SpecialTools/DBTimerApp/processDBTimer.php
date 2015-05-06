<?php

include "modelDBTimer.php";


if(isset($_GET['time'])){
    $time = $_GET['time'];
        $vcashier = new modelDBTimer('mysql:host=172.16.102.157;dbname=npos,pegsconn,pegsconnpass');
        
        $connected = $vcashier->open();

        if($connected)
        {
            $result = $vcashier->getTime();
            
            $result = $result['NOW(6)'];
            $time = $time;
            echo 'SELECT NOW(6) Result '.$result;
            echo "<br/>";
            
            echo date('h:i:s') . "<br>";

            sleep($time);

            //start again
            echo date('h:i:s');

            $result2 = $vcashier->getTime();

            $result2 = $result2['NOW(6)'];

            echo 'SELECT NOW(6) Result2 '.$result2;

            $vcashier->close();
            
            
        }
    
}
else{
   echo 'Please try again'; 
}


?>
