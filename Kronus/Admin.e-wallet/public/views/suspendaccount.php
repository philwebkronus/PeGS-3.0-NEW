<?php
$pagetitle = "Freeze Accounts";
include 'process/ProcessSiteManagement.php';
include 'header.php';
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
            if(isset ($_SESSION['accdetails']))
            {
                $vaccdetails = $_SESSION['accdetails'];
                echo "<script type=\"text/javascript\">
                      jQuery(document).ready(function(){
                      document.getElementById('light').style.display='block';
                      document.getElementById('fade').style.display='block';
                      });
                      </script>";
                unset($_SESSION['accdetails']);
            }
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
       jQuery("#useraccs").jqGrid(
       {
            url:'process/ProcessSiteManagement.php',
            mtype: 'post',
            postData: {
                    sitepage: function() {return $("#sitepage").val();},
                    updatestatus: function(){return '2';}
                      },
            datatype: "json",
            colNames:['Site ID','Site / PEGS Name','Site / PEGS Code', 'POS Account', 'Site / PEGS Description', 'Site / PEGS Address', 'Status', 'Action'],
            colModel:[
                    {name:'SiteID', index:'SiteID', align:'left', hidden: true},
                    {name:'SiteName',index:'SiteName', width:170, align: 'left'},
                    {name:'SiteCode',index:'SiteCode', width:140, align: 'left'},
                    {name:'POS', index:'POS', align:'center'},
                    {name:'SiteDescription',index:'SiteDescription', width:230, align: 'left'},
                    {name:'SiteAddress',index:'SiteAddress', width:250, align: 'left'},
                    {name:'Status',index:'Status', width:75, align: 'center'},
                    {name:'button', index: 'button', width:120, align: 'center'}
            ],

            rowNum:10,
            rowList:[10,20,30],
            height: 280,
            width: 1200,
            pager: '#pager2',
            sortname: 'SiteID',
            viewrecords: true,
            sortorder: "asc",
            caption: "Freeze Accounts"
       });
       jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <br />
    <input type="hidden" name="sitepage" id="sitepage" value="Paginate"/>
    <div align="center" id="pagination">
        <table border="1" id="useraccs"></table>
        <div id="pager2"></div>
    </div>
</div>
<?php  
    }
}
include "footer.php"; ?>