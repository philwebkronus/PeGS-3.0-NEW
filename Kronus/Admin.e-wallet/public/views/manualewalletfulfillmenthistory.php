<?php
/**
 * Manual ewallet Fulfillment History
 * @date January 29, 2015
 */
?>
<?php 
$pagetitle = "Application Support";  
include "process/ProcessEwalletAppSupport.php";
include "header.php";
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

<div id="workarea">
        <script type="text/javascript">
            $(document).ready(function(){
                
                $('#senchaexport1').hide();
                
                $('#cmbsite').live('change', function(){
                    jQuery("#txttermname").text(" ");
                    sendSiteID($(this).val());
                    $('#cmbterm').empty();
                    $('#cmbterm').append($("<option />").val("-1").text("Please Select"));
                    var url = 'process/ProcessEwalletAppSupport.php';
                    jQuery.ajax({
                          url: url,
                          type: 'post',
                          data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                          dataType: 'json',
                          success: function(data){
                              if(jQuery("#cmbsite").val() > 0)
                              {
                                jQuery("#txtsitename").text(data.SiteName);
                              }
                              else
                              {   
                                jQuery("#txtsitename").text(" ");
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
                });

                $('#cmbterm').live('change', function(){  
                    sendterminalname($(this).val()) ;                 
                });
                
                $('#btnSubmit').click(function(){      
                   $('#userdata').GridUnload();
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

                    
                    if(document.getElementById('txtCardNumber').value == "" 
                       && document.getElementById('cmbsite').value == "-1")
                    {
                        alert("Please select Site or Card Number.");
                        document.getElementById('txtCardNumber').focus();
                        return false;
                    }
                    else if(document.getElementById('cmbstatus').value == "-1")
                    {
                        alert("Please select transaction status.");
                        document.getElementById('cmbstatus').focus();
                        $("#senchaexport1").hide();
                        $("#pager2").hide();
                        return false;
                    }
                    else if(document.getElementById('popupDatepicker1').value == "Date")
                    {
                        alert("Please choose date");
                        document.getElementById('popupDatepicker1').focus();
                        $("#senchaexport1").hide();
                        $("#pager2").hide();
                        return false;
                    }
                    else if(document.getElementById('popupDatepicker2').value == "Date")
                    {
                        alert("Please choose date");
                        document.getElementById('popupDatepicker2').focus();
                        $("#senchaexport1").hide();
                        $("#pager2").hide();
                        return false;
                    }  
                    
                    else if((document.getElementById('popupDatepicker2').value) < 
                        (document.getElementById('popupDatepicker1').value))
                    {
                      alert("Invalid date. Please check date range");
                      $("#senchaexport1").hide();
                      $("#pager2").hide();
                      return false;         
                    }
                    
                    else if((datenow) < 
                        (document.getElementById('popupDatepicker2').value))
                    {
                      alert("Queried date must not be greater than today");
                      $("#senchaexport1").hide();
                      $("#pager2").hide();
                      return false;         
                    }
                    
                    else{
                        $("div.grid-label").empty();
                        $("div.grid-label").append("Transactions of " + $("#txtCardNumber").val() + ", " + formatDate($("#popupDatepicker1").val(), "from") + " 6:00:00 AM to " + formatDate($("#popupDatepicker2").val(), "to") + " 6:00:00 AM");
                        $('#pager2').show();
                        $('#senchaexport1').show();
                    }
                    
                    function formatDate(date, label){
                        var m_names = new Array("January", "February", "March", 
                                                "April", "May", "June", "July", "August", "September", 
                                                "October", "November", "December");
                        var weekday = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thurdsay", 
                                                "Friday", "Saturday");

                        var d = new Date(date);
                        var curr_day = d.getDay();
                        var curr_date = d.getDate();
                        if(label == "to"){
                            curr_date = d.getDate() + 1;
                        }
                        var curr_month = d.getMonth();
                        var curr_year = d.getFullYear();
                        return weekday[curr_day] + ", " + m_names[curr_month] + " " + curr_date + ", " + curr_year;
                    }
                    
                    
                    jQuery("#userdata").jqGrid({
                            url:'process/ProcessEwalletAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    txtcardnumber: function(){ return $("#txtCardNumber").val(); }, 
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); }, 
                                    cmbsite: function() { return $("#cmbsite").val(); }, 
                                    cmbstatus: function(){return $("#cmbstatus").val();},
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Site', 'Terminal', 'Transaction Type', 'Amount','Service Name', 'Transaction Date','Status','User Mode', 'Fulfilled By'],
                            colModel:[
                                    //{name:'LoyaltyCard',index:'LoyaltyCard', align: 'center', width:150},
                                    {name:'SiteCode',index:'SiteCode',align: 'center', width:120},
                                    {name:'TerminalID',index:'TerminalID', align: 'center', width:120},
                                    {name:'TransactionType',index:'TransactionType', align: 'left'},
                                    {name:'Amount',index:'Amount', align: 'right', width:120},
                                    {name:'ServiceName',index:'ServiceName', align: 'left', width:150},
                                    {name:'TransactionDate',index:'TransactionDate', align: 'center', width:180},
                                    {name:'Status',index:'Status', align: 'center', width:210},
                                    {name:'UserMode', index:'UserMode', align: 'center', width:150},
                                    {name:'CreatedByAID', index:'CreatedByAID', align: 'center', width:150}
//                                    {name:'button', index: 'button',align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 220,
                            width: 1100,
                            pager: '#pager2',
                            refresh: true,
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Manual e-Wallet Fulfillment History"
                    });
                    jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
                    $('#userdata').trigger("reloadGrid");
            });

//            $(function() {
//                $('#popupDatepicker1').datepick({dateFormat: 'yyyy-mm-dd'});
//                $('#popupDatepicker2').datepick({dateFormat: 'yyyy-mm-dd'});
//            });
        });
    </script>
        <div id="pagetitle">Manual e-Wallet Fulfillment History</div>
        <br />
        <form method="post" action="" id="frmapps" name="frmapps">
            <input type="hidden" name="paginate" id="paginate" value="MCFHistory" />
            <table>
                <tr>
                    <td width="130px">Site / PEGS</td>
                    <td>
                    <?php
                        $vsite = $_SESSION['siteids'];
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                        echo "<option value=\"-1\">Please Select</option>";

                        foreach ($vsite as $result)
                        {
                             $vsiteID = $result['SiteID'];
                             $vorigcode = $result['SiteCode'];
                             
                             //search if the sitecode was found on the terminalcode
                             if(strstr($vorigcode, $terminalcode) == false)
                             {
                                $vcode = $vorigcode;
                             }
                             else
                             {
                               //removes the "icsa-"
                               $vcode = substr($vorigcode, strlen($terminalcode));
                             }
                             //removes Site HEad Office
                             if($vsiteID <> 1)
                             {
                               echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                             }
                        }
                        echo "</select>";
                    ?>
                         <label id="txtsitename"></label>
                    </td>
                </tr>
                <tr>
                    <td width="130px">Card Number: </td>
                    <td>
                        <input type="text" name="txtCardNumber" id="txtCardNumber" />
                    </td>
                </tr>
                <tr>
                    <td>Transaction Status</td>
                    <td>
                        <select id="cmbstatus" name="cmbstatus">
                            <option value="-1">Please Select</option>
<!--                            <option value="0">Pending</option>-->
                            <option value="3">Fulfillment Approved</option>
                            <option value="4">Fulfillment Denied</option>
<!--                            <option value="3">Timed Out</option>
                            <option value="4">Transaction Approved(Late)</option>
                            <option value="5">Transaction Denied(Late)</option>-->
                            <option value="All">All</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Date Range</td>
                    <td>
                    From: 
                     <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                     <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                    </td>
                    <td>
                    To:
                    <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                    <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                    </td>
                </tr>
            </table>
            
            
            <div id="submitarea"> 
                <input type="button" value="Submit" id="btnSubmit"/>
            </div>
        </form>
           <div align="left" class="grid-label"></div>
           <br/>
           <div align="center">
             <table border="1" id="userdata">

             </table>
             <div id="pager2">
                 
             </div>
          </div>
          <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1100px;">
           <br />
           <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
                       onclick="window.location.href='process/ProcessRptAS.php?pdf=MCFHistory&DateFrom='+document.getElementById('popupDatepicker1').value+'&DateTo='+document.getElementById('popupDatepicker2').value+'&Site='+document.getElementById('cmbsite').value+'&CardNumber='+document.getElementById('txtCardNumber').value+'&Status='+document.getElementById('cmbstatus').value" style="float: right;" />  
           <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
                       onclick="window.location.href='process/ProcessRptAS.php?excel=MCFHistory&DateFrom='+document.getElementById('popupDatepicker1').value+'&DateTo='+document.getElementById('popupDatepicker2').value+'&Site='+document.getElementById('cmbsite').value+'&CardNumber='+document.getElementById('txtCardNumber').value+'&Status='+document.getElementById('cmbstatus').value+'&fn=MCFHistory_for_'+document.getElementById('popupDatepicker1').value+'_to_'+document.getElementById('popupDatepicker2').value" style="float: right;" />
          </div>  
            
     
</div>
<?php  
    }
}
include "footer.php"; ?>

