<?php

/**
 * Description of batchplayerclassificationmapping
 *
 * @author jdlachica
 * @date 08/29/2014
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Batch Player Classification Mapping";
$currentpage = "Administration";

App::LoadControl("Button");

$btnUpload = new Button('btnUpload', 'btnUpload', 'Upload');
$btnUpload->ShowCaption = true;
$btnUpload->IsSubmit = false;

?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>   
<script type="text/javascript">
    $(document).ready(function(){
        var ReturnMessage = {
            0:'Successful!',
            1:'FileUpload object is not found.',
            2:'No File Selected.',
            3:'Incorrect File Type. Please upload CSV File Only.',
            4:'FileReader is not supported in this browser.',
            5:'File contains no valid data.',
            6:'File has incorrect format.'
        };
        
        var DialogTitle = {0:'Success',1:'Error'};
        
        //It contains names of column in form of an array. This will be the basis for validation.
        var CSVField = genArray(['UBCard','IsVIP','VIPLevel','ToBeEmailed']);
        
        var ObjectName = 'fileUpload1';
        $("#btnUpload").click(function(e){
            validateFile(ObjectName, 'csv');
        });
        
        function genArray(arr){
            var array = {};
            for(var i=0;i<arr.length;i++){
                var field = arr[i];
                array[field]=false;
            }
            return array;
        }
        
//        $("#MainForm").submit(function(e){
//            validateFile('fileUpload1', 'csv');
//            e.preventDefault();
//        });
        var Validity=false;
        function validateFile(ObjectName, FileType){
            
            var ReturnValue = _getFileDetails(ObjectName);
            if(ReturnValue['Valid']===true){
                var FileName = ReturnValue['FileName'];
                if(_validateFileType(FileName, FileType)===true){
                    _validateFileContent(ObjectName);
                }
            }
        }
            
            function _getFileDetails(ObjectName){
                var ReturnValue = {'Valid':false,'FileName':''};
                try{
                    var File = document.getElementById(ObjectName).value;
                    if(File==='' || File===null){
                        ReturnValue['Valid']=false;
                        showDialog(DialogTitle[1],ReturnMessage[2]);
                        
                    }else{
                        ReturnValue['Valid']=true;
                        ReturnValue['FileName']=File;
                    }
                    return ReturnValue;
                }
                catch(err){
                    alert(err);
                    return false;
                }
            }
            
            function _validateFileType(FileName, FileType){
                var Valid = false;
                var extension = FileName.substring(FileName.lastIndexOf('.')+1).toLowerCase();
                FileType===extension?Valid=true:showDialog(DialogTitle[1],ReturnMessage[3]);
                return Valid;
            }
            
            function _validateFileContent(ObjectName){
                var Files = document.getElementById(ObjectName).files;
                __handleFiles(Files);
            }
                
                function __handleFiles(files){
                    if(window.FileReader){
                        __getAsText(files[0]);
                    }else{
                        showDialog(DialogTitle[1],ReturnMessage[4]);
                    }
                }
                
                function __getAsText(FileToRead){
                    var reader = new FileReader();
                    reader.onload = __loadHandler;
                    reader.onerror = __errorHandler;  
                    reader.readAsText(FileToRead);
         
                }
                
                function __loadHandler(event){
                    var csv = event.target.result;
                    __processData(csv);
                }
                
                function __processData(csv){
                    var allTextLines = csv.split(/\r\n|\n/);
                    var lines = [];
                    
                    try{
                        lines = allTextLines[0].split(",");
                    }catch(err){
                        alert(err);
                    }
//                    while (allTextLines.length) {
//                        lines.push(allTextLines.shift().split(','));
//                  }
                    if(allTextLines.length<=1 || lines.length<=1){
                        showDialog(DialogTitle[1],ReturnMessage[5]);
                    }
                    else{
                        var line2 = allTextLines[1].trim().length;
                        if(line2==0){
                            showDialog(DialogTitle[1],ReturnMessage[5]);
                        }else{
                            compareResult(lines);
                        }
                        
                    }
                }
                
                function __errorHandler(evt){
                    if(evt.target.error.name == "NotReadableError") {
                        alert("Cannot read file !");
                    }
                }
        
        function compareResult(Result){
            
            try{
                var arr = Result;
                var i=0;

                for(var key in CSVField){
                    if(CSVField.hasOwnProperty(key)){
                        if(key==arr[i].trim()){
                            CSVField[key]=true;
                        }else{
                            CSVField[key]=false;
                        }
                        i++; 
                    }
                }
                if(i===arr.length){
                    var isValid = compareBooleans(CSVField);
                    //isValid===true?showDialog(DialogTitle[0],ReturnMessage[0]):showDialog(DialogTitle[1],ReturnMessage[6]);
                    if(isValid===true){
                        var FormData = bindForm();
                        $.ajax({
                            type: "POST",
                            url: document.URL,
                            data: FormData,
                            processData: false, contentType: false,
                            success:''
                        });
                    }
                }else{
                    showDialog(DialogTitle[1], ReturnMessage[6]);
                }

            }catch(err){alert(err);
                //showDialog(DialogTitle[1],ReturnMessage[6]);
            }
           
        }
        
        function bindForm(){
            var files = document.getElementById(ObjectName).files;
            var formData = new FormData();
            var file = files[0];

            formData.append('csvfile[]', file, file.name);
            return formData;
        }

        function compareBooleans(arr){
            var Valid = false;
            for(var key in arr){
                if(arr.hasOwnProperty(key)){
                    if(arr[key]===true){
                        Valid=true;
                    }else{
                        Valid=false;
                        return false;
                    }
                }
            }
            return Valid;
        }
        
        function showDialog(Title, Message){
            //console.log(e);alert();
            //console.log(e.preventDefault);alert();
            
            var ContainerMessage='#containerReturnMessage';
            $("#dialogReturnMessage").dialog({
                modal : true,
                title : Title,
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){$(this).dialog('close');$(ContainerMessage).text('');}
                }
            });
            $(ContainerMessage).text(Message);
            //alert(Title+": "+Message);
        }
    });
    
   
</script>
<div align="center">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <h2>Batch Player Classification Mapping</h2><br/>   

                <div class="searchbar formstyle">
                    <form name="frmSearch" id="frmSearch" method="POST" enctype="multipart/form-data">
                        CSV File: 
                        <input type="file" name="fileUpload1" id="fileUpload1" style="outline:1px solid #999;"/>
                        <?php echo $btnUpload; ?>
                    </form>
                </div>
                
                <br/><br/>
                
              
            </div>
        </div>
</div>
<div id="dialogReturnMessage">
    <p id="containerReturnMessage">
        
    </p>
</div>

<?php include("footer.php"); ?>
