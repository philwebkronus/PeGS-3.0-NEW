<?php
$pagetitle = "LP Deployment Created Terminals";
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
    jQuery(document).ready(function(){
       var url = 'process/ProcessAppSupport.php';
       jQuery.ajax({
           url : url,
           type : 'post',
           data : {page2 : function(){return 'GetTerminalsCreatedLogs';}},
           dataType : 'json',
           success : function(data)
           {
               var tblRow = "<thead><tr><th>Log Files</th></tr></thead>";
               jQuery.each(data, function(i, a){
                   tblRow += "<tbody><tr>\n\
                                   <td><a class='afiles' style='text-decoration: underline;cursor: pointer;'>"+a+"</a></td></tr></tbody>";
                   jQuery("#tblfiles").html(tblRow);
               })
           },
           error: function(XMLHttpRequest, e){
                alert(XMLHttpRequest.responseText);
                if(XMLHttpRequest.status == 401)
                {
                    window.location.reload();
                }
           }
        });
        
        jQuery(".afiles").live('click', function(){
           getlogcontents('ShowTerminalsCreatedLogs', jQuery(this).text());
        });
    });
    
    //get and display log contents
    function getlogcontents(page, file)
    {
        var url = 'process/ProcessAppSupport.php';
        jQuery.ajax({
               url: url,
               type: 'post',
               data: {page2 : function(){return page;},
                      logfile: function(){return file;}},
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
    <form method="post" action="#">
        <br /><br />
        <table id="tblfiles"  style="display: block; overflow: auto;height: 100px;width: 400px;">
            
        </table>
        <div id="logscontent" style="overflow: auto;height: 300px;">
            <table id="tblcontents" class="tablesorter" >
            
            </table>
        </div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>