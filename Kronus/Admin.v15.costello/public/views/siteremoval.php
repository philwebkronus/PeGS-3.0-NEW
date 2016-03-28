<?php

$pagetitle = "Removal of Assigned Site";  
include 'process/ProcessAccManagement.php';
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
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="process/ProcessAccManagement.php">
        <input type="hidden" name="page" value="RemoveAssignedSite" />
        <table>
            <tr>
                <td>Operator</td>
                <td>
                    <select id="cmboptr" name="cmboptr">
                        <option value="-1">Please Select</option>
                    </select>
                    <label id="displayStatus"></label>
                </td>
            </tr>
            <tr>
                <td>Site / Pegs</td>
                <td>
                    <select id="cmbsite" name="cmbsite">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" name="btnsubmit" value="Remove" onclick="return chksiteremove();" />
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
       var url = 'process/ProcessAccManagement.php';
       $.ajax({
         url : url,
         type : 'post',
         data : { page: function(){ return 'ViewActiveOperators';}},
         dataType : 'json',
         success : function(data){
             var acc = $("#cmboptr");
             $.each(data, function() {
                acc.append($("<option />").val(this.AID).text(this.UserName));                        
             });
         },
         error : function (XMLHttpRequest, e)
         {
           alert(XMLHttpRequest.responseText);
           if(XMLHttpRequest.status == 401)
            {
                window.location.reload();
            }
         }
       });
       
       $("#cmboptr").live('change', function(){
           var acc = $("#cmbsite");
           acc.empty();
           acc.append($("<option />").val("-1").text("Please Select"));    
           $.ajax({
              url : url,
              type : 'post',
              data : {page : function(){return 'OwnedSites'},
                      aid : function(){ return $("#cmboptr").find("option:selected").val()}
              },
              dataType : 'json',
              success : function(data){
                  $.each(data, function() {
                    acc.append($("<option />").val(this.SiteID).text(this.SiteCode));                        
                  });
              },
              error : function(XMLHttpRequest, e){
                  alert(XMLHttpRequest.responseText);
                  if(XMLHttpRequest.status == 401)
                  {
                     window.location.reload();
                  }
              }
           });
           
           if(document.getElementById('cmboptr').value != '-1'){
                    $('#displayStatus').empty();
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data:"sendOptID="+$(this).val(),
                        success: function(data){
                            jQuery('#displayStatus').text(data);
                        }
                    });
            } else {
                $('#displayStatus').empty();
            }
           
       });
    });
</script>
<?php  
        }
    }
include "footer.php"; 

?>

