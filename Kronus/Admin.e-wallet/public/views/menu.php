<?php 
session_start();
//include 'process/ProcessLogin.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
         <div style="float:right;">
            <a href="process/ProcessLogout.php">Logout</a>
        </div>
         <?php                       
            $rightid = $_SESSION['acctype'] ;             
            //$rightid = 5;  
            $dom = new DOMDocument;
            $dom->load('../xml/newXMLDocument.xml');
            $xpath = new DOMXPath($dom);
            $menu = $xpath->query('//right[@id='.$rightid.']/menu');
            if($menu->length)
            {
               foreach ($menu as $menu)
              {
                  $menid = $menu->getAttribute('id');
                  echo "<a href='".$menu->getAttribute("path")."' style='color:Black;font-weight:Bold;' >".$menu->getAttribute("name")."</a>";
                  echo "<br/>";
                  $sub = $xpath->query('//menu[@id='.$menid .']/submenu');
                  if ($sub->length) 
                 {
                     echo "<ul>";
                    foreach ($sub as $sub) 
                    {
                        echo "<li><a href='".$sub->getAttribute('path')."' >".$sub->getAttribute('name')."</a></li>";
                    }
                    echo "</ul>";
                 }          
               }
            }
            else 
            {
              die('xml not found or is empty');
            }
         ?>         
    </body>
</html>
