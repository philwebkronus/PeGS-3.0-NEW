<?php

/**
 * @Description: For process of transferring and deleting photos from tmp to rewarditems directory
 * @DateCreated: 2014-03-18
 * @Author: aqdepliyan
 */

include 'config/config.php';
session_start();

if(isset($_SESSION["SaveUpload"]['IsSuccess']) && isset($_SESSION["SaveUpload"]['ErrorCode'])){
    $msg = "<span id='msg2'>".$_SESSION['SaveUpload']['Message']."</span>";
    if($_SESSION["SaveUpload"]['IsSuccess'] && $_SESSION["SaveUpload"]['ErrorCode'] == 0){
        
        //Image Successfully Saved (transferred and Deleted)
        $scripts = '$("#savemsg").removeAttr("style");
                    $("#savemsg").html("'.$msg.'");
                    $("#savemsg").attr("style","position: relative; display: block; background-color: green; height: 40px");
                    $("#msg2").attr("style", "color: white; text-align: center; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px;");';
    } else if(!$_SESSION["SaveUpload"]['IsSuccess'] && $_SESSION["SaveUpload"]['ErrorCode'] == 1) {
        
        //Image Failed to Transfer to rewarditems/ directory
        $scripts = '$("#savemsg").removeAttr("style");
                    $("#savemsg").html("'.$msg.'");
                    $("#savemsg").attr("style","position: relative; display: block; background-color: red; height: 40px");
                    $("#msg2").attr("style", "color: white; text-align: center; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px;");';
    } else if(!$_SESSION["SaveUpload"]['IsSuccess'] && $_SESSION["SaveUpload"]['ErrorCode'] == 2){       
        
        //Image Failed to Delete from tmp/ directory
        $scripts = '$("#savemsg").removeAttr("style");
                    $("#savemsg").html("'.$msg.'");
                    $("#savemsg").attr("style","position: relative; display: block; background-color: red; height: 40px");
                    $("#msg2").attr("style", "color: white; text-align: center; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px;");';
    } else if(!$_SESSION["SaveUpload"]['IsSuccess'] && $_SESSION["SaveUpload"]['ErrorCode'] == 3){       
        
        //Image already exists
        $scripts = '$("#savemsg").removeAttr("style");
                    $("#savemsg").html("'.$msg.'");
                    $("#savemsg").attr("style","position: relative; display: block; background-color: red; height: 40px");
                    $("#msg2").attr("style", "color: white; text-align: center; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px;");';
    } else {       
        
        //Invalid Image
        $scripts = '$("#savemsg").removeAttr("style");
                    $("#savemsg").html("'.$msg.'");
                    $("#savemsg").attr("style","position: relative; display: block; background-color: red; height: 40px");
                    $("#msg2").attr("style", "color: white; text-align: center; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px;");';
    } 
} else {
    $scripts = '$("#msg2").removeAttr("style");
                $("#msg2").css("display","none");
                $("#savemsg").removeAttr("style");
                $("#savemsg").attr("style", "display: block; background-color: ghostwhite; height: 40px");';
}
?>

<link rel='stylesheet' type='text/css' href='css/main.css' />
<script type='text/javascript' src='js/jquery.min.js'></script>
<script type='text/javascript' src='js/jquery-ui.min.js'></script>
<script type='text/javascript' src='js/jquery-1.7.2.min.js'></script>

<script type="text/javascript">
   $("#savebtn").live("click", function(){
           $("#savephotoform2").submit();
   });
   
   function bodyload(){
        <?php echo $scripts; ?>
   }
   
</script>
<form method="POST" action="Save.php"
      enctype="multipart/form-data" id="savephotoform2">
    <body style="background-color: white;" onLoad="javascript: bodyload();">
    <div id = "save-image-container" >
        <input type="text" name="photovar" id="photovar" style="display: none;" value=""/>
        <div id = "upload-header" style = "color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; width: 150px; text-align: center; font-size: 14px;">Save Photo<br>( rewarditems/ Directory )</div>
        <div style="display: block; background-color: ghostwhite; height: 60px; float:left;" id = "savemsg">
        </div>
        <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px; appearance:button; -moz-appearance:button; -webkit-appearance:button;">
            <a href = "javascript: void(0);"  id = "savebtn" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Save Photo</a>
        </div>
    </div>
    </body>
</form>


<?php
if(isset($_SESSION["SaveUpload"]["IsSuccess"])){
    unset($_SESSION["SaveUpload"]);
}
?>

