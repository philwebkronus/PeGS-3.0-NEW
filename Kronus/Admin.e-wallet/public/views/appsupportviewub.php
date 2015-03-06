<?php 
$pagetitle = "UB Transaction Tracking";  
include "process/ProcessAppSupport.php";
include "header.php";
$vaccesspages = array('9','6','18');
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
        $(document).ready(function()
        {  
            
            $("#txtcardnumber").focus(function(){
                    $("#txtcardnumber").bind('paste', function(event) {
                        setTimeout(function(event) {
                            var data = $("#txtcardnumber").val();
                            if(!specialcharacter(data)){
                                $("#txtcardnumber").val("");
                                $("#txtcardnumber").focus();
                            }
                        }, 0);
                    });
            });
            
            //source combo box event
            $("#cmbsource").change(function() 
            {
                var source = document.getElementById('cmbsource').value;           
                document.getElementById("cmbstatus").selectedIndex = 0;
                document.getElementById("cmbtranstype").selectedIndex = 0;
                
                //source is Manual redemption
                if(source == "3") {
                    document.getElementById('cmbtranstype').disabled=true;
                    $("#cmbstatus option[value='3']").attr('disabled','disabled');
                    $("#cmbstatus option[value='4']").attr('disabled','disabled');                    
                } 
                //source is Cashier
                else if(source == "1"){
                    $("#cmbtranstype option[value='RD']").attr('disabled','disabled');
                    $("#cmbtranstype option[value='R']").attr('disabled',false);
                    $("#cmbstatus option[value='3']").attr('disabled',false);
                    $("#cmbstatus option[value='4']").attr('disabled',false);
                    document.getElementById('cmbtranstype').disabled=false;
                }
                //source is Launchpad
                else if(source == "2"){
                    $("#cmbtranstype option[value='R']").attr('disabled','disabled');
                    $("#cmbtranstype option[value='RD']").attr('disabled',false);
                    $("#cmbstatus option[value='3']").attr('disabled',false);
                    $("#cmbstatus option[value='4']").attr('disabled',false);
                    document.getElementById('cmbtranstype').disabled=false;
                }
                else if(source == "4"){
                    $("#cmbstatus").attr('disabled','disabled');
                    $("#cmbtranstype").attr('disabled','disabled');
                }
                else {
                    $("#cmbtranstype option[value='RD']").attr('disabled',false);
                    $("#cmbtranstype option[value='R']").attr('disabled',false);
                    $("#cmbstatus option[value='3']").attr('disabled',false);
                    $("#cmbstatus option[value='4']").attr('disabled',false);
                    document.getElementById('cmbtranstype').disabled=false;
                }
            });
              
            //submit button event to display loyalty card info
            jQuery("#btnconfirm").click(function()
            {
               
                 var cardnumber = jQuery("#txtcardnumber").val();
                 var transtype = jQuery("#cmbtranstype").val();
                 var status = jQuery("#cmbstatus").val();
                 var source = jQuery("#cmbsource").val();
                 var date = new Date();
                 var curr_date = date.getDate();
                 var curr_month = date.getMonth();
                 curr_month = curr_month + 1;
                 var curr_year = date.getFullYear();
                 var curr_hr = date.getHours();
                 var curr_min = date.getMinutes();
                 var curr_secs = date.getSeconds();
                    if(curr_month < 10)
                    {
                        curr_month = "0" + curr_month;
                        if(curr_date < 10)
                           curr_date = "0" + curr_date;
                    }
                 var datenow = curr_year + '-'+ curr_month + '-'+ curr_date + ' ' + curr_hr + ':' + curr_min + ':' + curr_secs;
                         
                if((cardnumber.length < 1))
                {
                 alert("Please Input Membership Card Number");
                }
                else if((status == -1) && source != '4')
                {
                 alert("Please Choose a Transaction Status");   
                }
                else if((source == -1))
                {
                 alert("Please Choose a Transaction Source");   
                }
                //if source is Manual Redemption (MR)
                else if((source != 3))
                {
                    if((transtype == -1) && source != '4')
                    {
                     alert("Please Choose a Transaction Type");   
                    }
                    else
                    {
                         if((datenow) < (document.getElementById('popupDatepicker2').value))
                         {
                           alert("Queried date must not be greater than today");
                           $('#pager2').hide();
                           return false;         
                         }
                         else
                         {
                            showCardInfoTable();  
                         }
                    }
                }
                else if((datenow) < (document.getElementById('popupDatepicker2').value))
                {
                   alert("Queried date must not be greater than today");
                   $('#pager2').hide();
                   return false;         
                }
                else
                {
                     showCardInfoTable(); 
                }
            });
                
                
                //submit button event to display jqgrid
            $('#btnSubmit').click(function()
            {      
                   showjqgrid();
            });
        });
        
        
        function showCardInfoTable()
        {
            var url = 'process/ProcessAppSupport.php';
            $('#results').hide();
            var date = "<?php echo date("Ymd");?>"
            var isValidDateTime = validateDateTime(date);
            if(isValidDateTime == true ) 
            {
                //for displaying site / pegs information
                jQuery.ajax(
                {
                   url: url,
                   type: 'post',
                   data: {page: function(){ return "GetLoyaltyCard2";},
                          txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                         },
                   dataType : 'json',     
                   success: function(data)
                   {
                       if(data == "8"){
                           alert("Migrated Temporary Card");
                           showjqgrid();
                       }
                       else{
                           var tblRow = "<thead>"
                                     +"<tr>"
                                     +"<th colspan='6' class='header'>Member Information </th>"
                                     +"</tr>"
                                     +"<tr>"
                                     +"<th>Member Name</th>"
                                     +"<th>Mobile No</th>"
                                     +"<th>Email Address</th>"
                                     +"<th>Birth Date</th>"
                                     +"<th>Casino</th>"
                                     +"<th>Login</th>"
                                     +"</tr>"
                                     +"</thead>";

                          $.each(data, function(i,user)
                          {
                               if(this.CardNumber == null)
                              {
                                  alert("Invalid Card Number");
                                  $('#light').hide();
                                  $('#fade').hide();
                              }
                              else
                              {
                                  if(this.MobileNumber == null){
                                      this.MobileNumber = '';
                                  }
                                  if(this.StatusCode == 9){
                                      alert("Card is Banned");
                                  }
                                 document.getElementById('light').style.display='block';
                                 document.getElementById('fade').style.display='block';
                             
                             
                                tblRow +=
                                         "<tbody>"
                                         +"<tr>"
                                         +"<td>"+this.UserName+"</td>"   
                                         +"<td>"+this.MobileNumber+"</td>"
                                         +"<td>"+this.Email+"</td>"
                                         +"<td>"+this.Birthdate+"</td>"
                                         +"<td>"+this.Casino+"</td>"
                                         +"<td>"+this.Login+"</td>"
                                         +"</tr>"
                                         +"</tbody>";
                                         $('#userdata2').html(tblRow);
                                         
                               }
                          });
                       }
                      
                       
                   },
                   error: function(XMLHttpRequest, e)
                   {
                         alert(XMLHttpRequest.responseText);
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                   }
                });
            }
        }
        
        function showjqgrid(){
            $('#results').show();
                   $('#userdata').GridUnload();
                   $('#userdata3').GridUnload();
                   $('#userdata4').GridUnload();
                    var source = jQuery("#cmbsource").val();
                    var date = new Date();
                    var curr_date = date.getDate();
                    var curr_month = date.getMonth();
                    curr_month = curr_month + 1;
                    var curr_year = date.getFullYear();
                        if(curr_month < 10)
                        {
                            curr_month = "0" + curr_month;
                            if(curr_date < 10)
                               curr_date = "0" + curr_date;
                        }
                    var datenow = curr_year + '-'+ curr_month + '-'+ curr_date;
                    var cutoff = curr_year + '-'+ curr_month + '-'+ (parseInt(curr_date) + 1);
                    $("#userdata tbody").html("");
                    $('#pager2').show();
                    
                    
                    
                    if(source == '1')
                    {
                        jQuery("#userdata").jqGrid({
                            url:'process/ProcessAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    cmbsource: function() { return $("#cmbsource").val(); },
                                    txtcardnumber: function() { return $("#txtcardnumber").val(); },
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); },
                                    cmbstatus: function(){return $("#cmbstatus").val();},
                                    cmbtranstype: function(){ return $("#cmbtranstype").val();},
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Site','Terminal', 'Service Name', 'Transaction Type', 'Service Ref. ID', 'Amount', 'Start Date', 'End Date','Status','Created by'],
                            colModel:[
                                    {name:'SiteCode',index:'SiteCode', align: 'center', width:150},
                                    {name:'TerminalCode',index:'TerminalCode',align: 'center', width:120},
                                    {name:'ServiceName',index:'ServiceName',align: 'center', width:120},
                                    {name:'TransactionType',index:'TransactionType', align: 'center', width:150},
                                    {name:'ServiceTransactionID',index:'ServiceTransactionID', align: 'center'},
                                    {name:'Amount',index:'Amount', align: 'center', width:100, align: 'right'},
                                    {name:'DateCreated',index:'DateCreated', align: 'right', width:210},
                                    {name:'DateEnded',index:'DateEnded', align: 'right', width:150},
                                    {name:'Status',index:'Status', align: 'center', width:150},
                                    {name:'UserName',index:'UserName', align: 'center', width:100}
                                    ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1100,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"User Based Transaction Tracking"
                        });
                        jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                        $('#userdata').trigger("reloadGrid");
                    }
                    else if(source == '2')
                    {
                    jQuery("#userdata3").jqGrid({
                            url:'process/ProcessAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    cmbsource: function() { return $("#cmbsource").val(); },
                                    txtcardnumber: function() { return $("#txtcardnumber").val(); },
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); },
                                    cmbstatus: function(){return $("#cmbstatus").val();},
                                    cmbtranstype: function(){ return $("#cmbtranstype").val();},
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Site','Terminal', 'Service Name', 'Transaction Type', 'Service Ref. ID', 'Amount', 'DateStarted','DateEnded','Status'],
                            colModel:[
                                    {name:'SiteID',index:'SiteID', align: 'center', width:150},
                                    {name:'TerminalID',index:'TerminalID',align: 'center', width:120},
                                    {name:'ServiceName',index:'ServiceName',align: 'center', width:120},
                                    {name:'TransactionType',index:'TransactionType', align: 'center', width:150},
                                    {name:'ServiceTransactionID',index:'ServiceTransactionID', align: 'center'},
                                    {name:'Amount',index:'Amount', align: 'right', width:100},
                                    {name:'DateStarted',index:'DateStarted', align: 'right', width:210},
                                    {name:'DateEnded',index:'DateEnded', align: 'right', width:150},
                                    {name:'Status',index:'Status', align: 'center', width:100}
                                    ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1100,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"User Based Transaction Tracking"
                        });
                        jQuery("#userdata3").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                        $('#userdata3').trigger("reloadGrid");
                    }
                    
                    else if(source == '3')
                    {
                    jQuery("#userdata4").jqGrid({
                            url:'process/ProcessAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    cmbsource: function() { return $("#cmbsource").val(); },
                                    txtcardnumber: function() { return $("#txtcardnumber").val(); },
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); },
                                    cmbstatus: function(){return $("#cmbstatus").val();},
                                    cmbtranstype: function(){ return $("#cmbtranstype").val();},
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Site','Terminal', 'Service Name','Service Ref. ID', 'Amount', 'Transaction Date','Status'],
                            colModel:[
                                    {name:'SiteCode',index:'SiteCode', align: 'center', width:150},
                                    {name:'TerminalCode',index:'TerminalCode',align: 'center', width:120},
                                    {name:'ServiceName',index:'ServiceName',align: 'center', width:120},
                                    {name:'TransactionID',index:'TransactionID', align: 'center'},
                                    {name:'ReportedAmount',index:'ReportedAmount', align: 'right', width:100},
                                    {name:'TransactionDate',index:'TransactionDate', align: 'right', width:210},
                                    {name:'Status',index:'Status', align: 'center', width:150}
                                    ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1100,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"User Based Transaction Tracking"
                        });
                        jQuery("#userdata4").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                        $('#userdata4').trigger("reloadGrid");
                    }
                    
                    else if(source == '4')
                    {
                        jQuery("#userdata4").jqGrid({
                            url:'process/ProcessAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    cmbsource: function() { return $("#cmbsource").val(); },
                                    txtcardnumber: function() { return $("#txtcardnumber").val(); },
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); },
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Site', 'Service Name','Starting Balance', 'Total Ewallet Loads', 'EndingBalance','StartDate','EndDate'],
                            colModel:[
                                    {name:'SiteCode',index:'SiteCode', align: 'center', width:150},
                                    {name:'ServiceName',index:'ServiceName',align: 'center', width:120},
                                    {name:'StartingBalance',index:'StartingBalance', align: 'center'},
                                    {name:'TotalEwalletReload',index:'TotalEwalletReload', align: 'right', width:100},
                                    {name:'EndingBalance',index:'EndingBalance', align: 'right', width:210},
                                    {name:'StartDate',index:'StartDate', align: 'center', width:150},
                                    {name:'EndDate',index:'EndDate', align: 'center', width:150}
                                    ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1100,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Ewallet Transaction Tracking"
                        });
                        jQuery("#userdata4").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                        $('#userdata4').trigger("reloadGrid");
                    }
                    
                    
                    function formatDate(date)
                    {
                        var m_names = new Array("January", "February", "March", 
                        "April", "May", "June", "July", "August", "September", 
                        "October", "November", "December");
                        var weekday = new Array("Sunday", "Monday", "Tuesday", "Wednesday", 
                        "Thurdsay", "Friday", "Saturday");

                        var d = new Date(date);
                        var curr_day = d.getDay();
                        var curr_date = d.getDate();
                        var curr_month = d.getMonth();
                        var curr_year = d.getFullYear();
                        var curr_hours = ('0'+d.getHours()).slice(-2);
                        var curr_minutes = ('0'+d.getMinutes()).slice(-2);
                        var curr_seconds = ('0'+d.getSeconds()).slice(-2);
                        return weekday[curr_day] + ", " + m_names[curr_month] + " " + curr_date + ", " + curr_year + " " + curr_hours + ":" + curr_minutes + ":" + curr_seconds;
                   
                    }
                    
                    $( "#info" ).empty().append( "<p align='left'>Transaction of "+ $("#txtcardnumber").val() +", "+formatDate($("#popupDatepicker1").val())+" to "+formatDate($("#popupDatepicker2").val()));
        }
    </script>
        <div id="pagetitle">UB Transaction Tracking</div>
        <br />
        <form method="post" action="" id="frmapps" name="frmapps" class="frmmembership">
            <input type="hidden" name="paginate" id="paginate" value="ViewSupportUB" />
            <table>
                <tr>
                <td>Card Number</td>
                <td>
                    <input type="text" size="30" class="txtmembership" id="txtcardnumber" name="txtcardnumber" value="" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);" />
                </td>
                <td>
                    Membership | Temporary
                </td>   
            </tr>
            <tr>
                <td>Source</td>
                <td>
                    <select id="cmbsource" name="cmbsource">
                        <option value="-1">Please Select</option>
                        <option value="1">Cashier</option>
                        <option value="2">Launchpad</option>
                        <option value="3">Manual Redemption</option>
                        <option value="4">Ewallet</option>
                    </select>
                </td>
                </tr>
                <tr>
                    <td>Transaction Status</td>
                    <td>
                        <select id="cmbstatus" name="cmbstatus">
                            <option value="-1">Please Select</option>
                            <option value="0">Pending</option>
                            <option value="1">Successful</option>
                            <option value="2">Failed</option>
                            <option value="3">Fulfillment Approved</option>
                            <option value="4">Fulfillment Denied</option>
                            <option value="All">All</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td>Transaction Type</td>
                    <td>
                        <select id="cmbtranstype" name="cmbtranstype">
                            <option value="-1">Please Select</option>
                            <option value="D">Deposit</option>
                            <option value="RD">Redeposit</option>
                            <option value="R">Reload</option>
                            <option value="W">Withdraw</option>
                            <option value="All">All</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                <td>Date Range</td>
                <td>
                From: 
                 <input name="txtDate1" id="popupDatepicker1" readonly value="<?php $thestime = date('Y-m-d H:i:s');;
$datetime_from = date("Y-m-d H:i:s",strtotime("-24 hours",strtotime($thestime)));
echo $datetime_from; ?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="javascript:NewCssCal('popupDatepicker1','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>
                <td>
                To:
                <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date('Y-m-d H:i:s'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="javascript:NewCssCal('popupDatepicker2','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>
            </tr>
            </table>
            
            
            <div id="submitarea"> 
                <input type="button" value="Submit" id="btnconfirm"/>
            </div>
            <div id="light" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <p align="center" style="font-weight: bold;"> Please verify if the following user information are correct </p>
            <input type="hidden" name="page" value="InsertPegsConfirmation2" />
            <input type="hidden" name="txtsitecode" id="txtsitecode" />
            <table id="userdata2" class="tablesorter" align="center">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr align="right">
                    <br />
                   
                   
                </tr>
            </table>
            <br />
            <div align="right">
                 <input type="button" value="Submit" id="btnSubmit" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
            <div id="fade" class="black_overlay"></div>
        </form>
     
           <div id="results" align="center">
             <table border="1" id="userdata">

             </table>
               <table border="1" id="userdata3">

             </table>
                <div id='info'></div>
             <table border="1" id="userdata4">

             </table>  
             <div id="pager2"></div>
          </div>
     
</div>
<?php  
    }
}
include "footer.php"; ?>
