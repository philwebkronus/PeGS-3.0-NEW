<?php

$size = sizeof($menu);
$ctr = 0;

foreach($menu as $m) {
    
    if(!array_key_exists("visible", $m)) {
    
        $visibility = true;
    
    }
    else {
        
        $visibility = $m["visible"];
        
    }
    
    if($visibility) {
        
        if($ctr+1 >= $size) {

            echo CHtml::link($m[0], $m[1]);

        }
        else {

            echo CHtml::link($m[0], $m[1])."&nbsp;|&nbsp;";

        }
    
    }
    
    $ctr++;
    
}

?>
