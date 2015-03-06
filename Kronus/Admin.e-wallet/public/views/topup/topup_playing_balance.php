<?php 
$pagetitle = "Playing Balance Per Site";
include "header.php";
$vaccesspages = array('5','6', '18');
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
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <table>
            <tr>
                <td>Sites / PEGS</td>
                <td>
                    <select id="selsite" name="selsite">
                        <option value="-1">Select Site</option>
                        <option value="all">All</option>
                        <?php foreach($param['sites'] as $site): ?>
                            <?php if($site['SiteCode'] != 'SiteHO'): ?>
                                <option lblsite="<?php echo $site['SiteName']." / ".$site['POSAccountNo']; ?>" value="<?php echo $site['SiteCode'] ?>">
                                    <?php  ?>
                                <?php echo substr_replace($site['SiteCode'], '', 0, 5)  ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select> <label id="lblsitename"></label>
                </td>
            </tr>
            <tr>
                <td>Total no. of Active Session</td>
                <td>
                    <input type="text" id="activeSession" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
           <tr>
                <td>No. of Active Session (Terminal Based)</td>
                <td>
                    <input type="text" id="activeSessionter" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
           <tr>
                <td>No. of Active Session (User Based)</td>
                <td>
                    <input type="text" id="activeSessionub" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
        </table>
        <br />
        <div id="loading"></div>
                <div id="submitarea"> 
                    <input type="button" value="Submit" id="btnSubmit"/>
                </div>
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
             jQuery('#gridwrapper').hide();
            jQuery('#senchaexport1').hide();
            if(jQuery(this).val() == '-1') {
                jQuery('#lblsitename').html(jQuery('').children('option:selected').attr('label'));
                jQuery('#gridwrapper').hide();
                jQuery('#senchaexport1').hide();
                 document.getElementById('activeSession').value = '';
                 document.getElementById('activeSessionter').value = '';
                 document.getElementById('activeSessionub').value = '';
                return false;
            }
            else{
                jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('lblsite'));
                jQuery.ajax({
                          url: "process/ProcessTopUpPaginate.php?action=sessioncount",
                          type: 'POST',
                          data: {
                                    siteID: function(){return jQuery("#selsite").val();},
                                    ActiveSession : true,
                                    ActiveSessionAction : "sessioncount"
                                },
                          success: function(data){
                              $("#activeSession").val(data);
                              
                              jQuery.ajax({
                                            url: "process/ProcessTopUpPaginate.php?action=sessioncountter",
                                            type: 'POST',
                                            data: {
                                                      siteID: function(){return jQuery("#selsite").val();},
                                                      ActiveSession : true,
                                                      ActiveSessionAction : "sessioncountter"
                                                  },
                                            success: function(data){
                                                $("#activeSessionter").val(data);
                                                
                                                jQuery.ajax({
                                                            url: "process/ProcessTopUpPaginate.php?action=sessioncountub",
                                                            type: 'POST',
                                                            data: {
                                                                      siteID: function(){return jQuery("#selsite").val();},
                                                                      ActiveSession : true,
                                                                      ActiveSessionAction : "sessioncountub"
                                                                  },
                                                            success: function(data){
                                                                $("#activeSessionub").val(data);
                                                            },
                                                            error: function(XMLHttpRequest, e){
                                                              alert(XMLHttpRequest.responseText);
                                                              if(XMLHttpRequest.status == 401)
                                                              {
                                                                  window.location.reload();
                                                              }
                                                            }
                                                      }); 
                                            },
                                            error: function(XMLHttpRequest, e){
                                              alert(XMLHttpRequest.responseText);
                                              if(XMLHttpRequest.status == 401)
                                              {
                                                  window.location.reload();
                                              }
                                            }
                                      });
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
        });
        
        jQuery('#btnSubmit').click(function(){
            
           if(document.getElementById('selsite').value == "-1")
           {
               alert("Please select site");
               return false;
           }
           else
           {
            var sitecode = jQuery("#selsite").val();
            jQuery('#gridwrapper').show();
            jQuery('#senchaexport1').show();
           }
           jQuery('#playingbal').GridUnload();
           jQuery("#playingbal").jqGrid({
                            url:'process/ProcessTopUpPaginate.php?action=getactiveterminals',
                            mtype: 'post',
                            postData: {
                                    sitecode: function() {return $('#selsite').val(); }
                                      },
                            datatype: "json",
                            colNames:['Site / PEGS Code', 'Site / PEGS Name', 'Terminal Code', 'Playing Balance','Service Name', 'User Mode','Terminal Type','e-Wallet?'],
                            colModel:[
                                {name:'SiteCode',index:'SiteCode',align:'left'},
                                {name:'SiteName',index:'SiteName',align:'left'},
                                {name:'TerminalCode',index:'TerminalCode',align:'center'},
                                {name:'PlayingBalance',index:'PlayingBalance',align:'right',sortable:false},
                                {name:'ServiceName', index:'ServiceName', align:'center', sortable:false},
                                {name:'UserMode', index:'UserMode', align:'center', sortable:false},
                                {name:'TerminalType', index:'TerminalType', align:'center', sortable:false},
                                {name:'Ewallet', index:'Ewallet', align:'center', sortable:false},
                            ], 
                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1200,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Playing Balance"
                    });
                    jQuery("#playingbal").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                    $('#playingbal').trigger("reloadGrid");
        })
        
   
    });
</script>
<?php  
    }
}
include "footer.php"; ?>