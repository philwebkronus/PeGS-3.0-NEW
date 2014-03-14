<?php 
$pagetitle = "Update Profile";  
include 'process/ProcessAccManagement.php';
include "header.php";

$vaccesspages = array('1', '8' , '2');
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
        <script type="text/javascript">
        $(document).ready(function(){
            var url = 'process/ProcessAccManagement.php';
            $('#cmbacc').live('change', function(){
                $("#userdata tbody").html("");
                var acc = document.getElementById('cmbacc').value;
                if(acc > 0)
                    {
                        $('#pagination').hide();
                    }
                else{
                        $('#pagination').show();
                        $('#results').hide();
                    }
                var data = $('#frmaccount').serialize();
                $.ajax({
                   url : url,
                   type : 'post',
                   data : data,
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
                            if(rstatus == 1)
                                rmessage = "Active";
                            else
                                rmessage = "Inactive";
                            
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td align='left'>"+this.UserName+"</td>"
                                        +"<td align='left'>"+this.Name+"</td>"
                                        +"<td align='left'>"+this.Email+"</td>"
                                        +"<td align='left'>"+this.Address+"</td>"
                                        +"<td align='center'>"+rmessage+"</td>"
                                        +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessAccManagement.php?accid="+acc+"&page=ViewAccount'\"/></td>"
                                        +"</tr>"
                                        +"</tbody>";

                                        //$(tblRow).html($("#userdata tbody"));
                                        $('#results').show();
                                        $('#details').hide();
                                        //$('#userdata').children('tbody').html(tblRow);
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
            });

           $('#cmbacctype').live('change', function(){
                var acctype = document.getElementById('cmbacctype').value;
                if(acctype > 0)
                    {
                        $('#pagination').show();
                        $('#results').hide();
                    }
                else{
                        $('#pagination').hide();
                        $('#results').hide();
                    }
                jQuery("#useraccs").jqGrid({
                            url: url,
                            mtype: 'post',
                            postData: {
                                    accpage: function() {return $("#accpage").val();},
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
                                    {name:'button', index: 'button', width:120, align: 'center'}
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
                    $('#useraccs').trigger("reloadGrid");
                    sendAccID($(this).val()); //this will populates the comboxes w/ accounts
                    $('#cmbacc').empty();
                    $('#cmbacc').append($("<option />").val("-1").text("All"));
            });
         });
    </script>
    
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <input type="hidden" name="accpage" id="accpage" value="Paginate"/>
        <table>
                <tr>
                    <td width="130px">Account Type</td>
                    <td>
                        <?php
                                $vacctype = $_SESSION['acctypes'];
                                echo "<select id=\"cmbacctype\" name=\"cmbacctype\">";
                                echo "<option value=\"-1\">Please Select</option>";

                                foreach ($vacctype as $result){
                   
                                      $vaccID = $result['AccountTypeID'];
                                      $vname = $result['Name'];
                                      echo "<option value=\"".$vaccID."\">".$vname."</option>";                        
                                }
                                echo "</select>";
                        ?>
                    </td>
                </tr>
        </table>
        <form method="post" id="frmaccount" action="#">
            <input type="hidden" name="page" id="page" value="AccountView"/>
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
        </form>

        <div align="center" id="pagination">
            <table border="1" id="useraccs">

            </table>
            <div id="pager2"></div>
        </div>

        <div id="results" style="display: none;">
              <table id="userdata" class="tablesorter">

              </table>
        </div>
        
</div>
    
<?php  
    }
}
include "footer.php"; ?>
