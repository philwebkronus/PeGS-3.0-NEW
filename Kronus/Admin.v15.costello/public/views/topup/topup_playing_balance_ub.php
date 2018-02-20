<?php 
$pagetitle = "Playing Balance Per Membership Card";
include "header.php";
$vaccesspages = array('5','6', '18');
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
    <form id="frmexport" method="post" class="frmmembership">
         <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <table>
            <tr>
            <td>
                Card Number
                <input type="input" size="30" id="txtcardnumber" class="txtmembership" name="txtcardnumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
                <div for="txtcardnumber" align='center'>Membership | Temporary</div>
            </td>
            </tr>
        </table>
        <table id="activesessionnumber"> 
            <tr>
                <td>Total no. of Active Session</td>
                <td>
                    <input type="text" id="activeSession" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
           <tr>
                <td>No. of Active Session (Terminal Based)</td>
                <td>
                    <input type="text" id="activeSessionter" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
           <tr>
                <td>No. of Active Session (User Based)</td>
                <td>
                    <input type="text" id="activeSessionub" value="" readOnly ="readOnly" style="width:50px;" />
                </td>
           </tr>
           
    </table>
        <br />
        <div id="loading"></div>
                <div id="submitarea"> 
                    <input type="button" value="Submit" id="btnSubmit"/>
                </div>
        
        <div align="center" id="pagination">
            <div id="gridwrapper" style="display: none">
                <table id="playingbal" >

                </table>
                <div id="pager2"></div>
            </div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px; display: none;">
            <br />
            <input type="button" value="Export to PDF File" id="btnpdf" style="float: right;"/>
            <input type="button" value="Export to Excel File" id="btnexcel" style="float: right;"/> 
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
                 <input type="button" value="Submit" id="btnOK" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
            <div id="fade" class="black_overlay"></div>
    </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        $('#activesessionnumber').hide();
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
        
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=playingbalpdfub');
            jQuery('#frmexport').submit();                 
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=playingbalexcelub');
            jQuery('#frmexport').submit();
        });
        
        
        jQuery('#btnOK').click(function(){
            var cardnumber = jQuery('#txtcardnumber').val();
            $('#activesessionnumber').show();
            jQuery('#gridwrapper').show();
            jQuery('#senchaexport1').show();
            jQuery('#playingbal').show();
            jQuery("#playingbal").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getactiveterminalsub&cardnumber="+cardnumber,
            page:1}).trigger("reloadGrid");  
        });
        
        
        jQuery('#btnSubmit').click(function(){
            $('#activesessionnumber').hide();
            if(document.getElementById('txtcardnumber').value == "")
           {
               alert("Please Enter Membership Card Number");
               document.getElementById('activeSession').value = '';
               document.getElementById('activeSessionter').value = '';
               document.getElementById('activeSessionub').value = '';
               jQuery('#gridwrapper').hide();
               jQuery('#senchaexport1').hide();
               return false;
           }
           else
           {
            jQuery('#gridwrapper').hide();
            jQuery('#senchaexport1').hide();
            
            jQuery.ajax({
                          url: "process/ProcessTopUpPaginate.php?action=sessioncount1",
                          type: 'POST',
                          data: {
                                    txtcardnumber: jQuery("#txtcardnumber").val(),
                                    ActiveSession : true,
                                    ActiveSessionAction : "sessioncount1"
                                },
                          success: function(data){
                              $("#activeSession").val(data);
                              
                              jQuery.ajax({
                                            url: "process/ProcessTopUpPaginate.php?action=sessioncountter1",
                                            type: 'POST',
                                            data: {
                                                      txtcardnumber: jQuery("#txtcardnumber").val(),
                                                      ActiveSession : true,
                                                      ActiveSessionAction : "sessioncountter1"
                                                  },
                                            success: function(data){
                                                $("#activeSessionter").val(data);
                                                
                                                jQuery.ajax({
                                                            url: "process/ProcessTopUpPaginate.php?action=sessioncountub1",
                                                            type: 'POST',
                                                            data: {
                                                                      txtcardnumber: jQuery("#txtcardnumber").val(),
                                                                      ActiveSession : true,
                                                                      ActiveSessionAction : "sessioncountub1"
                                                                  },
                                                            success: function(data){
                                                                $("#activeSessionub").val(data);
                                                            },
                                                            error: function(XMLHttpRequest, e){
                                                              alert(XMLHttpRequest.responseText);
                                                              if(XMLHttpRequest.status == 401)
                                                              {
                                                                  window.location.reload();
                                                              }
                                                            }
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
                          },
                          error: function(XMLHttpRequest, e){
                            alert(XMLHttpRequest.responseText);
                            if(XMLHttpRequest.status == 401)
                            {
                                window.location.reload();
                            }
                          }
                    });
                    
            showCardInfoTable();
           }
            
            
        });
        
        jQuery("#playingbal").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getplayingbalance',
            datatype: "json",
            colNames:['Site / PEGS Code', 'Site / PEGS Name', 'Terminal Code', 'Playing Balance','Service Name', 'User Mode', 'Terminal Type','e-SAFE?'],
            rowNum:10,
            height: 280,
            width: 1200,
            rowList:[10,20,30],
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption:"Playing Balance Per Membership Card",
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left',sortable:false},
                {name:'SiteName',index:'SiteName',align:'left',sortable:false},
                {name:'TerminalCode',index:'TerminalCode',align:'center',sortable:false},
                {name:'PlayingBalance',index:'PlayingBalance',align:'right',sortable:false},
                {name:'ServiceName', index:'ServiceName', align:'center', sortable:false},
                {name:'UserMode', index:'UserMode', align:'center', sortable:false},
                {name:'TerminalType', index:'TerminalType', align:'center', sortable:false},
                {name:'Ewallet', index:'Ewallet', align:'center', sortable:false}
            ],     
            resizable:true
        });
    });
    
    function showCardInfoTable()
        {
                //for displaying site / pegs information
                jQuery.ajax(
                {
                  url: "process/ProcessTopUpPaginate.php?action=getcardnumber",
                          type: 'post',
                          data: {cardnumber: function(){return jQuery("#txtcardnumber").val();}},
                          dataType: 'json',  
                   success: function(data)
                   {
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
                              if(this.StatusCode == 9){
                                  alert("Card is Banned")
                              }
                             
                              // EDITED CCT 02/20/2018 BEGIN
                              // If player has no user based account.
                              //if(this.CardNumber == null)
                              if (this.Casino != 'undefined')
                              {
                              //    alert("Invalid Card Number");
                              //    $('#light').hide();
                              //    $('#fade').hide();
                              //}
                              //else
                              //{
                                  if(this.MobileNumber == null){
                                      this.MobileNumber = '';
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
                               // EDITED CCT 02/20/2018 END
                          });
                       
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
</script>
<?php  
    }
}
include "footer.php"; ?>