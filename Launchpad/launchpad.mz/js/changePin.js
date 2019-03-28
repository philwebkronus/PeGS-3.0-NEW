
$(document).ready(function(){
    
   var table="";
    
    
  //************************************************UI*********************************//
  
   $.createChangePin = function()
   {
       
       table+="<a class='boxclose'></a>"
               +"<center><div id='changePinModule'>"
                +"<table style='margin-top:-30px'>"
                    +"<tr>"
                        +"<td style='padding-top: 35px;'>"
                            +"<p style='color: white;'><b>Card Number</b></p>"
                            +"<input type='text' width="+confirmButtonsW+" id='changePINUB' readonly/>"
                        +"</td>"
                    +"</tr>"
                    +"<tr>"
                        +"<td style='padding-top: 15px;'>"
                            +"<p style='color: white;'><b>Current PIN</b></p>"
                            +"<input type='password' width="+confirmButtonsW+" id='changeUserPIN' readonly/>"
                        +"</td>" 
                    +"</tr>"
                    +"<tr>"
                        +"<td>"
                            +"<input type='button' style='margin-top:"+confirmButtonsMarginTop+"' id='confirmchangePIN' width="+confirmButtonsW+" value='Confirm'>"
                        +"</td>"
                    +"</tr>"
                +"</table>"
                +"<center><div id='buttcont2' style='display:none;'></div></center> "
            +"</div></center>";
                
                
          
          $.lytBox(table,changePinContW,changePinContH,true);
          $("#changeUserPIN").attr('disabled',true);
 
          
          table="";
   };
   
   
   $.createNewPinTable = function()
   {
       
       
       table+="<a class='boxclose'></a>"
               +"<center><div id='changePinModule'>"
                +"<table style='margin-top:-30px'>"
                    +"<tr>"
                        +"<td style='padding-top: 35px;'>"
                            +"<p style='color: white;'><b>Enter new PIN</b></p>"
                            +"<input type='password' width="+confirmButtonsW+" id='changeNPIN' readonly/>"
                        +"</td>"
                    +"</tr>"
                    +"<tr>"
                        +"<td style='padding-top: 15px;'>"
                            +"<p style='color: white;'><b>Re-Enter new PIN</b></p>"
                            +"<input type='password' width="+confirmButtonsW+" id='changeRNPIN' readonly/>"
                        +"</td>" 
                    +"</tr>"
                    +"<tr>"
                        +"<td>"
                            +"<input type='button' style='margin-top:"+confirmButtonsMarginTop+"' width="+confirmButtonsW+" id='confirmchangePIN2' value='Confirm'>"
                        +"</td>"
                    +"</tr>"
                +"</table>"
                +"<center><div id='buttcont2' style='display:none;'></div></center> "
            +"</div></center>";
     
     
          $.lytBox(table,changePinContW,changePinContH,true);
          
          $("#changeRNPIN").attr('disabled',true);

          
          table="";
       
   };
   
    $.createLobby2b = function()
    {

        table+="<center><div id='lobby2'>"
                 +"<div style='margin-bottom: 5px'>"
                     +"<input type='button' style='margin-right: 5px' class='myButtonStyle' id='nonPlatinum' value='Classic'>"
                     +"<input type='button'  id='platinum' class='myButtonStyle' value='Modern'>"
                 +"</div>"
                 +"<div style='margin-bottom: 5px'><input type='button' class='myButtonStyle' style='width:407px; height: 30px;' id='endSession' value='END SESSION'></div>"
//                 +"<div style='text-align: right;  padding-right:50px;'><input type='button' class='myButtonStyle' style='width:100px;' id='testLock' value='LOCK'></div>"
             +"</div></center>";

           $.lytBox(table,changePinContW,changePinContH-70,true);

           table="";
           
    };
  
    
});