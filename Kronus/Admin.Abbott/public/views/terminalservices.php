<?php  $pagetitle = "Update Terminal Provider Assignment";  ?>

<?php
//ini_set('display_errors',true);
//ini_set('log_errors',true);
include("../process/ProcessTerminalMgmt.php");
?>

<?php  include "header.php"; ?>

<div id="workarea">

    <script type="text/javascript">
        $(document).ready(function(){
            $('#cmbsitename').live('change', function(){
                sendSiteID1($(this).val());
                $('#cmbterminals').empty();
                $('#cmbterminals').append($("<option />").val("-1").text("Please Select"));
            });
                
            $('#cmbterminals').live('change', function(){
                $("#userdata tbody").html("");
                var terminal = document.getElementById('cmbterminals').value;
                var url = '../process/ProcessTerminalMgmt.php';
                var data = $('#frmservices').serialize();
                $.ajax({
                   url : url,
                   type : 'post',
                   data : data,
                   dataType : 'json',
                   success : function(data){
                           var tblRow = "<thead>"
                                        +"<tr>"
                                        +"<th colspan='3' class='header'>Terminal Provider Assignments</th>"
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>Services</th>"
                                        +"<th>Status</th>"
                                        +"<th>Action</th>"
                                        +"</tr>"
                                        +"</thead>";

                        $.each(data, function(i,user){
                            var serviceID = this.ServiceID;
                            var statusID = this.Status;
                            var vstatus;
                            if(statusID == 1)
                                {
                                    vstatus = "Active";
                                }
                            else
                              {
                                vstatus = "Inactive";  
                              }
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td>"+this.ServiceName+"</td>"
                                        +"<td align='center'>"+vstatus+"</td>"
                                        +"<td align='center'><input type=\"button\" value=\"Edit Status\" onclick=\"window.location.href='../process/ProcessTerminalMgmt.php?termid="+terminal+"&servicepage=ServiceUpdate&service="+serviceID+"'\"/></td>"
                                        +"</tr>"
                                        +"</tbody>";

                                        //$(tblRow).html($("#userdata tbody"));
                                        $('#results').show();
                                        //$('#userdata').children('tbody').html(tblRow);
                                        $('#userdata').html(tblRow);
                        });
                   },
                   error : function(e) {
                       alert(e.reponseText);
                   }
                });
            });
         });

    </script>
    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        
        <form method="post" id="frmservices">
            <input type="hidden" name="page" value="TerminalServices"/>
             <table>
                <tr>
                   <td width="130px">Site / PEGS Name</td>
                   <td>
                    <?php
                        $vsiteID = $_SESSION['siteids'];
                        echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                        echo "<option value=\"-1\">Please Select </option>";
                        foreach ($vsiteID as $result)
                        {
                          $vsiteID = $result['SiteID'];
                          $vname = $result['SiteName'];
                          if($vsiteID <> 1)
                          {
                            echo "<option value=\"".$vsiteID."\">".$vname."</option>";
                          }
                        }
                        echo "</select>";
                    ?>
                   </td>
               </tr>
               <tr>
                   <td>Terminal Name</td>
                   <td>
                       <select id="cmbterminals" name="cmbterminals">
                           <option value="-1">Please Select</option>
                       </select>
                   </td>
               </tr>
            </table>
        </form>
        <div id="results" style="display: none;">
            <table id="userdata" class="tablesorter">

            </table>
        </div>
</div>

    
<?php  include "footer.php"; ?>
