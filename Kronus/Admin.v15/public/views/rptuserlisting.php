<?php
$pagetitle = "User Listing";
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
?>
<div id ="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" id="frmusers" action="#">
        <br />
        <table>
            <tr>
                <td width="130px">Site / PEGS</td>
                <td>
                <?php
                    $vsite = $_SESSION['siteids'];
                    echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                    echo "<option value=\"0\">All</option>";

                    foreach ($vsite as $result)
                    {
                         $vsiteID = $result['SiteID'];
                         $vorigcode = $result['SiteCode'];

                         //search if the sitecode was found on the terminalcode
                         if(strstr($vorigcode, $terminalcode) == false)
                         {
                            $vcode = $vorigcode;
                         }
                         else
                         {
                           //removes the "icsa-"
                           $vcode = substr($vorigcode, strlen($terminalcode));
                         }
                         //removes Site HEad Office
                         if($vsiteID <> 1)
                         {
                           echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                         }
                    }
                    echo "</select>";
                ?>
                     <label id="txtsitename"></label>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Query" id="btnquery"/>
        </div>
        
        <!-- jqgrid pagination -->
        <div align="center" id="pagination">
          <!-- for site listing -->
          <table border="1" id="users"></table>
          <div id="pager1"></div>
          <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;display:none;">
               <br />
               <input type="button" name="exportExcel" id="btnexcel" value="Export to Excel File"  style="float: right;margin-right: 10px;"/>
               <input type="button" name="exportPdf" id="btnpdf" value="Export to PDF File" style="float: right;margin-right: 10px;"/>
          </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessRptPegs.php';
        var siteID = jQuery("#cmbsite").val();
        //ajax call: loading of sites
        jQuery('#cmbsite').live('change', function()
        {
            jQuery("#senchaexport1").hide();
            jQuery("#users").GridUnload();
            jQuery.ajax({
                  url: url,
                  type: 'post',
                  data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                  dataType: 'json',
                  success: function(data){
                      if(jQuery("#cmbsite").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName+" / "+data.POSAccNo);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                      }
                  },
                  error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                  }
            }); 
       }); 
       
       jQuery("#btnquery").click(function(){
            jQuery("#users").GridUnload();
               jQuery("#senchaexport1").show();
               jQuery("#users").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                                rptpage: function(){return "UserListing"},
                                siteID: function(){return jQuery("#cmbsite").val();}
                              },
                    datatype: "json",
                    colNames:['POS Account','Site / PEGS Code','Site / PEGS Name','Name','User Group','Date Created','Status'],
                    colModel:[
                               {name:'POSAccountNo', index:'POSAccountNo', align:'center'},
                               {name:'SiteCode',index:'SiteCode', align: 'left'},
                               {name:'SiteName',index:'SiteName', align: 'left'},                           
                               {name:'Name', index:'Name', align:'left'},
                               {name:'UserGroup', index:'Name', align: 'left'},
                               {name:'DateCreated', index:'DateCreated', align:'left',  width:180},
                               {name:'Status',index:'Status', align: 'center'},
                             ],
                    rowNum:10,
                    rowList:[10,20,30],
                    height: 240,
                    width: 1200,
                    pager: '#pager1',
                    viewrecords: true,
                    sortorder: "asc",
                    caption: "User Details",
                    gridview: true
               });
               jQuery("#users").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
       });
       
       //event: onclick of export to excel button 
       jQuery("#btnexcel").click(function(){
           jQuery('#frmusers').attr('action', 'process/ProcessRptPegs.php?getpage=UserListing&fn=UserListing');
           jQuery('#frmusers').submit();
       });
       
       //event :  onclick of export to pdf button
       jQuery("#btnpdf").click(function(){
           jQuery("#frmusers").attr('action', 'process/ProcessRptPegs.php?getpage=UserListingPDF&cmbsite='+siteID);
           jQuery('#frmusers').submit();
       });
       
    });
</script>
<?php  
    }
}
include "footer.php"; ?>