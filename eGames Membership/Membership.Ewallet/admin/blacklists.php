<?php
/**
 * Blacklist List
 * @author Mark Kenneth Esguerra
 * @date November 7, 2013
 * @copyright (c) 2013, Philweb Corporation
 * @modifiedBy Noel Antonio
 * @dateModified 11-20-2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

App::LoadCore('Validation.class.php');
$currentpage = "Administration";
$pagetitle = "Player Blacklisting";

App::LoadModuleClass("Membership", "BlackLists");
App::LoadModuleClass("Membership", "AuditTrail");

$log = new AuditTrail();
$blacklist = new BlackLists();

App::LoadControl("Button");
App::LoadControl("TextBox");
App::LoadControl("DatePicker");
App::LoadControl("Hidden");

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$fproc = new FormsProcessor();

$txtLastname = new TextBox("txtLastname", "txtLastname", "Last Name: ");
$txtLastname->ShowCaption = false;
$txtLastname->CssClass = "validate[required, custom[trailingSpaces], custom[onlyLetterSp], minSize[2]]";
$txtLastname->Length = 30;
$txtLastname->Size = 15;
$txtLastname->Style = "height:20px;";
$fproc->AddControl($txtLastname);

$txtFirstName = new TextBox("txtFirstName", "txtFirstName", "First Name: ");
$txtFirstName->ShowCaption = false;
$txtFirstName->CssClass = "validate[required, custom[trailingSpaces], custom[onlyLetterSp], minSize[2]]";
$txtFirstName->Length = 30;
$txtFirstName->Size = 15;
$txtFirstName->Style = "height:20px;";
$fproc->AddControl($txtFirstName);

$dsmaxdate->AddYears(-21);
$dsmindate->AddYears(-100);

$dtBirthDate = new DatePicker("dtBirthDate", "dtBirthDate", "Birth Date: ");
$dtBirthDate->MaxDate = $dsmaxdate->CurrentDate;
$dtBirthDate->MinDate = $dsmindate->CurrentDate;
//$dtBirthDate->SelectedDate = $dsmaxdate->PreviousDate;
$dtBirthDate->ShowCaption = false;
$dtBirthDate->YearsToDisplay = "-100";
$dtBirthDate->CssClass = "validate[required]";
$dtBirthDate->isRenderJQueryScript = true;
$dtBirthDate->Style = "height:20px";
$dtBirthDate->Size = 15;
$fproc->AddControl($dtBirthDate);

$txtRemarks = new TextBox("txtRemarks", "txtRemarks", "Remarks: ");
$txtRemarks->ShowCaption = false;
$txtRemarks->Multiline = true;
$txtRemarks->Columns = 30;
$txtRemarks->Rows = 5;
$fproc->AddControl($txtRemarks);

$hdnProcess = new Hidden("hdnprocess", "hdnprocess");
$hdnProcess->Args = "value='1'";
$fproc->AddControl($hdnProcess);

$hdnBLId = new Hidden("hdnBLId", "hdnBLId");
$fproc->AddControl($hdnBLId);

$btnAdd = new Button("btnaddblacklist", "btnaddblacklist", "Add");
$btnAdd->ShowCaption = true;
$btnAdd->ID = "btnaddblacklist";
$btnAdd->Style = "cursor:pointer";
$btnAdd->Args = "onclick = 'javascript: $('#dlgaddblacklist').dialog('open'); '";
$fproc->AddControl($btnAdd);

$fproc->ProcessForms();

// Add and Update method are consolidated on this part
if ($fproc->IsPostBack)
{
    if (!isset($_POST['confirmAddAgain']) && !isset($_POST['hdnblacklistID']))
    {
        $process = $hdnProcess->SubmittedValue;
        $aid = $_SESSION['userinfo']['AID'];
        if (isset($hdnBLId->SubmittedValue))
            $blackListedID = $hdnBLId->SubmittedValue;
        
        if ($process != 3)
        {
            $lastname   = formatName(mysql_escape_string($txtLastname->SubmittedValue));
            $firstname  = formatName(mysql_escape_string($txtFirstName->SubmittedValue));
            $birthdate  = mysql_escape_string($_POST['dtBirthDate']);
            $remarks    = mysql_escape_string($txtRemarks->SubmittedValue);
            if (($lastname && $firstname && $birthdate) != "")
            {
                if (validateNames($lastname) && validateNames($firstname))
                {
                    //Check if entered data are already exist in records
                    $checkifexist = $blacklist->checkIfExist($lastname, $firstname, $birthdate, $process, $blackListedID);
                    if (count($checkifexist) > 0)
                    {
                        //ADD
                        if ($process == 1)
                        {
                            //If 0, change to blacklisted 
                            if ($checkifexist[0]['Status'] == 0)
                            {
                                $blackListedID = $checkifexist[0]['BlackListedID'];
                                $result = $blacklist->changeBlackListedStat($blackListedID, $aid, 1, $remarks);
                                if (isset($result))
                                {
                                    //Check result
                                    if ($result['TransCode'] == 1)
                                    {
                                        $showdialog = true;
                                        $msg = $result['TransMsg'];
                                        $title = "MESSAGE";
                                    }
                                    else if ($result['TransCode'] == 0)
                                    {
                                        $showdialog = true;
                                        $msg = $result['TransMsg'];
                                        $title = "ERROR MESSAGE";
                                    }
                                }

                            }
                            
                            //Prompt already exist
                            else if ($checkifexist[0]['Status'] == 1)
                            {
                                $showdialog = true;
                                $msg = "The player is already black listed";
                                $title = "ERROR MESSAGE";
                            }
                        }
                        //Update
                        else if ($process == 2)
                        {
                            if ($checkifexist[0]['Status'] == 0)
                            {
                                $showconfirm = true;
                                $ask = "The player is already tagged as whitelisted. Do you want to include it again on our blacklists record?";
                                $hdBLId = $checkifexist[0]['BlackListedID'];
                                $remark = $remarks;
                            }
                            else
                            {
                                $showdialog = true;
                                $msg = "The player is already black listed";
                                $title = "ERROR MESSAGE";
                            }
                        }
                    }
                    else
                    {
                        //Check if what process Add or Update
                        if ($process == 1) // Add to Black List
                        {
                            $result = $blacklist->addToBlackList($lastname, $firstname, $birthdate, $remarks, $aid);
                        }
                        else if ($process == 2) //Update BlackList
                        {
                            $result = $blacklist->updateBlacklistedDetails($lastname, $firstname, $birthdate, $remarks, $blackListedID);
                        }
                        if (isset($result))
                        {
                            //Check result
                            if ($result['TransCode'] == 1)
                            {
                                $showdialog = true;
                                $msg = $result['TransMsg'];
                                $title = "MESSAGE";
                            }
                            else if ($result['TransCode'] == 0)
                            {
                                $showdialog = true;
                                $msg = $result['TransMsg'];
                                $title = "ERROR MESSAGE";
                            }
                        }
                    }
                }
                else
                {
                    $showdialog = true;
                    $msg = "You have entered an invalid character/s";
                    $title = "ERROR MESSAGE";
                }
            }
            else
            {
                $showdialog = true;
                $msg = "Please fill up all fields";
                $title = "ERROR MESSAGE";
            }
        }
        else
        {
            //Remove from list
            $result = $blacklist->changeBlackListedStat($blackListedID, $aid, 2);
            if (isset($result))
            {
                //Check result
                if ($result['TransCode'] == 1)
                {
                    $showdialog = true;
                    $msg = $result['TransMsg'];
                    $title = "MESSAGE";
                }
                else if ($result['TransCode'] == 0)
                {
                    $showdialog = true;
                    $msg = $result['TransMsg'];
                    $title = "ERROR MESSAGE";
                }
            }
        }
    }
    else
    {
        //Add to blacklist
        $blackListedID = $_POST['hdnblacklistID'];
        $remarks = $_POST['remarks'];

        $result = $blacklist->changeBlackListedStat($blackListedID, $aid, 1, $remarks);
        if (isset($result))
        {
            //Check result
            if ($result['TransCode'] == 1)
            {
                $showdialog = true;
                $msg = $result['TransMsg'];
                $title = "MESSAGE";
            }
            else if ($result['TransCode'] == 0)
            {
                $showdialog = true;
                $msg = $result['TransMsg'];
                $title = "ERROR MESSAGE";
            }
        }
    }
}
?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>   
<script type="text/javascript">
    $(document).ready(function(){
            function loadData()
            {
                loadBlacklist();
            }
            loadData();
            function loadBlacklist()
            {
                var url = "Helper/helper.blacklists.php";
                jQuery('#blacklist').GridUnload();
                jQuery("#blacklist").jqGrid({
                        url:url,
                        datatype: "json",
                        mtype : 'post',
                        colNames:['Last Name', 'First Name', 'Birth Date','Action'],
                        colModel:[
                                {name: 'LastName', index: 'LastName', align: 'left', width: 230, fixed: true},
                                {name: 'FirstName', index: 'FirstName', align: 'left', width: 230, fixed: true},
                                {name: 'BirthDate', index: 'BirthDate', align: 'center', width: 180, fixed: true},
                                {name: 'Action', index: 'action', align: 'center', width: 50}
                        ],

                        rowNum: 10,
                        rowList: [10,20,30],
                        height: 300,
                        width: 770,
                        pager: "#pager",
                        refresh: true,
                        loadonce: true,
                        loadText: "Loading...",
                        viewrecords: true,
                        sortorder: "desc",
                        caption:"Player Blacklisting"
                });
                jQuery("#blacklist").jqGrid('navGrid','#pager',
                {
                    edit:false,add:false,del:false, search:false, refresh: false});
            }
            $("#editblacklisted").live('click', function(){
                var LastName    = $(this).attr('LastName');
                var FirstName   = $(this).attr('FirstName');
                var BirthDate   = $(this).attr('BirthDate');
                var Remarks     = $(this).attr('Remarks');
                var ID          = $(this).attr('BlackListedID');
                
                $("#hdnBLId").val(ID);
                $('#txtLastname').val(LastName);
                $("#txtFirstName").val(FirstName);
                $("#dtBirthDate").val(BirthDate);
                $("#txtRemarks").val(Remarks);
                $("#hdnprocess").val(2);
                $("#dlgaddblacklist").dialog('open');
                $("#dlgaddblacklist").dialog('option','title','Update Details');
            });
            $("#deleteblacklisted").live("click", function(){
                var lastname = $(this).attr('LastName');
                var firstname = $(this).attr('FirstName');
                var blacklistID = $(this).attr('BlackListedID');
                //Remove the two fields from first form
                $("#hdnBLId").remove();
                $("#hdnprocess").remove();
                //Attach the remove fields to second form
                $("<input>").attr({
                   type : 'hidden',
                   id : 'hdnprocess',
                   name : 'hdnprocess'
                }).appendTo("#changestat");
                $("<input>").attr({
                   type : 'hidden',
                   id : 'hdnBLId',
                   name : 'hdnBLId'
                }).appendTo("#changestat");
                
                $("#hdnBLId").val(blacklistID);
                $("#confirmdialog").dialog('open');
                $("#ask").html("Are you sure you want to remove "+firstname+" "+lastname+" from the blacklist?");
            });
            $("#blacklistedhist").live('click', function(){
               var blacklistedID = $(this).attr('BlackListedID');
               var value = $(this).attr('value');
               var FirstName = $(this).attr('FirstName');
               
               $("#name").html(value+", "+FirstName);
               loadBlacklistHist(blacklistedID);
               $("#blacklisthist").dialog('open'); 
            });
            function loadBlacklistHist(blacklistedID)
            {
                var url = "Helper/helper.blacklistedhistory.php";
                jQuery('#tblblacklistedhist').GridUnload();
                jQuery("#tblblacklistedhist").jqGrid({
                        url:url,
                        datatype: "json",
                        mtype : 'post',
                        postData : {blacklistedID: blacklistedID},
                        colNames:['Date Recorded','Added By','Remarks'],
                        colModel:[
                                {name: 'DateCreated', index: 'DateCreated', align: 'left', width: 140, fixed: true},
                                {name: 'CreatedByAID', index: 'CreatedByAID', align: 'left', width: 100, fixed: true},
                                {name: 'Remarks', index: 'Remarks', align: 'left', width: 225, fixed: true}
                        ],

                        rowNum: 10,
                        rowList: [10,20,30],
                        height: 200,
                        width: 490,
                        pager: "#pagerhist",
                        refresh: true,
                        loadonce: true,
                        loadText: "Loading...",
                        viewrecords: true,
                        sortorder: "desc",
                        caption:"Blacklist History"
                });
                jQuery("#tblblacklistedhist").jqGrid('navGrid','#pagerhist',
                {
                    edit:false,add:false,del:false, search:false, refresh: true});
            }
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        
        $("#dlgaddblacklist").dialog({
            modal : true,
            title : 'Add to Black List',
            width : 420,
            height : 400,
            autoOpen : false,
            resizable : false,
            draggable :false,
            buttons : {
                'Save' :  function(){
                    $('.formErrorContent').show();
                    $('.formErrorArrow').show();
                    $("#submit-addblacklist").submit();
                },
                'Cancel' : function(){
                    $(this).dialog('close');
            }
            },
            beforeClose: function(){
                $('.formErrorContent').hide();
                $('.formErrorArrow').hide();
            },
        }).parent().appendTo($("#submit-addblacklist").validationEngine());
        
        $("#blacklisthist").dialog({
            modal : true,
            title : 'Blacklist History',
            width : 520,
            height : 450,
            autoOpen : false,
            resizable : false,
            draggable :false,
            buttons : {
                'Close' : function(){
                    $(this).dialog('close');
                }
            }
        }).parent().appendTo($("#submit-addblacklist").validationEngine());
    });
</script>
<script type="text/javascript">
    <?php if (isset($showdialog)): ?>
        $(document).ready(function(){
            $("#dialogmsg").dialog({
                modal : true,
                autoOpen : <?php echo $showdialog; ?>,
                title : '<?php echo $title?>',
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){
                        $(this).dialog('close');
                    }
                }
            });
        });
    <?php endif; ?>
</script>   
<script type="text/javascript">
    $(document).ready(function(){
       $("#btnaddblacklist").click(function(){
          $('#txtLastname').val("");
          $("#txtFirstName").val("");
          $("#dtBirthDate").val("");
          $("#txtRemarks").val("");
          $("#hdnprocess").val(1);
          $("#dlgaddblacklist").dialog('open');

       });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
       <?php if (isset($showconfirm)): ?>
       $("#confirmdialog").dialog({
           modal : true,
           title : 'CONFIRMATION',
           autoOpen: <?php echo $showconfirm; ?>,
           resizable : false,
           draggable :false,
           buttons : {
               'Yes' : function(){
                    //Attach the remove fields to second form
                    $("<input>").attr({
                       type : 'hidden',
                       id : 'confirmAddAgain',
                       name : 'confirmAddAgain'
                    }).appendTo("#changestat");
                    
                    $("<input>").attr({
                       type : 'hidden',
                       id : 'hdnblacklistID',
                       name : 'hdnblacklistID'
                    }).appendTo("#changestat");
                    
                    $("<input>").attr({
                       type : 'hidden',
                       id : 'remarks',
                       name : 'remarks'
                    }).appendTo("#changestat");
                    
                    $("#hdnblacklistID").val(<?php echo $hdBLId; ?>);
                    $("#remarks").val('<?php echo $remark; ?>');
                    $("#changestat").submit();
               },
               'No' : function(){
                    $(this).dialog('close');
               }    
           }
       });
       <?php else: ?>
       $("#confirmdialog").dialog({
           modal : true,
           title : 'CONFIRM',
           autoOpen: false,
           resizable : false,
           draggable :false,
           buttons : {
               'Yes' : function(){
                    $("#hdnprocess").val(3);
                    $("#changestat").submit();
               },
               'No' : function(){
                    $(this).dialog('close');
               }    
           }
       });
       <?php endif; ?>
    });
</script>
</form>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <h2>
                Player Blacklisting
            </h2>
            <br/>
            <div id="divlist">
                <table border="1" id="blacklist"></table>
                <div id="pager"></div>
            </div>
            <div id="addblacklist">
                <?php echo $btnAdd; ?>
            </div>    
        </div>
    </div>
</div>
<!--error dialog-->
<div id="dialogmsg">
    <p id="msg">
        <?php 
            if (isset($msg))
            echo $msg; 
        ?>
    </p>
</div>
<div id="confirmdialog">
    <p id="ask">
        <?php 
            if (isset($ask))
                echo $ask; 
        ?>
    </p>
    <form action = "" method="post" id="changestat">
    </form>
</div>    
<div id="blacklisthist">
    <h3 id="name"></h3><br />
    <table border="1" id="tblblacklistedhist"></table>
    <div id="pagerhist"></div>
</div>  
<form action ="" id="submit-addblacklist" method="post" >
    <div id="dlgaddblacklist">
        <table id="tblblacklist" style="margin-top: 50px;">
            <tr>
                <td>Last Name</td>
                <td><?php echo $txtLastname; ?></td>
            </tr> 
            <tr>
                <td>First Name</td>
                <td><?php echo $txtFirstName; ?></td>
            </tr>
            <tr>
                <td>Birth Date</td>
                <td>
                    <script type="text/javascript">
                        $(function() {
                            var date = new Date();                            
                            $( "#dtBirthDate" ).datepicker({
                                showOn: "button",
                                buttonImage: "images/calendar.gif",
                                buttonImageOnly: true,
                                dateFormat: "yy-mm-dd",
                                changeMonth: true,
                                changeYear: true,
                                closeText: "Close",
                                showButtonPanel: true,
                                minDate: new Date(1913, 10, 13),
                                maxDate: new Date(date.getUTCFullYear() - 21, date.getUTCMonth(), date.getUTCDate()),
                                yearRange: 'c-100:c'
                            });
                        });
                    </script>    
                    <input type="text" name="dtBirthDate" id="dtBirthDate" readonly="true"  onfocus="this.blur();" size="15" style="height:20px; cursor: pointer;" />
                </td>
            </tr>
            <tr>
                <td>Remarks</td>
                <td><?php echo $txtRemarks; ?></td>
            </tr>
        </table>   
    </div>
    <?php echo $hdnProcess; ?>
    <?php echo $hdnBLId; ?>
</form>
<?php include("footer.php"); ?>

<?php
/**
 * Format names
 * @example Inputted lastname is 'dEla crUz' the ouput would be Dela Cruz
 * @param type $str Name
 * @return string reformatted name
 * @author Mark Kenneth Esguerra
 * @date November 12, 2013
 */
function formatName($str)
{
    $arrNames = explode(" ", $str);
    if (count($arrNames) > 1)
    {
        $name = "";
        foreach($arrNames as $names)
        {
           $n = trim(ucfirst(strtolower($names)));
           $name .= $n." ";
        }
        return trim($name);
    }
    else
    {
        $name = trim(ucfirst(strtolower($str)));
        return trim($name);
    }
}
function validateNames($name)
{
    $result = (preg_match('/^[a-zA-Z\s]/i',$name));
    if ($result)
    {
        return true;
    }
    else
    {
        return false;
    }
}
?>
