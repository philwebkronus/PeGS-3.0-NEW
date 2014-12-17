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
<script type="text/javascript" src='jscripts/jquery-1.11.1.js'></script>
<script src="jscripts/jqgridtest/js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script src="jscripts/jqgridtest/js/jquery.jqGrid.src.js" type="text/javascript"></script>
<script src="jscripts/jqgridtest/js/jquery.jqGrid.min.js" type="text/javascript"></script>

<script type="text/javascript">
var jQuery_1 = jQuery.noConflict(true);
var mygrid="";
    jQuery(document).ready(function(){  
        
       var url = 'process/ProcessAppSupport.php';
       var linkIndex = 0;
       var ServerLog = "";
       

        jQuery("#allPick").hide();
        jQuery("#cmbserver").hide();
        jQuery("#cmbpick").hide();
       

        jQuery("#cmbsystem").change(function(){
            var sys = jQuery("#cmbsystem").val();
            jQuery("#allPick").html('<select id="allPick">'+
                                    '<option value="0" selected="selected">Select Logs</option>'+
                                    '</select>');
            if(sys!=100){
                ServerLog = sys;
                checkServers(ServerLog);
               
                jQuery("#cmbserver").show();
                jQuery("#allPick").hide();
                jQuery("#seldate").hide();
                jQuery("#cmbpick").hide();
                jQuery_1('#cmbpick').prop('selectedIndex',0);
                jQuery_1("#datagrid").jqGrid("clearGridData");
                jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
               
            }else{
                jQuery("#allPick").hide();
                jQuery("#cmbserver").hide();
                jQuery("#allPick").hide();
                jQuery("#cmbpick").hide();
                jQuery_1('#cmbpick').prop('selectedIndex',0);
                jQuery("#submitarea").hide();
                jQuery_1("#datagrid").jqGrid("clearGridData");
                jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
            }    
        });
        
        jQuery("#cmbserver").change(function(){
            
            var server = jQuery("#cmbserver").val();
            
            if(server!=100){
                linkIndex =  server;
                populateLogList(ServerLog,linkIndex);    
                jQuery("#cmbpick").show();
                jQuery("#allPick").show();
                jQuery("#submitarea").show();
            
                jQuery_1("#datagrid").jqGrid("clearGridData");
                jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
            }else{
                
                jQuery("#cmbpick").hide();
                jQuery("#allPick").hide(); 
                jQuery("#seldate").hide();
                jQuery("#submitarea").hide();
                jQuery_1('#cmbpick').prop('selectedIndex',0);
                jQuery_1("#datagrid").jqGrid("clearGridData");
                jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
            }
        });

        jQuery("#btnsearch").click(function(){
           var pick = $("#cmbpick").val(); 
           var date="";
           var result="";

           if(pick==1){
            date = $("#allPick").val();   
            result=true;
           }
           if(pick==2){
            date = jQuery("#popupDatepicker1").val().replace(/-/g,'_');
            result = validatedate(jQuery("#popupDatepicker1").val());
           }
           if (result == true)
           {    
                if(jQuery("#cmbserver").val()!=100){
                   getlogcontents('ShowLogContent',date,linkIndex,ServerLog);
               }else
               {
                   alert("Please Select a Server");
               }
                   
           }
        });
        
        jQuery("#cmbpick").bind('change', function(){
           var pick = (jQuery(this).find("option:selected").val());
           if(pick == 2)
           {
               jQuery("#seldate").show();
               jQuery("#submitarea").show();
               jQuery("#allPick").hide();
               jQuery("#logscontent").hide();
//               jQuery_1("#datagrid").jqGrid("clearGridData");
//               jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
           }
           if(pick == 1)
           {
               jQuery("#allPick").show();
               jQuery("#seldate").hide();
               jQuery("#logscontent").hide();
               jQuery("#submitarea").show();
//               jQuery_1("#datagrid").jqGrid("clearGridData");
//               jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
           }
           if(pick == 100){
               
               jQuery("#tblfiles").hide();
               jQuery("#seldate").hide();
               jQuery("#logscontent").hide();
               jQuery("#submitarea").hide();
               jQuery("#allPick").hide();
//               jQuery_1("#datagrid").jqGrid("clearGridData");
//               jQuery_1("#datagrid").jqGrid('setCaption',ServerLog);
            }
            
        }); 
    });
    
    //get and display log contents
    function getlogcontents(page,file,link,system)
    {   
        var url = 'process/ProcessAppSupport.php';
       
        var gridParam = { url: url, datatype: "json",postData:{page2:function(){return page;},
                                                               logfile:function(){return file;},
                                                               link:function() {return link;},
                                                               system:function() {return system;}}};
         
        $.post(url,{page2:page,logfile:file,link:link,system:system},function(data){
         
            try{
                if($.parseJSON(data)){
                    
                      mygrid = jQuery_1("#datagrid").jqGrid({
                        url:url,
                        datatype: "json",
                        postData:{page2:function(){return page;},
                                  logfile:function(){return file;},
                                  link:function() {return link;},
                                  system:function() {return system;}},
                        mtype: 'POST', 
                        loadonce:true,
                        rowNum:50,
                        rowList:[50,60,70,100],
                        rownumbers: false,
                        rownumWidth: 40,
                        viewrecords: true,
                        pager:'#navGrid',
                        sortname: '',
                        sortorder: "",
                        height: 400,
                        width:1200,
                        gridview: true,
                        caption:system,
                        colNames:['LOGS'],
                        colModel:[{name:'ERROR',index:'ERROR'}],
                        gridComplete: function(){
                          var recs = parseInt(jQuery_1("#datagrid").getGridParam("records"),10);  
                          if (isNaN(recs) || recs == 0) {
                                alert("No Data to Show");
                            } 
                        }
                    });
                    jQuery_1("#datagrid").jqGrid('navGrid','#navGrid',{edit:false,add:false,del:false});
                    mygrid.jqGrid('setCaption',system);
                    mygrid.jqGrid('setGridParam', gridParam).trigger("reloadGrid");
        
                       $("html, body").animate({ scrollTop: "500px" });
                    
                    
                }
            }catch(err){
                
                if(data!="")
                alert(data);
                else
                alert("Error in reading data");
                jQuery_1("#datagrid").jqGrid("clearGridData");
            }
    
        });
                                                           
      
//                    jQuery(window).scrollTop(jQuery('div#jq').position().top);
                   
                   
//                     
 }      
 function checkServers(system){
   var url = 'process/ProcessAppSupport.php';
   $("#cmbserver").html('<select id="cmbserver">'+
                        '<option value="100" selected="selected">Select Server</option>'+
                        '</select>');
         jQuery.ajax({
                    url : url,
                    type : 'post',
                    data : {page2 : function(){return 'CheckServers';},
                            file:function(){return system;}},
                    success: function(data)
                    {
                      
                      $("#cmbserver").append(data);
                      
                        
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
 function populateLogList(system,link){
     
       var url = 'process/ProcessAppSupport.php';
       jQuery.ajax({
                    url : url,
                    type : 'post',
                    data : {page2 : function(){return 'GetLogFile';},
                            link:function() {return link;},
                            system:function() {return system;}},
                        
                    dataType : 'json',
                    success : function(data)
                    {
                        var tblRow = "";
                        jQuery.each(data, function(i, a){
                            tblRow += "<option value="+a+">"+a+"</option>"
                            jQuery("#allPick").html(tblRow);
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
   }
        

</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="#">
     
        <table>
            <tr>
                <td>
                    <select id="cmbsystem">
                        <option value="100"  selected="selected">Select System</option>
                        <option value="Cashier">Kronus Cashier</option>
                        <option value="Membership" >Membership</option>
                        <option value="Admin" >Kronus Admin</option>
                        <option value="Rewards" >Rewards Management</option>
                        <option value="AMPAPI" >AMPAPI</option>
                        <option value="BTAMPAPI" >BTAMPAPI</option>
                        <option value="MPAPI" >MPAPI</option>
                        <option value="VMS" >VMS</option>
                        <option value="LaunchPad" >Launchpad</option>
                    </select>
                </td>
                <td>
                    <select id="cmbserver">
                        <option value="100" selected="selected">Select Server</option>
                    </select>
                </td>
                <td>&nbsp;</td>
                <td>
                    <select id="cmbpick">
                        <option value="1" selected="selected">All</option>
                        <option value="2">Pick Date</option>
                    </select>
                </td>
                <td>
                <div id="seldate" style="display: none;">
                    <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                    <img id="imgcal" name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </div>
                </td>
                <td>
                    <select id="allPick">
                        <option value="0" selected="selected">Select Logs</option>
                    </select>
                </td>
            </tr>
            
        </table>
        
        <div id="submitarea" style="display: none;">
            <input type="button" id="btnsearch" value="Search"/>
        </div>
        <br /><br />
<!--        <table id="tblfiles" style="display: block; overflow: auto;height: 100px;width: 150px;">    
        </table>
        <br><br>-->
        <div id='jq'>
             <table border="1" id="datagrid">

             </table>
             <div id="navGrid"></div>
        </div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
