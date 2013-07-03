<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "List of Banned Accounts";
$currentpage = "Reports";

//$fproc = new FormsProcessor();
//$fproc->ProcessForms();

?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>
    
    $(document).ready(function(){
        
        function loadData(){
                getCardList('');
        }
        
        loadData();
        
        function getCardList(MemberCardID)
        {
            var url = "Helper/helper.carddetails.php";
            jQuery('#accountlist').GridUnload();
            jQuery("#accountlist").jqGrid({
                    url:url,
                    mtype: 'post',
                    postData: {
                                MemberCardID : function() {return MemberCardID}
                              },
                    datatype: "json",
                    colNames:['Card Number', 'Member Since', 'Action Date'],
                    colModel:[
                            {name: 'CardNumber', index: 'CardNumber', align: 'left'},
                            {name: 'DateCreated', index: 'DateCreated', align: 'left'},
                            {name: 'ActionDate', index: 'ActionDate', align: 'left'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 250,
                    width: 970,
                    pager: '#pager2',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"List Of Banned Accounts"
            });
            jQuery("#accountlist").jqGrid('navGrid','#pager2',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }
        
        function getAccountHistory(MemberCardID)
        {
            var url = "Helper/helper.carddetails.php";
            jQuery('#accounthistory').GridUnload();
            jQuery("#accounthistory").jqGrid({
                    url:url,
                    mtype: 'post',
                    postData: {
                                MemberCardID : function() {return MemberCardID}
                              },
                    datatype: "json",
                    colNames:['Account Status', 'Action Date', 'Remarks'],
                    colModel:[
                            {name: 'AccountStatus', index: 'AccountStatus', align: 'left'},
                            {name: 'ActionDate', index: 'ActionDate', align: 'left'},
                            {name: 'Remarks', index: 'Remarks', align: 'left'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 150,
                    width: 650,
                    pager: '#pager3',
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Account Status History"
            });
            jQuery("#accounthistory").jqGrid('navGrid','#pager3',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }
        
        $(".statuslink").live('click', function(){
            var membercardid = $(this).attr('MemberCardID');
            if(membercardid != ''){
                $("#CardNumber").html("<label>"+$(this).attr('CardNumber')+"</label>");
                $("#tblage").html("<label>"+$(this).attr('Age')+"</label>");
                $("#tblgender").html("<label>"+$(this).attr('Gender')+"</label>");
                $("#tblnationality").html("<label>"+$(this).attr('Nationality')+"</label>");
                getAccountHistory(membercardid);
                    if($("#viewrecords").dialog('isOpen') !== true){
                        $("#viewrecords").dialog({
                            modal: true,
                            width: 700,
                            height: 'auto',
                            position: 'center',
                            buttons: {
                                "Close": function(){
                                    $("#CardNumber").html("");
                                    $("#tblage").html("");
                                    $("#tblgender").html("");
                                    $("#tblnationality").html("");
                                    $(this).dialog('close');
                                }
                            },
                            title: 'Card Account History'
                        }).parent().appendTo($("#viewdatarecords"));
                    }
            }

        });

    });
</script>
<div align="center">
    </form>
    <form name="bannedaccountlists" id="bannedaccountlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                    <br><br>
                    <div align="center" id="pagination">
                        <table border="1" id="accountlist">

                        </table>
                        <div id="pager2"></div>
                        <span id="errorMessage"></span>
                    </div>
            </div>
        </div>
    </form>
    <form name="viewdatarecords" class="viewdatarecords" id="viewdatarecords">
        <div id="viewrecords" class="viewrecords" style="display:none;">
            <table id="card-table">
                <tr><th>Card Number</th></tr>
                <tr><td id="CardNumber"></td></tr>
            </table>
            <table id="accountprofile">
                <tr><th colspan="2">Player Profile</th></tr>
                <tr><td style="width: 200px;">Age</td><td id="tblage"></td></tr>
                <tr><td style="width: 200px;">Gender</td><td id="tblgender"></td></tr>
                <tr><td style="width: 200px;">Nationality</td><td id="tblnationality"></td></tr>
            </table>
            <br><br><br><br><br><br><br><br><br><br><br><br>
            <div align="center" id="pagination">
                <table border="1" id="accounthistory">

                </table>
                <div id="pager3"></div>
                <span id="errorMessage"></span>
            </div>
            <br>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>