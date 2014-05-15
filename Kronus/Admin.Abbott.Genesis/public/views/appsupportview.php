<?php 
$pagetitle = "Application Support";  
include "process/ProcessAppSupport.php";
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
                $('#cmbsite').live('change', function(){
                    jQuery("#txttermname").text(" ");
                    sendSiteID($(this).val());
                    $('#cmbterm').empty();
                    $('#cmbterm').append($("<option />").val("-1").text("Please Select"));
                    var url = 'process/ProcessAppSupport.php';
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

                    if(document.getElementById('cmbsite').value == "-1")
                    {
                        alert("Please select Site / PEGS");
                        document.getElementById('cmbsite').focus();
                        return false;
                    }
                    else if(document.getElementById('cmbterm').value == "-1")
                    {
                        alert("Please select terminal");
                        document.getElementById('cmbterm').focus();
                        return false;
                    }
                    
                    else if(document.getElementById('cmbstatus').value == "-1")
                    {
                        alert("Please select transaction status.");
                        document.getElementById('cmbstatus').focus();
                        return false;
                    }
                    else if(document.getElementById('cmbtranstype').value == "-1")
                    {
                        alert("Please select transaction type.");
                        document.getElementById('cmbtranstype').focus();
                        return false;
                    }
                    else if(document.getElementById('popupDatepicker1').value == "Date")
                    {
                        alert("Please choose date");
                        document.getElementById('popupDatepicker1').focus();
                        return false;
                    }
                    else if(document.getElementById('popupDatepicker2').value == "Date")
                    {
                        alert("Please choose date");
                        document.getElementById('popupDatepicker2').focus();
                        return false;
                    }  
                    
                    else if((document.getElementById('popupDatepicker2').value) < 
                        (document.getElementById('popupDatepicker1').value))
                    {
                      alert("Invalid date. Please check date range");
                      $('#pager2').hide();
                      return false;         
                    }
                    
                    else if((datenow) < 
                        (document.getElementById('popupDatepicker2').value))
                    {
                      alert("Queried date must not be greater than today");
                      $('#pager2').hide();
                      return false;         
                    }
                    
                    else{
                        $('#pager2').show();
                    }
                    
                    
                    jQuery("#userdata").jqGrid({
                            url:'process/ProcessAppSupport.php',
                            mtype: 'post',
                            postData: {
                                    cmbsite: function() {return $('#cmbsite').val(); },
                                    cmbterminal: function() { return $("#cmbterm").val(); },
                                    txtDate1: function() { return $("#popupDatepicker1").val(); },
                                    txtDate2: function() { return $("#popupDatepicker2").val(); },
                                    cmbstatus: function(){return $("#cmbstatus").val();},
                                    cmbtranstype: function(){ return $("#cmbtranstype").val();},
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Transaction Reference ID', 'Terminal Code', 'Transaction Type', 'Service Reference ID', 'Amount','Transaction Date','Status','Created By'],
                            colModel:[
                                    //{name:'LoyaltyCard',index:'LoyaltyCard', align: 'center', width:150},
                                    {name:'TransactionReferenceID',index:'TransactionReferenceID',align: 'left', width:210},
                                    {name:'TerminalID',index:'TerminalID', align: 'center', width:140},
                                    {name:'TransactionType',index:'TransactionType', align: 'left'},
                                    {name:'ServiceTransactionID',index:'ServiceTransactionID', align: 'left', width:100},
                                    {name:'Amount',index:'Amount', align: 'right', width:150},
                                    {name:'DateCreated',index:'DateCreated', align: 'center', width:210},
                                    {name:'Status',index:'Status', align: 'center', width:100},
                                    {name:'UserName', index:'UserName', align: 'center', width:150}
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
                            caption:"Transaction Tracking"
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
        <div id="pagetitle">Transaction Tracking</div>
        <br />
        <form method="post" action="" id="frmapps" name="frmapps">
            <input type="hidden" name="paginate" id="paginate" value="ViewSupport" />
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
                    <td>Terminals</td>
                    <td>
                        <select id="cmbterm" name="cmbterminal">
                            <option value="-1">Please Select</option>
                        </select>
                        <label id="txttermname"></label>
                    </td>
                </tr>
                <tr>
                    <td>Transaction Status</td>
                    <td>
                        <select id="cmbstatus" name="cmbstatus">
                            <option value="-1">Please Select</option>
<!--                            <option value="0">Pending</option>-->
                            <option value="1">Successful</option>
                            <option value="2">Failed</option>
<!--                            <option value="3">Timed Out</option>
                            <option value="4">Transaction Approved(Late)</option>
                            <option value="5">Transaction Denied(Late)</option>-->
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
     
           <div align="center">
             <table border="1" id="userdata">

             </table>
             <div id="pager2"></div>
          </div>
     
</div>
<?php  
    }
}
include "footer.php"; ?>
