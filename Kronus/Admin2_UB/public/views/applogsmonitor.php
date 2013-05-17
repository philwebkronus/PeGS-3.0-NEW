<?php
$pagetitle = "Logs Tracking";
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
           data : {page2 : function(){return 'GetLogFile';}},
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
           getlogcontents('ShowLogContent', jQuery(this).text());
        });
        
        jQuery("#btnsearch").click(function(){
           var date = jQuery("#popupDatepicker1").val().replace(/-/g,'_');
           var result = validatedate(jQuery("#popupDatepicker1").val());
           if (result == true)
           { 
               getlogcontents('ShowLogContent', date); 
           }
        });
        
        jQuery("#cmbpick").bind('change', function(){
           var pick = (jQuery(this).find("option:selected").val());
           if(pick == 2)
           {
               jQuery("#seldate").show();
               jQuery("#submitarea").show();
               jQuery("#tblfiles").hide();
               jQuery("#logscontent").hide();
           }
           if(pick == 1)
           {
               jQuery("#tblfiles").show();
               jQuery("#seldate").hide();
               jQuery("#logscontent").hide();
               jQuery("#submitarea").hide();
           }
           
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
        <label for="cmbpick">Please Select</label>
        <select id="cmbpick">
            <option value="1">All</option>
            <option value="2">Pick Date</option>
        </select>
        <div id="seldate" style="display: none;float: right;margin-right: 500px;">
            <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
            <img id="imgcal" name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
        </div>
        
        <div id="submitarea" style="display: none;">
            <input type="button" id="btnsearch" value="Search" />
        </div>
        <br /><br />
        <table id="tblfiles"  style="display: block; overflow: auto;height: 100px;width: 150px;">
            
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
