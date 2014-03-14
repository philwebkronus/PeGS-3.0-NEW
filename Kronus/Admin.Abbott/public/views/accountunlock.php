<?php 

$pagetitle = "Unlock accounts";  
include 'process/ProcessAccManagement.php';
include "header.php";

$vaccesspages = array('1', '6' , '2', '8','9');
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
                                        +"<th colspan='6' class='header'>Unlock Accounts</th>"
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>Username</th>"
                                        +"<th>Name</th>"
                                        +"<th>Action</th>"
                                        +"</tr>"
                                        +"</thead>";

                        $.each(data, function(i,user){
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td align='left'>"+this.UserName+"</td>"
                                        +"<td align='left'>"+this.Name+"</td>"
                                        +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Unlock\" onclick=\"window.location.href='process/ProcessAccManagement.php?accid="+acc+"&unlockpage=AccountUnlock'\"/></td>"
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
                                    unlockpage: function() {return $("#accpage").val();},
                                    cmbacctype: function() {return $("#cmbacctype").val();}
                                      },
                            datatype: "json",
                            colNames:['Username','Name', 'Action'],
                            colModel:[
                                    {name:'UserName',index:'UserName', width:150, align: 'left'},
                                    {name:'Name',index:'Name', width:200, align: 'left'},
                                    {name:'button', index: 'button', width:120, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1200,
                            pager: '#pager2',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Unlock Accounts"
                    });
                    jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
                    $('#useraccs').trigger("reloadGrid");
                    
                    //this will populate accounts comboboxes
                    var data = jQuery(this).val();
                    jQuery.ajax({
                        url: url,
                        data: {accattempt: function(){ return data}},
                        type: 'post',
                        dataType: 'json',
                        success: function(data){
                            var acc = jQuery("#cmbacc");
                            jQuery.each(data, function(){
                                acc.append(jQuery("<option />").val(this.AID).text(this.UserName));
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
                    $('#cmbacc').empty();
                    $('#cmbacc').append($("<option />").val("-1").text("All"));
            });
         });

    </script>
    
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <input type="hidden" name="accpage" id="accpage" value="UnlockAccounts"/>
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
                                    //if account type is customer service then selection must be operator, cashier, supervisor
                                    if($_SESSION['acctype'] == 6)
                                    {
                                        //if($vaccID == 2 || $vaccID == 10) disabled 02-08-12
                                        if($vaccID == 2 || $vaccID == 3 || $vaccID == 4)
                                        {
                                          echo "<option value=\"".$vaccID."\">".$vname."</option>"; 
                                        }
                                    }
                                    //if account type is pegs then seletion must be pagcor, cashier, supervisor
                                    elseif($_SESSION['acctype'] == 8)
                                    {
                                        if($vaccID == 11 || $vaccID == 3 || $vaccID == 4)
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
