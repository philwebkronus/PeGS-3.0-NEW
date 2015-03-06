<?php
$pagetitle = "BCF Per Site";
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
    jQuery(document).ready(function(){
       jQuery("#senchaexport1").show();   
       jQuery("#userdata").jqGrid(
       {    
           url:'process/ProcessRptOptr.php',
           mtype: 'post',
           postData: {
                        paginate: function() {return "BCFPerSite";}
                     },
           datatype: "json",
           colNames:['POS Account','Site / PEGS Code','Site / PEGS Name', 'Last Transaction Date', 'Top-up Type', 'Pick-up Tag','Minimum Balance','Maximum Balance','Balance'],
           colModel:[
                     {name: 'POS', index: 'POS', align: 'center'},
                     {name: 'SiteCode', index: 'SiteCode', align: 'center'},
                     {name:'SiteName',index:'SiteName',align: 'left'},
                     {name:'LastTransactionDate', index:'LastTransactionDate', align:'left'},
                     {name:'Top-up Type',index:'TopupType', align: 'center'},
                     {name:'Pick-up Tag',index:'PickupTag', align: 'center'},
                     {name:'MinimumBalance',index:'MinBalance', align: 'right'},
                     {name:'MaximumBalance',index:'MaxBalance', align: 'right'},
                     {name:'Balance',index:'Balance', align: 'right'}
                    ],
           rowNum:10,
           rowList:[10,20,30],
           height: 220,
           width: 1200,
           pager: '#pager2',
           refresh: true,
           viewrecords: true,
           sortorder: "asc",
           loadComplete: function (){gettotal();},
           caption: "BCF Per Site"
       });
       jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true}); 
    });
    
    //function for getting the sum of each transaction type
    function gettotal()
    {
        jQuery.ajax({
           url: 'process/ProcessRptOptr.php',
           data: {
                     gettotalbcf: function(){return "GetBCFTotals"; }
                 },
           type: 'post',
           dataType: 'json',
           success: function (data){
               var pagesum = data.summary;
               var grandsum = data.grandsum;

               document.getElementById('trans').style.display='block';
               //display summary per page
               document.getElementById('totpage').innerHTML = pagesum;
               document.getElementById('totgrand').innerHTML = grandsum;
           },
           error: function(XMLHttpRequest, e){
                alert(XMLHttpRequest.responseText);
                if(XMLHttpRequest.status == 401)
                {
                    window.location.reload();
                }
            }
        });
     }
</script>
<div id="workarea">
      <div id="pagetitle"><?php echo $pagetitle; ?></div>
      <br />
      <input type="hidden" name="rptDate" id="rptDate" readonly value="<?php echo date('Y-m-d')?>"/>
      <!--jqgrid pagination on this part-->
      <div align="center">
        <table border="1" id="userdata"></table>
        <div id="pager2" style="height: 150px;">
            <table id="trans" style="background-color:#D6EB99; padding-left: 910px; display: none; font-size: 14px; height: 40%; ">
                <tr>
                    <td>Summary per Page</td>
                    <td style="padding-left: 40px;"></td>
                    <td id="totpage" style="font-weight: bold;"></td>
                </tr>
                <tr>
                    <td>Grand Total</td>
                    <td style="padding-left: 40px;"></td>
                    <td id="totgrand" style="font-weight: bold;"></td>
                </tr>
            </table>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1200px;">
            <br />
            <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                   onclick="window.location.href='process/ProcessRptOptr.php?pdf1=bcfpersite&date='+document.getElementById('rptDate').value" style="float: right;" />  
            <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                   onclick="window.location.href='process/ProcessRptOptr.php?excel1=bcfpersite&date='+document.getElementById('rptDate').value+'&fn=BCF_Per_Site'" style="float: right;"/>
        </div>
      </div>
</div>
<?php  
    }
}
include "footer.php"; ?>