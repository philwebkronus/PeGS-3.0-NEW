<?php
ob_start();
/*
 * Created By: Arlene Salazar
 * Purpose: Controller for Excel Convertion for Top Up
 * Created On: June 23,2011
 */

#### Roshan's very simple code to export data to excel
#### Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
#### if you have any problem contact me at http://roshanbh.com.np
#### fell free to visit my blog http://php-ajax-guru.blogspot.com

    //code to download the data of report in the excel format
    $fn=$_GET['fn'].".xls";
    include_once(__DIR__."/../sys/class/class.export_excel.php");
    //create the instance of the exportexcel format
    $excel_obj=new ExportExcel("$fn");
    //setting the values of the headers and data of the excel file
    //and these values comes from the other file which file shows the data
    $excel_obj->setHeadersAndValues($_SESSION['report_header'],$_SESSION['report_values']);
    //now generate the excel file with the data and headers set
    $excel_obj->GenerateExcelFile();
    //print_r($_SESSION['report_values']);

    //unset($_SESSION['report_values']);
?>