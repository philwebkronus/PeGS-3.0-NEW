<?php

/**
 * @Description: Script for Upload Process.
 *              Validation of Image Size and File Size Included.
 * @DateCreated: 2014-03-18
 * @Author: aqdepliyan
 */

session_start();
include 'config/config.php';

$allowedExtensions = $_Config['image_allowed_ext'];

if(isset($_FILES['thbsubmit_limited'])) {
    $file = isset($_FILES['thbsubmit_limited']["name"]) ? $_FILES['thbsubmit_limited'] : '';
    $idname = "thbsubmit_limited";
    $redirectURL = "tmpUpload.php";
}

if($file != ""){
       $fileArr = explode("." , $file["name"]);
       $ext = $fileArr[count($fileArr)-1];

       $size2 = $file['size'];
       $upload_location = $_Config['tmp_path'];

       if($file["error"] > 0){
           $result['message'] = "Upload Failed";
       } else {
           if ($size2 < 307200 ) { // 200KB = 204800; 300 KB = 307200; 1 KB = 1024
               if (in_array($ext, $allowedExtensions)){
                   if(file_exists("$upload_location".$file["name"])){
                       $result['message'] = "File already exists";
                   } else {
                       $image_info = getimagesize($file["tmp_name"]);
                       $image_width = $image_info[0];
                       $image_height = $image_info[1];
                       if($idname == "thbsubmit_limited" || $idname == "thbsubmit_outofstock"){
                           if($image_width == 280 && $image_height == 175){
                               $path_parts = pathinfo($file['name']);
                               $extname = $idname == "thbsubmit_limited" ? "thblimited":"thboutofstock";
                               $newname = $path_parts['filename'].'_'.$extname.'.'.$path_parts['extension'];
                               $rs = move_uploaded_file($file["tmp_name"],"$upload_location".$newname);
                               if($rs){
                                    $result['uploadedfile'] = $newname;
                                    $result['message'] = "Image Uploaded"; 
                               }else{
                                    $result['message'] = "Upload Failed";
                               }
                           } else {
                               $result['message'] = "Invalid Image Dimension";
                           }
                       } 
                   }
               } else {
                   $result['message'] = "Invalid Extension";
               }
           } else{
               $result['message'] = "Too Large File Size";
           }
       }
} else {
   $result= "";
}

if(!isset($result['uploadedfile'])){
   $result['uploadedfile'] = '';
}

$_SESSION["ImageUpload"]['uploadedfile'] = $result['uploadedfile'];
$_SESSION["ImageUpload"]['message'] = $result['message'];

header("Location: ".$redirectURL);

?>
