<?php
$pagetitle = "Gross Hold per Provider";
include 'process/ProcessRptOptr.php';
include 'header.php';
$vaccesspages = array('2');
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
    $(document).ready(function()
    {
       $("#divgrosshold").hide();
       
       $("#btnquery").live('click', function()
       {
          var rptdate =  function() {return $("#rptDate").val();}
          var page = $("#sitepage").val();
          var tbl = "";
          $("#hdntransdate").val(rptdate);
          var d1 = new Date(rptdate);
          var d2 = new Date("<?php echo $deploymentDate; ?>");
          if (d1 < d2) 
          {
              alert("Invalid date.");
          }
          else 
          {
              grossholdGrid(rptdate, page);
          }
       });
    });
    
    //function for jqgrid
    function grossholdGrid(rptdate, page)
    {
       jQuery("#userdata").jqGrid(
       {    
           url:'process/ProcessRptOptr.php',
           mtype: 'post',
           postData: 
           {
               date : rptdate, 
               paginate : page
           },
           datatype: "json",
           colNames:['Site / PEGS Code', 'RTG', 'Habanero', 'e-Bingo', 'Total Gross Hold'],
           colModel:[
                     {name:'SiteCode', index:'SiteCode', align:'left', width: 200},
                     {name:'RTG', index:'RTG', align:'right', width: 200},
                     {name:'Habanero', index:'Habanero', align:'right', width: 200},
                     {name:'eBingo', index:'eBingo', align:'right', width: 200},
                     {name:'SubTotal',index:'SubTotal', align: 'right', width: 200}
                    ],
           rowNum:10,
           rowList:[10,20,30],
           height: 240,
           width: 1000,
           pager: '#pager1',
           refresh: true,
           viewrecords: true,
           sortorder: "asc",
           caption:"Gross Hold"
     });
     jQuery("#userdata").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false, refresh: true});
     jQuery('#userdata').trigger("reloadGrid");
     jQuery("#senchaexport2").show();
   }
</script>    
<style>
    #tblgrosshold1 thead th 
    {
        border-collapse: collapse;
        border: 1px solid #424242;
        background: #D5E5A3;
        padding: 5px;
        text-align: center
    }
    #tblgrosshold1 tr td 
    {
        border-collapse: collapse;
        border: 1px solid #424242;
    }
    #divgrosshold 
    {
        width: 1000px;
        float:left;
    }
</style>
<div id="workarea">
      <div id="pagetitle"><?php echo $pagetitle; ?></div>
      <br />
      <form>
        <input type="hidden" name="sitepage" id="sitepage" value="RptGrossHoldPerProvider" />
        <input type="hidden" name="rptdate" id="rptDateHdn" readonly value="<?php echo date('Y-m-d')?>"/>
        <span>Select Date: &nbsp;</span> <input name='strDate' id='rptDate' readonly value="<?php echo date("Y-m-d");?>" />
        <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('strDate', false, 'ymd', '-');"/>
        <div id="submitarea">
              <input type="button" value="Submit" id="btnquery"/>
        </div>
      </form>
      <div id="divgrosshold">
        <div style="float:left;width: 800px;">
            <table id="tblgrosshold1" style="border-collapse: collapse; border: 1px solid #000;width: 800px;">  
            </table>    
        </div>
      </div>
      <table border="1" id="userdata"></table>
      <div id="pager1">
            <div style="float:right;width:100px"></div>
      </div>
      <div id="senchaexport2" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1000px;">
        <br />
        <input type="hidden" id="hdntransdate" name="hdntransdate" />
        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
          onclick="window.location.href='process/ProcessRptOptr.php?pdf4=grossholdpdfperprovider&date='+$('#hdntransdate').val();" style="float: right;" />  
        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
          onclick="window.location.href='process/ProcessRptOptr.php?excel4=grossholdexcelperprovider&date='+$('#hdntransdate').val();" style="float: right;"/>
      </div>
</div>
<?php  
    }
}
include "footer.php"; ?>