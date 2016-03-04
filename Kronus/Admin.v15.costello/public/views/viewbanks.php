<?php
$pagetitle = "Banks";
//include "process/processbatchterminals.php";
include "header.php";
$vaccesspages = array('5');
    $vctr = 0;
    if(isset($_SESSION['acctype']))
    {
        foreach ($vaccesspages as $val)
        {
            if($_SESSION['acctype'] == $val)
            {
                break;
            }
            else
            {
                $vctr = $vctr + 1;
            }
        }

        if(count($vaccesspages) == $vctr)
        {
            echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
document.getElementById('blockf').style.display='block';</script>";
        }
        else
        {
?>
<script type="text/javascript">
    $(document).ready(function(){
       $("#btn-add-bank").live('click', function(){
           $("#fade").show();
           $("#light").show();
       });
       jQuery("#grid").jqGrid(
       {
            url:'process/ProcessBanks.php',
            mtype: 'post',
            postData: {
                    sitepage: function() { return $("#txtsitepage").val(); }  
            },
            datatype: "json",
            colNames:['Bank Code', 'Bank Name', 'Is Accredited', 'Status', 'Action'],
            colModel:[
                    {name:'BankCode', index:'BankCode', align:'left', width: 150},
                    {name:'BankName',index:'BankName', width:170, align: 'left'},
                    {name:'IsAccredited',index:'IsAccredited', width:140, align: 'center'},
                    {name:'Status', index:'Status', align:'center'},
                    {name:'Action',index:'Action', width:100, align: 'center'}
            ],

            rowNum:10,
            rowList:[10,20,30],
            height: 280,
            width: 1100,
            pager: '#pager2', 
            loadonce: true, 
            sortname: 'BankCode',
            viewrecords: true,
            sortorder: "asc",
            caption: "Banks"
       });
       $("#btnupdate").live("click", function(){
            var bankID = $(this).attr("BankID");
            $("#txtbankid").val(bankID);
            $("#frmsetupdate").submit();
        });
    });
</script>    
<div id="workarea">
<div id="pagetitle"><?php echo $pagetitle; ?></div>
<br />
    <br />
    <!--------GRID----------->
    <input type="hidden" name="page" id="txtsitepage" value="ViewBanks" />
    <table border="1" id="grid"></table>
    <div id="pager2"></div>
<br />
<div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div>

<form action="process/ProcessBanks.php" method="post" id="frmsetupdate">
    <input type="hidden" id="hdnredirect" name="sitepage" value="SetBankUpdate" />
    <input type="hidden" id="txtbankid" name="bankid" />
</form>    
<?php
    }
}
include "footer.php"; ?>
