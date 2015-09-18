<?php
session_start();
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
    jQuery(document).ready(function(){
        jQuery("#logout").click(function(){
           window.location.href='../process/ProcessLogout.php'; 
        });
    });
    //Page Loader -- this is to send Menu ID via $_POST (not $_GET)
    function loadpage(x,m)
    {
        document.getElementById('menuform').action = x;
        document.getElementById('mid').value = m;
        document.getElementById('menuform').submit();
    }
</script>
<script language="javascript" type="text/javascript" src="../jscripts/helper.js"></script>

<ul id="loginuser">
    <li>
        <p style="margin: 0 35px;  float: right;">
            <label style="color:white; font-size: 13px;" id="lblDate"></label>,
            <label style="color:white; font-size: 13px;" id="lblTime"></label>
           <br />
            You are logged in as 
            <label style="font-weight: bold;"><?php //echo $_SESSION['uname']; ?></label>, 
            (<?php //echo $_SESSION['accname'];?>)&nbsp;&nbsp;&nbsp;&nbsp;
            |&nbsp;&nbsp;&nbsp;<label id="logout" style="color: yellow; cursor: pointer;font-size: 18px;"> Logout</label>
        </p>
    </li>
</ul>
                 <?php                       
                    $rightid = $_SESSION['acctype'];
                    $dom = new DOMDocument;
                    $dom->load('../../xml/newXMLDocument.xml');
                    $xpath = new DOMXPath($dom);
                    $menu = $xpath->query('//right[@id='.$rightid.']/menu');
                  
                    if($menu->length)
                    {
                        if(isset($_POST['mid']))
                        {
                            $page = $_POST['mid'];
                        }
                        else
                        {
                            $page = $menuid;
                        }
                        echo "<ul>";
                        foreach ($menu as $menu)
                        {
                            $menid = $menu->getAttribute('id');
                            
                            if($menu->getAttribute('pagename') == $page)
                            {
                                echo "<li><a style=\"cursor: pointer; \" class='menulist active' onclick=\"loadpage('".$menu->getAttribute("path") . "','". $menid ."');\" >".$menu->getAttribute("name")."</a></li>";   
                            }
                            else
                            {
                                echo "<li><a style=\"cursor: pointer;\" class='menulist' onclick=\"loadpage('".$menu->getAttribute("path") . "','". $menid ."');\" >".$menu->getAttribute("name")."</a></li>";   
                            }
                        }
                        echo "</ul>";                                              
                    }
                    else 
                    {
                        echo "No Access Rights.";
                    }
                    
                 ?>

    
<!--<ul id="loginuser">
    <li><a href="../../process/ProcessLogout.php" class='menulist'>Logout</a></li>
</ul>-->
<script>
	if (document.getElementById)
	{
		dCurrentDate = new Date("<?php echo date("m/d/Y H:i:s");?>");
		dCurrentDate.setSeconds(dCurrentDate.getSeconds() + 5);
	
		document.getElementById("lblDate").innerHTML = dCurrentDate.toLocaleDateString();
		document.getElementById("lblTime").innerHTML = dCurrentDate.toLocaleTimeString();

		clock();
	}  
</script>
<form name="menuform" id="menuform" method="POST">
    <input type="hidden" id="mid" name="mid" />
    
</form>