<?php
include 'process/ProcessRptOptr.php';
$pagetitle = 'Standalone Terminal Monitoring';
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
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <form method="post" action="#">
        <table>
            <tr>
                <td>Site / PEGS </td>
                <td>
                  <?php
                       $vsite = $_SESSION['siteids'];
                       echo "<select id=\"cmbsite\" name=\"cmbsite\">";
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

                          if($_SESSION['acctype'] == 2)
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
            </tr>
        </table>
        <iframe id="monitor" width="1200" height="542" style="display: none;"></iframe>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
       var url = 'process/ProcessRptOptr.php';
       jQuery("#cmbsite").live('change', function(){
           
            //ajax: get site name and pos account no
            jQuery.ajax({
                  url: url,
                  type: 'post',
                  data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                  dataType: 'json',
                  success: function(data){
                      if(jQuery("#cmbsite").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName+" / ");
                        jQuery("#txtposaccno").text(data.POSAccNo);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
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
            
            if(jQuery("#cmbsite").val() > 0)
            {
                 jQuery.ajax({
                    url : url,
                    type : 'post',
                    data : {page2: function(){return 'StandaloneMonitoring'},
                            siteid: function(){return jQuery("#cmbsite").find("option:selected").val();},
                            sitecode: function(){return jQuery("#cmbsite").find("option:selected").text();}
                            },
                    success : function(data){
                        jQuery("#monitor").show();
                        jQuery("#monitor").attr('src', data);
                    }
                });
            }
            else
            {
                jQuery("#monitor").hide();
            }
       });
//       jQuery.ajax({
//           url: url,
//           type: 'post',
//           data: {page2: function(){return 'OwnedSites';}},
//           dataType: 'json',
//           success: function(data)
//           {
//               var siteID = jQuery("#cmbsites");
//               jQuery.each(data, function(){
//                   siteID.append(jQuery("<option />").val(this.SiteID).text(this.SiteCode));
//               });
//           },
//           error: function(XMLHttpRequest, e)
//           {
//               alert(XMLHttpRequest.statusText);
//               if(XMLHttpRequest.status == 401)
//               {
//                    window.location.reload();
//               }
//           }
//       }); 
    });
</script>
<?php  
    }
}
include "footer.php"; ?>