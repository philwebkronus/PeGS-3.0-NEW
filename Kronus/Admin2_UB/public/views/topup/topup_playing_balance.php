<?php 
$pagetitle = "Playing Balance";
include "header.php";
$vaccesspages = array('5','6');
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
<div id="workarea">
    <form id="frmexport" method="post" />
        <div id="pagetitle">Playing Balance</div>
        <br />
        <table>
            <tr>
                <td>Sites / PEGS</td>
                <td>
                    <select id="selsite" name="selsite">
                        <option value="">Select Site</option>
                        <option value="all">All</option>
                        <?php foreach($param['sites'] as $site): ?>
                            <?php if($site['SiteCode'] != 'SiteHO'): ?>
                                <option label="<?php echo $site['SiteName']." / ".$site['POSAccountNo']; ?>" value="<?php echo $site['SiteCode'] ?>">
                                    <?php  ?>
                                <?php echo substr_replace($site['SiteCode'], '', 0, 5)  ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select> <label id="lblsitename"></label>
                </td>
            </tr>
        </table>
        <br />
        <div align="center" id="pagination">
            <div id="gridwrapper" style="display: none">
                <table id="playingbal" >

                </table>
                <div id="pager2"></div>
            </div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px; display: none;">
            <br />
            <input type="button" value="Export to PDF File" id="btnpdf" style="float: right;"/>
            <input type="button" value="Export to Excel File" id="btnexcel" style="float: right;"/> 
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=playingbalpdf');
            jQuery('#frmexport').submit();                 
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=playingbalexcel');
            jQuery('#frmexport').submit();
        });
        
        jQuery('#selsite').change(function(){
            jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('label'));
            if(jQuery(this).val() == '') {
                jQuery('#gridwrapper').hide();
                jQuery('#senchaexport1').hide();
                return false;
            }
            var sitecode = jQuery(this).val();
            jQuery('#gridwrapper').show();
             jQuery('#senchaexport1').show();
            jQuery("#playingbal").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getactiveterminals&sitecode="+sitecode,
                page:1}).trigger("reloadGrid");  
        });
        
        jQuery("#playingbal").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getplayingbalance',
            datatype: "json",
            colNames:['Site / PEGS Code', 'Site / PEGS Name', 'Terminal Code', 'Playing Balance','Service Name'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption:"Playing Balance",
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'SiteName',index:'SiteName',align:'left'},
                {name:'TerminalCode',index:'TerminalCode',align:'center'},
                {name:'PlayingBalance',index:'PlayingBalance',align:'right',sortable:false},
                {name:'ServiceName', index:'ServiceName', align:'center', sortable:false},
            ],     
            resizable:true
        });        
    });
</script>
<?php  
    }
}
include "footer.php"; ?>