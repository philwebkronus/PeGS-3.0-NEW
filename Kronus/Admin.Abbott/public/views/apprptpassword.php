<?php
$pagetitle = "Change Terminal Password Logs";
include 'process/ProcessAppSupport.php';
include 'header.php';
$vaccesspages = array('9');
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
    $(document).ready(function(){
        $("#btnlogs").click(function(){
           getlogcontents('ShowTerminalPassword'); 
        });
    });
    
    //get and display log contents
    function getlogcontents(page)
    {
        var url = 'process/ProcessAppSupport.php';
        jQuery.ajax({
               url: url,
               type: 'post',
               data: {page2 : function(){return page;}},
               dataType: 'json',
               success : function(data)
               {
                   if(data.error)
                   {
                       alert(data.error);
                   }
                   else
                   {
                       var content;
                       var tblcont = "<thead>\n\
                                           <tr>\n\
                                            <th>Log Contents</th>\n\
                                           </tr></thead>";
                       if(data == "")
                       {
                           content = "No logs found";
                           tblcont += "<tbody><tr><td>"+content+"</td></tr></tbody>";
                       }
                       else
                       {
                           jQuery.each(data, function(str,content){
                               tblcont += "<tbody><tr><td>"+content+"</td></tr></tbody>"; 
                           });
                       }
                       jQuery("#logscontent").show();
                       jQuery("#tblcontents").html(tblcont);
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
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <div align="center">
        <input type="button" id="btnlogs" value="Show Logs" height="10" width="10" />
    </div>
    
    <div id="logscontent" style="overflow: auto;height: 300px;">
        <table id="tblcontents" class="tablesorter" style="text-align: left;">

        </table>
    </div>
</div>
<?php  
    }
}
include "footer.php"; ?>