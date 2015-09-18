<?php
$pagetitle = "User Deactivation";
//include '../process/ProcessMenuMaintenance.php';
include 'menuheader.php';
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
    jQuery(document).ready(function()
    {
           var url = '../process/ProcessMenuMaintenance.php';
           loadaccounts(url);
           jQuery.ajax({
               url : url,
               type : 'post',
               data : {page : function(){return 'GetUserAccounts';}},
               dataType : 'json',
               success: function(data){
                   jQuery.each(data, function(){
                        var accounts = jQuery("#cmbacc");
                        accounts.append(jQuery("<option />").val(this.AID).text(this.UserName));
                   });
               },
               error : function(XMLHttpRequest, e)
               {
                   alert(XMLHttpRequest.responseText);
                   if(XMLHttpRequest.status == 401)
                   {
                       window.location.reload();
                   }
               }
           });
           
           jQuery('#cmbacc').live('change', function(){
                if(jQuery("#cmbacc").val() == "-1")
                {
                    loadaccounts(url);
                    jQuery('#results').hide();
                }
                else
                {
                    jQuery("#useraccs").GridUnload();
                    jQuery.ajax({
                        url : url,
                        type : 'post',
                        data : {page : function(){ return 'ViewUserAccount'; },
                                aid : function(){ return jQuery("#cmbacc").val();}
                               },
                        dataType : 'json',
                        success : function(data)
                        {
                            var tblRow = "<thead>"
                                        +"<tr>"
                                        +"<th colspan='6' class='header'>User Deactivation</th>"
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>User Name</th>"
                                        +"<th>Name</th>"
                                        +"<th>Email</th>"
                                        +"<th>Address</th>"
                                        +"<th>Status</th>"
                                        +"<th>Action</th>"
                                        +"</tr>"
                                        +"</thead>";
                            jQuery.each(data, function(){
                                var uname = this.UserName;
                                var aid = this.AID;
                                tblRow  +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td align='left'>"+this.UserName+"</td>"
                                        +"<td align='left'>"+this.Name+"</td>"
                                        +"<td align='left'>"+this.Email+"</td>"
                                        +"<td align='left'>"+this.Address+"</td>"
                                        +"<td align='center'>"+this.Status+"</td>"
                                        +"<td align='center' style='width: 50px;'><input type=\"button\" class=\"btnterminate\" value=\"Deactivate User\" onclick=\"window.location.href='../process/ProcessMenuMaintenance.php?uname="+uname+"&aid="+aid+"&getpage='+'DeactivateUser';\" /></td>"
                                        +"</tr>"
                                        +"</tbody>";

                                        $('#results').show();
                                        $('#details').hide();
                                        $('#userdata').html(tblRow);
                             });
                        },
                        error : function(XMLHttpRequest, e)
                        {
                            alert(XMLHttpRequest.responseText);
                            if(XMLHttpRequest.status)
                            {
                                window.location.reload();
                            }
                        }
                    });
                }
           });  
    });
    
    function loadaccounts(url)
    {
        jQuery("#useraccs").jqGrid(
           {
                 url: url,
                 mtype: 'post',
                 postData: {
                                paginate: function() {return 'UserDeactivation'}
                           },
                 datatype: "json",
                 colNames:['User Name','Name', 'Email', 'Address', 'Status', 'Action'],
                 colModel:[
                            {name:'UserName',index:'UserName', width:150, align: 'left'},
                            {name:'Name',index:'Name', width:200, align: 'left'},
                            {name:'Email',index:'Email', width:250, align: 'left'},
                            {name:'Address',index:'Address', width:250, align: 'left'},
                            {name:'Status',index:'Status', width:70, align: 'center'},
                            {name:'button', index: 'button', width:150, align: 'center',sortable :false}
                          ],

                 rowNum:10,
                 rowList:[10,20,30],
                 height: 280,
                 width: 1200,
                 pager: '#pager2',
                 viewrecords: true,
                 sortorder: "asc",
                 caption:"User Deactivation"
           });
           jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <br />
    <form method="post" id="frmaccount" action="../process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value='DeactivateUser' />
        <table>
              <tr>
                <td width="130px">Accounts</td>
                <td>
                  <select id="cmbacc" name="cmbacc">
                    <option value="-1">All</option>
                  </select>
                </td>
              </tr>
        </table>
        <div id="light" class="white_confirm">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <input type="hidden" id="txtaccid" name="txtaccid" value="<?php echo  $vaccdetails['AID']; ?>" />
            <input type="hidden" name="optstatus" value="4" />
            <input type="hidden" name="txtuname" id="txtuname" value="<?php echo $vaccdetails['Username']; ?>"/>
            <p>Are you sure you want to deactivate this account?</p>
            <p align="center"><?php echo $vaccdetails['Username']; ?></p>
            <input type="submit" value="OK" style="float: left;"/>
            <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
    <div align="center" id="pagination">
        <br />
        <table border="1" id="useraccs"></table>
        <div id="pager2"></div>
    </div>
    <div id="results" style="display: none;">
        <table id="userdata" class="tablesorter"></table>
    </div>
</div>
<?php  

include "menufooter.php"; ?>