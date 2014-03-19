<?php

/**
 * @Description: Script for Save Process.
 *              Transfer and Deletion of Images included.
 * @DateCreated: 2014-03-18
 * @Author: aqdepliyan
 */

session_start();
include 'config/config.php';

//For Future Purposes
//if($thblimitedphoto != ""){
//    $extname_thblimited = strpos($thblimitedphoto, 'thblimited') !== false ? "limited":"";
//    $thblimited = explode(".",$thblimitedphoto);
//    $ext_thblimited = end($thblimited);
//    $rewarditemname = preg_replace("/[^a-zA-Z0-9+$]/", "-", $rewarditemname);
//    $newthblimitedphoto = $rewarditemname."_".$extname_thblimited.".".$ext_thblimited;
//    if(file_exists("$imagetmpdirectory".$thblimitedphoto)){
//        $transferfile1 = copy("$imagetmpdirectory".$thblimitedphoto, "$imagedirector".$newthblimitedphoto);
//        chmod("$imagetmpdirectory".$thblimitedphoto, 0777);
//    }
//}

if(isset($_POST["photovar"]) && $_POST["photovar"] != ''){
    $uploadedphoto = $_POST["photovar"];
    $tmpdirectory = $_Config["tmp_path"];
    $imagedirector = $_Config["img_path"];

    if(!file_exists("$imagedirector".$uploadedphoto)){
    
        //Transfer the Image from tmp/ to rewarditems/
        $transferfile1 = copy("$tmpdirectory".$uploadedphoto, "$imagedirector".$uploadedphoto);

        //ErrorCode: 0-No Error, 1-Transfer Error, 2-Deletion Error, 3-Invalid Image
        if(!$transferfile1){
            $_SESSION["SaveUpload"]["IsSuccess"] = FALSE;
            $_SESSION["SaveUpload"]["Message"] = "Failed: Error in uploading of images.";
            $_SESSION["SaveUpload"]["ErrorCode"] = 1;
        } else {
            if(file_exists("$tmpdirectory".$uploadedphoto)){
                $unlink1 = $uploadedphoto != "" ? unlink("$tmpdirectory".$uploadedphoto):true;
            }

            //Check if the uploaded images in tmp are deleted.
            if(!$unlink1){
                $_SESSION["SaveUpload"]["IsSuccess"] = FALSE;
                $_SESSION["SaveUpload"]["Message"] = "ERROR: Failed to delete image in tmp/ directory";
                $_SESSION["SaveUpload"]["ErrorCode"] = 2;
            } else {
                $_SESSION["SaveUpload"]["IsSuccess"] = TRUE;
                $_SESSION["SaveUpload"]["Message"] = "Image Successfully Saved.";
                $_SESSION["SaveUpload"]["ErrorCode"] = 0;
            }
        } 
    } else {
        
        if(file_exists("$tmpdirectory".$uploadedphoto)){
            $unlink1 = $uploadedphoto != "" ? unlink("$tmpdirectory".$uploadedphoto):true;
        }
        
        $_SESSION["SaveUpload"]["IsSuccess"] = FALSE;
        $_SESSION["SaveUpload"]["Message"] = "File already exists.";
        $_SESSION["SaveUpload"]["ErrorCode"] = 3;
    }
} else {
    $_SESSION["SaveUpload"]["IsSuccess"] = FALSE;
    $_SESSION["SaveUpload"]["Message"] = "ERROR: Invalid Image";
    $_SESSION["SaveUpload"]["ErrorCode"] = 4;
}

header("Location: transferPhoto.php");
?>
