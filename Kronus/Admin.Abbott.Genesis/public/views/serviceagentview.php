<?php  
$pagetitle = "Update MG Agent";  
include "process/ProcessTerminalMgmt.php";
include "header.php";
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
    <script type="text/javascript">
        $(document).ready(function()
        {        
           var url = 'process/ProcessTerminalMgmt.php'; 
           jQuery("#cmbsitename").live('change', function(){
              var siteID = document.getElementById("cmbsitename").value;
              var data = $('#frmusername').serialize();
         
              //this is for displaying the MG AgentID, MG Username
              jQuery.ajax(
              {
                 url: url,
                 data: {page: function(){return "DisplayAgents";},
                        cmbsitename: function(){return siteID;}
                       },
                 type: 'post',
                 dataType: 'json',
                 success: function(data){
                    jQuery.each(data, function(index, user)
                    {  
                       jQuery("#txtusername").val(this.Username);
                    });
                 },
                 error: function(XMLHttpRequest, e){
                    alert(XMLHttpRequest.responseText);
                    jQuery("#txtusername").val(" ");
                    jQuery("#userdata").hide();
                    if(XMLHttpRequest.status == 401)
                    {
                        window.location.reload();
                    }
                 }
             });
            
             //this part is for displaying site name
             jQuery.ajax(
             {
                url: url,
                type: 'post',
                data: {cmbsitename: function(){return jQuery("#cmbsitename").val();}},
                dataType: 'json',
                success: function(data)
                {
                      if(jQuery("#cmbsitename").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName+" / ");
                        jQuery("#txtposaccno").text(data.POSAccNo);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                      }
                }
             }); 
            
             //this is for displaying the table information of MG Agent on every site  
             $("#userdata tbody").html("");
             $.ajax(
             {
                 url : url,
                 type : 'post',
                 data : {page: function(){return "ViewAgentAccount";},
                         cmbsitename: function(){return siteID;}
                        },
                 dataType : 'json',
                 success : function(data)
                 {
                     jQuery("#userdata").show();
                     var tblRow = "<thead>"
                                 +"<tr>"
                                 +"<th colspan='3' class='header'>Service Agents</th>"
                                 +"</tr>"
                                 +"<tr>"
                                 +"<th>Service Agent ID</th>"
                                 +"<th>Username</th>"
                                 +"<th>Action</th>"
                                 +"</tr>"
                                 +"</thead>";

                        $.each(data, function(i,user)
                        {
                             tblRow += "<tbody>"
                                    +"<tr>"
                                    +"<td>"+this.ServiceAgentID+"</td>"
                                    +"<td>"+this.Username+"</td>"
                                    +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Edit Agent\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?agentid="+this.ServiceAgentID+"&updagentpage=UpdateAgent'\"/></td>"
                                    +"</tr>"
                                    +"</tbody>";
                              $('#userdata').html(tblRow);
                        });
                   },
                   error : function(e) {
                      $("#userdata tbody").html("");
                   }
                });  
           });
        });
  </script>
<div id="workarea">
    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br/>
        <input type="hidden" name="paginate" id="paginate" value="ViewAgentAccount"/>
        <form method="post" id="frmusername" action="#">
             <input type="hidden" name="page" id="page" value="ViewAgentAccount"/>
             <table>
              <tr>
                   <td width="130px">Site / PEGS</td>
                   <td>
                    <?php
                        $vsiteID = $_SESSION['siteids'];
                        echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                        echo "<option value=\"-1\">Please Select </option>";
                        foreach ($vsiteID as $result)
                        {
                               $rsiteID = $result['SiteID'];
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
                               if($rsiteID <> 1)
                               {
                                 echo "<option value=\"".$rsiteID."\">".$vcode."</option>";
                               }
                        }
                        echo "</select>";
                    ?>
                       <label id="txtsitename"></label><label id="txtposaccno"></label>
                   </td>
                </tr> 
                <tr>
                   <td width="130px">Agent Name</td>
                   <td>
<!--                       <select name="cmbusername" id="cmbusername">
                           <option value="-1">Please Select</option>
                       </select>-->
                       <input type="text" readonly="readonly" id="txtusername" name="txtusername" />
                   </td>
                </tr>
             </table>
        </form>

        <div id="results">
              <table id="userdata" class="tablesorter">

              </table>
        </div>
</div>
<?php  
    }
}
include "footer.php"; ?>
