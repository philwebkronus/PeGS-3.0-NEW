<?php

 $page = $_POST['page'];
 
    if($page =='logout'){
  
        echo json_encode('logouts');
    }
?>
