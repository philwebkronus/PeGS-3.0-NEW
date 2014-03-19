<?php
/**
 * @Description: Views for Image Upload Tester
 * @DateCreated: 2014-03-18
 * @Author: aqdepliyan
 */

include 'config/config.php';  
session_start();

if(isset($_SESSION["ImageUpload"]['uploadedfile']) && isset($_SESSION["ImageUpload"]['message'])){
    if($_SESSION["ImageUpload"]['message'] == "Image Uploaded"){
            $scripts = '$("#thblimited").attr("src","'.$_Config['tmp_url'].$_SESSION["ImageUpload"]['uploadedfile'].'");
                        $("#thblimitedmsgbox").removeAttr("style");
                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                        $("#thbsubmit_limited").attr("IsFilled", "Yes");
                        $("#thbsubmit_limited").attr("ImageName", "'.$_SESSION["ImageUpload"]['uploadedfile'].'");
                        $("#thblimited").attr("IsFilled", "Yes");
                        $("#thblimited").attr("ImageName", "'.$_SESSION["ImageUpload"]['uploadedfile'].'");
                        $("#msg1").removeAttr("style");
                        $("#msg1").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                        $("#msg1").css("color", "white");
                        $("#msg1").html("'.$_SESSION["ImageUpload"]['message'].'"); 
                        parent.frames["savebuttonframe"].document.getElementById("photovar").value = "'.$_SESSION["ImageUpload"]['uploadedfile'].'";';
    } else if($_SESSION["ImageUpload"]['message'] == "Invalid Image Dimension") { 
            $scripts = '$("#thblimited").attr("src","images/image_preview1.jpg");
                        $("#thblimitedmsgbox").removeAttr("style");
                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                        $("#msg1").removeAttr("style");
                        $("#msg1").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                        $("#msg1").css("color", "white");
                        $("#msg1").html("'.$_SESSION["ImageUpload"]['message'].'");';
    } else { 
            $scripts = '$("#thblimited").attr("src","images/image_preview1.jpg");
                        $("#thblimitedmsgbox").removeAttr("style");
                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                        $("#msg1").removeAttr("style");
                        $("#msg1").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                        $("#msg1").css("color", "white");
                        $("#msg1").html("'.$_SESSION["ImageUpload"]['message'].'"); ';
    }
} else {
    $scripts = '$("#thblimited").removeAttr("src");
                $("#thblimited").attr("src","images/image_preview1.jpg");';
}
?>

<link rel='stylesheet' type='text/css' href='css/main.css' />
<script type='text/javascript' src='js/jquery.min.js'></script>
<script type='text/javascript' src='js/jquery-ui.min.js'></script>
<script type='text/javascript' src='js/jquery-1.7.2.min.js'></script>

<script type="text/javascript">
   $("#thbsubmitlimited").live("click", function(){
       $("#thbsubmit_limited").click();
   });

   $("#thbsubmit_limited").live("change", function(){
           $("#uploadingphotoform1").submit();
   });

   jQuery.fn.delay = function(time,func){
       return this.each(function(){
           setTimeout(func,time);
       });
   };

   function bodyload(){

        <?php echo $scripts; ?>

//        $("#msgcontainer1").delay(5000, function(){
//            $("#msgcontainer1").css("display", "none");
//        });
   }
</script>
<form method="POST" action="Upload.php"
      enctype="multipart/form-data" id="uploadingphotoform1">
    <body style="background-color: white;" onLoad="javascript: bodyload();">
    <div id = "upload-image-container" >
        <div id = "upload-header" style = "color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; width: 150px; text-align: center; font-size: 14px;">Upload Photo<br>( tmp/ Directory )</div>
        <img alt = "" src="images/image_preview1.jpg" width = "150" height = "150" id = "thblimited">
        <input type = "file" name = "thbsubmit_limited" id = "thbsubmit_limited" style = "visibility:hidden;position:absolute;top:0;left:0"/>
        <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px; appearance:button; -moz-appearance:button; -webkit-appearance:button;">
            <a href = "javascript: void(0);"  id = "thbsubmitlimited" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Upload Photo</a>
        </div>
        <div id="msgcontainer1"><div id = "thblimitedmsgbox" style = "z-index: 5; float: left; display: none;"></div>
        <p id="msg1" style="display: none"></p></div>
    </div>
    <span style="font-style: italic; font-size: 11px; float: left; height: 10px; width: 160px;"><center>280 x 175 only</center></span>
    </body>
</form>
   


<?php
if(isset($_SESSION["ImageUpload"]['uploadedfile']) && isset($_SESSION["ImageUpload"]['message'])){
    unset($_SESSION["ImageUpload"]['uploadedfile'],$_SESSION["ImageUpload"]['message']);
}
?>