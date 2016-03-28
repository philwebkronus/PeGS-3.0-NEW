<?php 
$pagetitle = "Active Session and Terminal Balance Per Site";
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
    
    jQuery(document).ready(function()
    {  
       jQuery('#btnsubmit').click(function(){
       
               if(document.getElementById('cmbsite').value == "-1")
               {
                   alert("Please select site");
                   return false;
               }
               else
               {
                   
                   unloadDataGrid();
                   
                   var url = "process/ProcessRptOptr.php";
                   
                   jQuery("#userdata").jqGrid({
                        url : url,
                        datatype : "json",
                        mtype : "post",
                        postData : {
                            siteID: jQuery("#cmbsite").val(),
                            ActiveSession : true,
                            ActiveSessionAction : "pagcorsessionrecord"
                        },
                        colNames : ["TerminalType","Terminal Code", "Playing Balance","User Mode", "e-SAFE?"],
                        colModel : [
                             {name:'TerminalType',index:'TerminalType', width: 300, sortable:false},
                            {name:'TerminalCode',index:'TerminalCode', width: 300, sortable:false},
                            {name:'PlayingBalance',index:'PlayingBalance', width: 400, align: 'right', sortable:false},
                            {name:'UserMode',index:'UserMode', width: 400, align: 'center', sortable:false},
                            {name:'Ewallet',index:'Ewallet', width: 200, align: 'center', sortable:false}
                        ],
                        rowNum : 10,
                        rowList:[10,20,30], 
                        pager: '#pager',
                        loadonce : true,
                        width: 800,
                        height: 230,
                        caption : "Active Session and Terminal Balance Per Site",
                        viewrecords: true
                   });
                   
                   jQuery.ajax({
                          url: url,
                          type: 'POST',
                          data: {
                                    siteID: function(){return jQuery("#cmbsite").val();},
                                    ActiveSession : true,
                                    ActiveSessionAction : "sessioncount"
                                },
                          success: function(data){
                              $("#activeSession").val(data);
                              
                              jQuery.ajax({
                                            url: url,
                                            type: 'POST',
                                            data: {
                                                      siteID: function(){return jQuery("#cmbsite").val();},
                                                      ActiveSession : true,
                                                      ActiveSessionAction : "sessioncountter"
                                                  },
                                            success: function(data){
                                                $("#activeSessionter").val(data);
                                                
                                                jQuery.ajax({
                                                            url: url,
                                                            type: 'POST',
                                                            data: {
                                                                      siteID: function(){return jQuery("#cmbsite").val();},
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
       
       jQuery("#cmbsite").live('change', function(){

            unloadDataGrid();

            var url = 'process/ProcessRptOptr.php';
          
            if(document.getElementById('cmbsite').value != "-1"){
                
                    jQuery.ajax({
                          url: url,
                          type: 'POST',
                          data: {
                                    siteID: function(){return jQuery("#cmbsite").val();},
                                    ActiveSession : true,
                                    ActiveSessionAction : "sessioncount"
                                },
                          success: function(data){
                              $("#activeSession").val(data);
                              
                              jQuery.ajax({
                                            url: url,
                                            type: 'POST',
                                            data: {
                                                      siteID: function(){return jQuery("#cmbsite").val();},
                                                      ActiveSession : true,
                                                      ActiveSessionAction : "sessioncountter"
                                                  },
                                            success: function(data){
                                                $("#activeSessionter").val(data);
                                                
                                                jQuery.ajax({
                                                            url: url,
                                                            type: 'POST',
                                                            data: {
                                                                      siteID: function(){return jQuery("#cmbsite").val();},
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
            else {
                document.getElementById('activeSession').value = '';
                 document.getElementById('activeSessionter').value = '';
                 document.getElementById('activeSessionub').value = '';            
            }
       });

       function unloadDataGrid() {

            try{

                jQuery("#userdata").jqGrid("GridUnload");

            }
            catch(err){  

            }

       }
       
   });
   
</script>
<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  <form method="post" action="#">
    <input type="hidden" name="paginate" id="paginate" value="DailySiteTransaction" />  
    <table> 
        <tr>
          <td>Site / PEGS </td>
          <td>
              <?php
                   
                   array_key_exists("siteids", $_SESSION) ? $sitesList = $_SESSION['siteids'] : $siteList = array();
                   $vsite = $sitesList;
                   echo "<select id=\"cmbsite\" name=\"cmbsite\" style='min-width: 200px;'>";
                   echo "<option value=\"-1\">Please Select</option>";
                                
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
                         //remove the "icsa-"
                         $vcode = substr($vorigcode, strlen($terminalcode));
                      }
                                    
                      if($_SESSION['acctype'] == 2 || $_SESSION['acctype'] == 3)
                      {
                         $vsitesowned = $_SESSION['pegsowned'];
                                
                         foreach ($vsitesowned as $results)
                         {
                            $vownedsites = $results['SiteID'];
                            if( $vownedsites == $vsiteID)
                            {                                        
                               echo "<option value=\"".$vownedsites."\">".$vcode."</option>";
                            }
                         }
                      }
                   }
                   
                   echo "</select>";
               ?>
               <label id="txtsitename"></label><label id="txtposaccno"></label>
           </td>
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
        </tr>
    </table>
    <div id="submitarea">
        <input type="button" value="Display Active Session" id="btnsubmit" />
    </div>
  </form>
  
  <!--jqgrid pagination on this part-->
  <div align="center">
    <table border="1" id="userdata"></table>
    <div id="pager"></div>
  </div>
</div>
<?php  
    }
}
include "footer.php"; ?>
