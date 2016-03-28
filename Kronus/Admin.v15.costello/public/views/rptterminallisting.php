<?php
$pagetitle = "Terminal Listing";
include 'process/ProcessRptPegs.php';
include 'header.php';
$vaccesspages = array('8');
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
            if((!isset ($_SESSION['details'])))
            {
                echo "<script type='text/javascript'>window.location.href='rptsitelisting.php';</script>";
            }
            else
            {
                $vdetails = $_SESSION['details'];
                foreach($vdetails as $val)
                {
                    $vpage = $val['page'];
                    $vsiteID = $val['SiteID'];
                    $vsitecode = $val['SiteCode'];
                    $vpos = $val['POS'];
                }
            }
?>

<script type="text/javascript">
    jQuery(document).ready(function()
    { 
       var url = 'process/ProcessRptPegs.php';
       var siteID = jQuery("#siteID").val();
       
       //this part displays the site details
           jQuery("#terminals").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            rptpage: function(){return jQuery("#page").val();},
                            siteID: function() {return $("#siteID").val();}
                          },
                datatype: "json",
                colNames:['Terminal Code','Status', 'Service Name','Terminal Type', 'Owner'],
                colModel:[
                           {name:'TerminalCode',index:'tcode', align: 'left'},
                           {name:'Status',index:'tstat', align: 'left'},
                           {name:'ServiceName', index:'ServiceName', align: 'left'},
                           {name:'TerminalType', index:'TerminalType', align: 'left'},
                           {name: 'Owner', index:'Owner',align:'left', sortable:false}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 240,
                width: 1200,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Terminal Listing",
                gridview: true
           });
           jQuery("#terminals").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false}); 
           
           //onclick event : export to excel button
           jQuery("#exportexcel").click(function(){
                jQuery("#frmexport").attr('action', 'process/ProcessRptPegs.php?getpage=ListTerminals&siteid='+siteID+'&fn=TerminalListing');
                jQuery("#frmexport").submit();
           });
           
           //onclick event : export to pdf button
           jQuery("#exportpdf").click(function(){
                jQuery("#frmexport").attr('action', 'process/ProcessRptPegs.php?getpage=ListTerminalsPDF&siteid='+siteID);
                jQuery("#frmexport").submit();
           });
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form id="frmexport" method="post" action="#">
        <input type="hidden" id="page" value="<?php echo $vpage;?>" />
        <input type="hidden" id="siteID" name="siteID" value="<?php echo $vsiteID; ?>" />
        
        <b>Site Code</b>: <?php echo $vsitecode; ?>
        <br />
        <b>POS Account Number</b>: <?php echo $vpos; ?>
        <div align="center" id="pagination">
          <!-- for terminals listing -->
          <table border="1" id="terminals"></table>
          <div id="pager1"></div>
          <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
               <br />
               <input type="button" name="exportExcel" id="exportexcel" value="Export to Excel File" style="float: right;margin-right: 10px;"/>
               <input type="button" name="exportPdf" id="exportpdf" value="Export to PDF File" style="float: right;margin-right: 10px;"/>
          </div>
        </div>
    </form>

</div>

<?php  
    }
}
include "footer.php"; 
?>
