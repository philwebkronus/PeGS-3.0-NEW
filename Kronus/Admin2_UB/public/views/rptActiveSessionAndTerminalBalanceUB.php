<?php 
$pagetitle = "Active Session and Terminal Balance Per Membership Card";
include 'process/ProcessRptOptr.php';
include 'header.php';

$vaccesspages = array('2','3');
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
    
    jQuery(document).ready(function()
    {  
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
         
       //on click function for 2nd Submit button     
       jQuery('#btnsubmit').click(function(){
       
                   $('#activesessionnumber').show();
                   unloadDataGrid();
                   
                   var url = "process/ProcessRptOptr.php";
                   
                   jQuery("#userdata").jqGrid({
                        url : url,
                        datatype : "json",
                        mtype : "post",
                        postData : {
                            txtcardnumber: jQuery("#txtcardnumber").val(),
                            siteID: '',
                            ActiveSession : true,
                            ActiveSessionAction : "sessionrecordub"
                        },
                        colNames : ["Terminal Code", "Playing Balance","User Mode"],
                        colModel : [
                           
                            {name:'TerminalCode',index:'TerminalCode', width: 300, sortable:false},
                            {name:'PlayingBalance',index:'PlayingBalance', width: 400, align: 'right', sortable:false},
                            {name:'UserMode',index:'UserMode', width: 400, align: 'center', sortable:false}
                        ],
                        rowNum : 10,
                        rowList:[10,20,30], 
                        pager: '#pager',
                        loadonce : true,
                        width: 800,
                        height: 230,
                        caption : "Active Session and Terminal Balance Per Membership Card",
                        viewrecords: true
                   });
       });
       
       //on click function for 1st Submit button
       jQuery('#btnOK').click(function(){
                $('#activesessionnumber').hide();
               if(document.getElementById('txtcardnumber').value == "")
               {
                    document.getElementById('activeSession').value = '';
                    document.getElementById('activeSessionter').value = '';
                    document.getElementById('activeSessionub').value = '';   
                    alert("Please Enter Membership Card Number");
                    unloadDataGrid();
                   return false;
               }
               else
               {
                showCardInfoTable();
                unloadDataGrid();
                
                var url = 'process/ProcessRptOptr.php';
                
                jQuery.ajax({
                          url: url,
                          type: 'POST',
                          data: {
                                    txtcardnumber: function(){return jQuery("#txtcardnumber").val();},
                                    siteID: '',
                                    ActiveSession : true,
                                    ActiveSessionAction : "sessioncount1"
                                },
                          success: function(data){
                              $("#activeSession").val(data);
                              
                              jQuery.ajax({
                                            url: url,
                                            type: 'POST',
                                            data: {
                                                      txtcardnumber: function(){return jQuery("#txtcardnumber").val();},
                                                      siteID: '',
                                                      ActiveSession : true,
                                                      ActiveSessionAction : "sessioncountter1"
                                                  },
                                            success: function(data){
                                                $("#activeSessionter").val(data);
                                                
                                                jQuery.ajax({
                                                            url: url,
                                                            type: 'POST',
                                                            data: {
                                                                      txtcardnumber: function(){return jQuery("#txtcardnumber").val();},
                                                                      siteID: '',
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
               }
       });
       
      

       function unloadDataGrid() {

            try{

                jQuery("#userdata").jqGrid("GridUnload");

            }
            catch(err){  

            }

       }
       
   });
   
   function showCardInfoTable()
        {
            var url = "process/ProcessRptOptr.php";
                //for displaying site / pegs information
                jQuery.ajax(
                {
                  url: url,
                          type: 'post',
                          data: {
                              pageub: function(){ return "GetLoyaltyCard";},
                              txtcardnumber: function(){return jQuery("#txtcardnumber").val();}},
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
<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  <form method="post" action="#" class="frmmembership">
    <input type="hidden" name="paginate" id="paginate" value="DailySiteTransaction" />  
    <table> 
       
            <td>
                Card Number
                <input type="input" size="30" id="txtcardnumber" class="txtmembership" name="txtcardnumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
                <div for="txtcardnumber" align='center'>Membership | Temporary</div>
            </td>
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
    <div id="submitarea">
        <input type="button" value="Display Active Session" id="btnOK" />
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
                 <input type="button" value="Submit" id="btnsubmit" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
            <div id="fade" class="black_overlay"></div>
  </form>
  
  <!--jqgrid pagination on this part-->
  <div align="center">
    <table border="1" id="userdata"></table>
    <div id="pager"></div>
  </div>
</div>
<?php  
    }
}
include "footer.php"; ?>
