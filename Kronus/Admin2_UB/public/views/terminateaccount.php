<?php
$pagetitle = "Terminate Account";
include 'process/ProcessAccManagement.php';
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
            $vaccdetails = null;
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
        var url = 'process/ProcessAccManagement.php';
        $('#cmbacc').live('change', function(){
            $("#userdata tbody").html("");
            var acc = document.getElementById('cmbacc').value;
            var accname = (jQuery(this).find("option:selected").text());
            
            if(acc > 0)
            {
                $('#pagination').hide();
                $.ajax({
                       url : url,
                       type : 'post',
                       data : {page: function(){return "AccountView";},
                               cmbacc: function(){return jQuery("#cmbacc").val();}
                              },
                       dataType : 'json',
                       success : function(data){
                               var tblRow = "<thead>"
                                            +"<tr>"
                                            +"<th colspan='6' class='header'>Accounts</th>"
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

                            $.each(data, function(i,user){
                                var rstatus = this.Status;
                                var rmessage;
                                switch(rstatus)
                                {
                                    case "0":
                                        rmessage = "Inactive";
                                    break;
                                    case "1":
                                        rmessage = "Active";
                                    break;
                                    case "2":
                                        rmessage = "Suspended";
                                    break;
                                    case "3":
                                        rmessage = "Locked(Attempts)";
                                    break;
                                    case "4":
                                        rmessage = "Locked(Admin)";
                                    break;
                                    case "5":
                                        rmessage = "Terminated";
                                    break;
                                    case "6":
                                        rmessage = "Password expired";
                                    break;
                                }

                                tblRow +=
                                            "<tbody>"
                                            +"<tr>"
                                            +"<td align='left'>"+this.UserName+"</td>"
                                            +"<td align='left'>"+this.Name+"</td>"
                                            +"<td align='left'>"+this.Email+"</td>"
                                            +"<td align='left'>"+this.Address+"</td>"
                                            +"<td align='center'>"+rmessage+"</td>"
                                            +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"TerminateAccount\" onclick=\"window.location.href='process/ProcessAccManagement.php?accname="+accname+"&accid="+acc+"'+'&terminate='+'TerminateAccount';\"/></td>"
                                            +"</tr>"
                                            +"</tbody>";

                                            $('#results').show();
                                            $('#details').hide();
                                            $('#userdata').html(tblRow);
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
            else{
                    $('#pagination').show();
                    $('#results').hide();
                }
        });

        $('#cmbacctype').live('change', function(){
           $('#useraccs').trigger("reloadGrid");
           jQuery("#useraccs").jqGrid(
           {
                 url: url,
                 mtype: 'post',
                 postData: {
                                TerminatePage: function() {return $("#accpage").val();},
                                cmbacctype: function() {return $("#cmbacctype").val();}
                           },
                 datatype: "json",
                 colNames:['User Name','Name', 'Email', 'Address', 'Status', 'Action'],
                 colModel:[
                            {name:'UserName',index:'UserName', width:150, align: 'left'},
                            {name:'Name',index:'Name', width:200, align: 'left'},
                            {name:'Email',index:'Email', width:250, align: 'left'},
                            {name:'Address',index:'Address', width:250, align: 'left'},
                            {name:'Status',index:'Status', width:70, align: 'center'},
                            {name:'button', index: 'button', width:150, align: 'center'}
                          ],

                 rowNum:10,
                 rowList:[10,20,30],
                 height: 280,
                 width: 1200,
                 pager: '#pager2',
                 viewrecords: true,
                 sortorder: "asc",
                 caption:"Accounts"
           });
           jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false}); 
           sendAccID($(this).val()); //this will populates the comboxes w/ accounts
           $('#cmbacc').empty();
           $('#cmbacc').append($("<option />").val("-1").text("All"));
       });
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <br />
    <table>
        <tr>
            <td width="130px">Account Type</td>
            <td>
                <?php
                        $vacctype = $_SESSION['acctypes'];

                        echo "<select id=\"cmbacctype\" name=\"cmbacctype\">";
                        echo "<option value=\"-1\">Please Select</option>";

                        foreach ($vacctype as $result)
                        {
                            $vaccID = $result['AccountTypeID'];
                            $vname = $result['Name'];
                            //if account type is pegs operator, select liason and site operator 
                            if($_SESSION['acctype'] == 8)
                            {
                                if($vaccID == 2)
                                {
                                  echo "<option value=\"".$vaccID."\">".$vname."</option>"; 
                                }
                            }
                            else
                            {
                                echo "<option value=\"".$vaccID."\">".$vname."</option>";                        
                            }
                        }
                        echo "</select>";
                ?>
            </td>
        </tr>
    </table>
        <form method="post" id="frmaccount" action="process/ProcessAccManagement.php">
            <input type="hidden" id="page" name="page" value="TerminateAccount" />
            <table>
              <tr>
                <td width="130px">Accounts</td>
                <td>
                  <select id="cmbacc" name="cmbacc">
                    <option value="-1">Please Select</option>
                  </select>
                </td>
              </tr>
            </table>
            <div id="light" class="white_confirm">
                <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
                <input type="hidden" id="txtaccid" name="txtaccid" value="<?php echo  $vaccdetails['AID']; ?>" />
                <input type="hidden" name="page" value='TerminateAccount' />
                <input type="hidden" name="optstatus" value="5" />
                <input type="hidden" name="txtoperator" id="txtoperator" value="<?php echo $vaccdetails['AccName']; ?>"/>
                <p>Are you sure you want to terminate this account?</p>
                <p align="center"><?php echo $vaccdetails['AccName']; ?></p>
                <input type="submit" value="OK" style="float: left;"/>
                <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
            </div>
            <div id="fade" class="black_overlay"></div>
        </form>
    <div align="center" id="pagination">
        <table border="1" id="useraccs"></table>
        <div id="pager2"></div>
    </div>
    <div id="results" style="display: none;">
        <table id="userdata" class="tablesorter"></table>
    </div>
</div>
<?php  
    }
}
include "footer.php"; ?>