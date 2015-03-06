<?php $pagetitle = "Update Profile";  ?>

<?php
include '../process/ProcessSiteGroup.php';
?>

<?php  include "header.php"; ?>

<div id="workarea">
    <script type="text/javascript">
        jQuery(document).ready(function(){

           jQuery("#useraccs").jqGrid({
              url:'../process/ProcessSiteGroup.php',
              mtype: 'post',
              postData: {
                          paginate: function() {return $("#paginate").val();},
                          cmbsitegrp: function() {return $("#cmbsitegrp").val();}
                        },
              datatype: "json",
              colNames:['Site / PEGS Group Name','Description', 'Action'],
              colModel:[
                          {name:'SiteGroupsName',index:'SiteGroupsName', width:150, align: 'left'},
                          {name:'Description',index:'Description', width:200, align: 'left'},
                          {name:'button', index: 'button', width:120, align: 'center'}
                       ],

              rowNum:10,
              rowList:[10,20,30],
              height: 280,
              width: 800,
              pager: '#pager2',
              sortname: 'SiteGroupsName',
              viewrecords: true,
              sortorder: "asc",
              caption:"Site / PEGS Groups"
           });
           jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
           jQuery('#useraccs').trigger("reloadGrid");
           
           jQuery('#cmbsitegrp').live('change', function(){
               var url = '../process/ProcessSiteGroup.php';
               var data = jQuery("#frmsitegrp").serialize();
                           var grpid = document.getElementById('cmbsitegrp').value;
               if(grpid > 0)
                   {
                     jQuery('#pagination').hide();
                     jQuery('#results').show();  
                   }
               else
                   {
                     jQuery('#pagination').show();
                     jQuery('#results').hide();   
                   }
             
               jQuery.ajax({
                 url:url,
                 type: 'post',
                 data: data,
                 dataType: 'json',
                 success: function(data){
                     var tblRow = "<thead>"
                                 +"<tr>"
                                 +"<th colspan='6' class='header'>Site Groups</th>"
                                 +"</tr>"
                                 +"<tr>"
                                 +"<th>Site / PEGS Groups Name</th>"
                                 +"<th>Description</th>"
                                 +"<th>Action</th>"
                                 +"</tr>"
                                 +"</thead>";

                            $.each(data, function(i,user){
                                
                                tblRow +=
                                            "<tbody>"
                                            +"<tr>"
                                            +"<td align='left'>"+this.SiteGroupsName+"</td>"
                                            +"<td align='left'>"+this.Description+"</td>"
                                            +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='../process/ProcessSiteGroup.php?grpid="+grpid+"&page=ViewGroups'\"/></td>"
                                            +"</tr>"
                                            +"</tbody>";
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
    <input type="hidden" name="paginate" id="paginate" value="Paginate"/>
        <form method="post" id="frmsitegrp">
            <input type="hidden" name="page" id="page" value="SiteGroupsView" />
            <table>
                <tr>
                    <td width="130px">Site / PEGS Groups</td>
                    <td>
                        <?php
                                $vsitegrp = $_SESSION['sitegrps'];
                                echo "<select id=\"cmbsitegrp\" name=\"cmbsitegrp\">";
                                echo "<option value=\"-1\">All</option>";
                                foreach ($vsitegrp as $result){
                                      $vgrpID = $result['SiteGroupID'];
                                      $vgrpname = $result['SiteGroupsName'];
                                      echo "<option value=\"".$vgrpID."\">".$vgrpname."</option>";                        
                                }
                                echo "</select>";
                        ?>
                    </td>
                </tr>
           </table>
        </form>

        <div align="center" id="pagination">
            <table border="1" id="useraccs">

            </table>
            <div id="pager2"></div>
        </div>

        <div id="results" style="display: none; width:800px; margin-left: 100px;">
<!--            <div class="ui-widget-header" style="width: 1210px; height: 20px; padding: 5px 0px 5px 0;  float:left;">Accounts</div>-->
              <!-- <p  style=" background-color: #00700A; width: 1210px; color: white;">Accounts</p>-->
              <table id="userdata" class="tablesorter">

              </table>
        </div>
        
</div>
    
<?php  include "footer.php"; ?>