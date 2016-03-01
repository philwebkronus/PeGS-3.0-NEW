

$(document).ready(function(){
    
          
    $.createKEW = function(){
     
            var val="";

            $.get('../views/description.html',function(data){

                    
                       val+="<a class='boxclose'></a><div id='kewID'>"
                          +"<div id='kewCont'>"
//                            +"<p class='learn'>"+data+"</p>"
                            +data
                            +"</div>"
                            +"<div id='agree'><input id='isagree'type='checkbox'/>I agree</div>";
                              
                       
                        val +="<br><br><center><div id='formUI'>"
                        +"<center><table><tr><td><div id='formCont'>"   
                        +"<tr><td colspan='2'><div id='labelInfo'><b>Enter Information<b></div></td></tr>"
                        +"<tr><td><label id='label'>Card Number </label></td><td><input type='text' class='width250 pad10' id='formCardNumber' name='formCardNumber' readonly><br /></td></tr>"  
                        +"<tr><td><label id='label'>Password </label></td><td><input type='password' class='width250 pad10' id='formPassword' name='formPassword' readonly><br /></td></tr>"  
                        +"<tr><td colspan=2><p id='portalLabel'>*Note: Password in Membership Website</p></td></tr>"  
                        +"<tr><td><label id='label'>PIN  </label></td><td><input type='password' class='width250 pad10' id='formNewPIN' name='formNewPIN' readonly><br /></td></tr>"  
                        +"<tr><td><label id='label'>Re-enter PIN </label></td><td><input type='password' class='width250 pad10' id='formRePIN' name='formRePIN' readonly><br /></td></tr>"    
                        +"</div></td></tr></table></center>"
                        +"<div id='infoBut'></div>"
                        +"</div></center></div>";
                        
                        $.lytBox(val,signupContW,lytBoxH,true);
                        $("#formCardNumber").attr('disabled',true);
                        $("#formPassword").attr('disabled',true);
                        $("#formNewPIN").attr('disabled',true);
                        $("#formRePIN").attr('disabled',true);
                        $("#kewID").css({"width":kewIDW,"height":kewIDH});
                        $("#kewCont").css({"width":kewContW});
                        $("#formUI").css({"width":formUIW});
                        $("#eSafetcbody").css({"width":eSafetcbodyW});
                        $("#whiteBox").css("background-color", "white");
                        $("#whiteBox").css("background", "white");
                        $("#whiteBox").css("filter", "");
                        capsLock = true;
                        $.populatePads();
                        $("button[value=-3]").hide();
                        $("button").attr('disabled',true);      
            });
    };
       
       

  $(function(){
          
          
       $(document).ajaxStart(function(){
          
          $("#blocker").css('filter','alpha(opacity=70)');   
          $("#blocker").show(); 
       });
       
       $(document).ajaxStop(function(){
          
          $("#blocker").hide(); 
       });
      
      
        $.checkUBCard = function(val)
        {
        if(infoUBValue!="" && infoPinValue!="")
        {
           
            $.checkTerminalType();

            $.post("../Helper/lock.php",
            {
               data:'checkUbCard',
               ubCard:infoUBValue
               
            },function(data){

          if(JSON.stringify(data['Status'])!=-1){  

            //for Conversion  
            if(val==0)
            {  
            if(JSON.stringify(data['IsEwallet'])!=1)
            {
                
                if(Switch)
                {
                        if(JSON.stringify(data['Status'])&&JSON.stringify(data['Status'])!=0
                        &&JSON.stringify(data['Status'])!=2&&JSON.stringify(data['Status'])!=7
                        &&JSON.stringify(data['Status'])!=8){ 


                            if(JSON.stringify(data['Status'])==5){
                                $.prompt("You are not allowed to convert a temporary card");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal();
                                capsLock = true;
                                $.populatePads();
                                infoPinValue="";
                                infoPinValue="000000";
                                $("button[value=-3]").hide();
                                $.buttonInfo($.infoUBValue,0);
                            }
                            else if(JSON.stringify(data['Status'])==9)
                            {
                                $.prompt("Card is banned.");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal();
                                capsLock = true;
                                $.populatePads();
                                infoPinValue="";
                                infoPinValue="000000";
                                $("button[value=-3]").hide();
                                $.buttonInfo($.infoUBValue,0);
                            }
                            else{
                                 $.isAllowedToConvert(infoUBValue);
                                if(isAllowed==1)
                                {
                                    $.checkPassword(JSON.stringify(data['MID']));
                                }
                                 else
                                {
                                    $.prompt("Your card is not allowed to convert to e-SAFE");
                                    $.resetVal(0);
                                    $.resetVal(1);
                                    $.resetVal();
                                    capsLock = true;
                                    $.populatePads();
                                    infoPinValue="";
                                    infoPinValue="000000";
                                    $("button[value=-3]").hide();
                                    $.buttonInfo($.infoUBValue,0);  
                                }
                                
                            }

                      }
                      else
                      {
                          $.prompt("Card is invalid");
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal();
                            capsLock = true;
                            $.populatePads();
                            infoPinValue="";
                            infoPinValue="000000";
                            $("button[value=-3]").hide();
                            $.buttonInfo($.infoUBValue,0);  
                      }
                }
                else
                {
                        if(JSON.stringify(data['Status'])&&JSON.stringify(data['Status'])!=0
                        &&JSON.stringify(data['Status'])!=2&&JSON.stringify(data['Status'])!=7
                        &&JSON.stringify(data['Status'])!=8){ 



                            if(JSON.stringify(data['Status'])==5){
                                $.prompt("You are not allowed to convert a temporary card");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal();
                                capsLock = true;
                                $.populatePads();
                                infoPinValue="";
                                infoPinValue="000000";
                                $("button[value=-3]").hide();
                                $.buttonInfo($.infoUBValue,0);
                            }
                            else if(JSON.stringify(data['Status'])==9)
                            {
                                $.prompt("Card is banned.");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal();
                                capsLock = true;
                                $.populatePads();
                                infoPinValue="";
                                infoPinValue="000000";
                                $("button[value=-3]").hide();
                                $.buttonInfo($.infoUBValue,0);
                            }
                            else{$.checkPassword(JSON.stringify(data['MID']));};

                      }

                      else
                      {
                            $.prompt("Card is invalid");
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal();
                            capsLock = true;
                            $.populatePads();
                            infoPinValue="";
                            infoPinValue="000000";
                            $("button[value=-3]").hide();
                            $.buttonInfo($.infoUBValue,0);  
                      }
                    
                } 
          }
          else
          {
                $.prompt("Card is already e-SAFE");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal();
                infoPinValue="";
                infoPinValue="000000";
                $.lytBox("","","",false);
          }
      }
      //for Login
      else if(val==1)
      {
          if(JSON.stringify(data['Status'])&&JSON.stringify(data['Status'])!=0
                &&JSON.stringify(data['Status'])!=2&&JSON.stringify(data['Status'])!=7
                &&JSON.stringify(data['Status'])!=8){ 
          if(JSON.stringify(data['Status'])==5)
                    {
                        $.prompt("You are not allowed to login a temporary card");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#ubfield").trigger('change');
        
                    }  
                    else if(JSON.stringify(data['Status'])==9)
                    {
                        $.prompt("Card is banned.");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#ubfield").trigger('change');
                    }
                    else{$.checkIsEwallet(tmpMid,2);}
                }
                else
                {
                    $.prompt("Card is invalid");
                    $("#ubfield").val("");
                    //$("#pinfield").val("");
                    infoUBValue="";
                    infoPinValue="";
                    infoPinValue="000000";
                    $("#ubfield").trigger('change');
                }
            }
            else
            {

                //for Change Pin Module
                if(JSON.stringify(data['Status'])&&JSON.stringify(data['Status'])!=0
                &&JSON.stringify(data['Status'])!=2&&JSON.stringify(data['Status'])!=7
                &&JSON.stringify(data['Status'])!=8)
                { 
                    if(JSON.stringify(data['Status'])==5)
                    {
                        $.prompt("You are not allowed to login a temporary card");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoUBValue="";
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#changePINUB").val("");
                        $("#changeUserPIN").val("");
                        $("#changePINUB").trigger('change');
        
                    }  
                    else if(JSON.stringify(data['Status'])==9)
                    {
                        $.prompt("Card is banned.");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoUBValue="";
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#changePINUB").val("");
                        $("#changeUserPIN").val("");
                        $("#changePINUB").trigger('change');
                    }
                    else
                    {
                        if(JSON.stringify(data['IsEwallet'])==1){
                            $.checkPin(JSON.stringify(data['MID']));  
                        } else {
                            $.prompt("Card is not yet an e-SAFE. If you wish to convert, kindly click the 'Sign up' link.");
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal(2);
                            $.resetVal();
                            infoUBValue="";
                            infoPinValue="";
                            infoPinValue="000000";
                            $("#changePINUB").val("");
                            $("#changeUserPIN").val("");
                            $("#changePINUB").trigger('change');
                            $.lytBox("","","",false);
                        }
                    }
                }
                else
                {
                    $.prompt("Card is invalid");
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    $.resetVal();
                    infoUBValue="";
                    infoPinValue="";
                    infoPinValue="000000";
                    $("#changePINUB").val("");
                    $("#changeUserPIN").val("");
                    $("#changePINUB").trigger('change');
                }
                
                
            }
            
        }
        else
        {
            $.prompt("Card is invalid");
            $.resetVal(0);
            $.resetVal(1);
            $.resetVal(2);
            $.resetVal();
            infoUBValue="";
            infoPinValue="";
            infoPinValue="000000";
            $("#changePINUB").val("");
            $("#changeUserPIN").val("");
            $("#changePINUB").trigger('change');
        }
            },'json');
    }else
    {
        infoPinValue="";
        infoPinValue="000000";
        $.prompt("Please fill in fields");
        $.resetVal(0);
        $.resetVal(1);
        $.resetVal(2);
        $.resetVal();
    }       
};
    
     
        $.checkPassword = function(mid)
        {
            
            
            $.post("../Helper/lock.php",
            {
                data:'checkPassword',
                pass:infoPassValue,
                mid:mid
            },function(data){
      
                    if(data>0)
                    {
                        $.checkPinInfo(mid);
                    }
                    else
                    {
                        $.prompt('Password is invalid');
                        $("#formPassword").val("");
                        $("#formNewPIN").val("");
                        $("#formRePIN").val("");
                        infoPassValue="";
                        infoPinValue="";
                        infoRPinValue="";
                        capsLock = false;
                        $.populatePads();
                        $("button[value=-3]").show();
                        $.buttonInfo($.infoPassValue,1);
 
                    }
                
            },'json');
            
        };
        
        $.checkPinInfo = function(mid)
        {
          if(infoPinValue.length==maxPinVal)
          {
            if(infoPinValue!=infoRPinValue)
            {
                $.prompt("New PIN and Re-Enter PIN did not match");
                $("#formNewPIN").val("");
                $("#formRePIN").val("");
                infoPinValue="";
                infoRPinValue="";
                $.populatePads2();
                $.buttonInfo($.infoPinValue,2);
                
            }
            else
            {
                $.checkSession(tmpMid);
            }
          
        }
        else
        {
            $.prompt("Maximum of six(6) numeric characters.");
            $("#formNewPIN").val("");
            $("#formRePIN").val("");
            infoPinValue="";
            infoRPinValue="";
            $.populatePads2();
            $.buttonInfo($.infoPinValue,2);
        }
            
        };
        
        $.checkPin = function(mid)
        {   
            
            var pinAttempts = 0;
            $.post("../Helper/lock.php",
            {
               data:'checkPin',
               mid:mid,
               cardNumber:infoUBValue,
               pinVal:infoPinValue
               
            },function(data){
                if(infoPinValue.length>maxPinVal){
                    $.prompt(JSON.stringify("PIN must be exactly 6 digits."));
                    $("#pinfield").val("");
                    $("#changeUserPIN").val("");
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    $("#hdncont1").hide(); 
                    $("#cont1").show();
                    $("#left").css("height","390px");
                    $.buttonInfo($.ubValue,4);
                } else {
                    if(JSON.stringify(data['pinRes'])!="null")
                    {
                        if(JSON.stringify(data['pinRes'])==14) {
                            $.prompt("PIN is invalid");
                            //$("#pinfield").val("");
                            $("#changeUserPIN").val("");
                            if(infoPinValue == ""){
                                infoPinValue="000000";
                            }
                        } else if(JSON.stringify(data['pinRes'])==25) {
                                //update pin attempts(maximum)
                                $.prompt('PIN is invalid. Your account has been locked');
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal(2);
                                infoPinValue="";
                                infoPinValue="000000";
                        }
                        else if (JSON.stringify(data['pinRes']) == 0)
                        {
                            if(JSON.stringify(data['PINLoginAttemps']) < maxAttempts)
                            {
                                if(!$('#changePinModule').is(':visible')){
                                    tmpUB = infoUBValue;
                                    tmpPin = infoPinValue;

                                    $.resetVal(0);

                                    $("#ubfield").val("");
                                    //$("#pinfield").val("");
                                    //$.populatePads3();

    //                                if(JSON.stringify(data['DatePINLastChange'])==0)
    //                                {
    //
    //                                    $("#cont1").hide();
    //                                    $("#hdncont1").show();
    //                                    $("#left").css("height","400px");
    //                                    $.populatePads4();
    //                                    infoPinValue="";
    //                                    $.buttonInfo($.NewPinValue,6);
    //
    //                                }
    //                                else
    //                                {
                                         $.checkIsCardSession('',2);
        //                                 $.checkEwalletSession();
                                    //}
                                } 
                                else
                                {

                                    //module for changePin fucnion
                                    $("#changePINUB").val("");
                                    $("#changeUserPIN").val("");
                                    tmpUB = infoUBValue;
                                    tmpPin = infoPinValue;
                                    $.resetVal(0);
                                    $.createNewPinTable();
                                }

                            }
                            else
                            {
                                $.prompt('Invalid PIN/Account has been Locked');
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal(2);
                                $("#hdncont1").hide(); 
                                $("#cont1").show();
                                $("#left").css("height","400px");
                                infoPinValue="";
                                infoPinValue="000000";
                                $.buttonInfo($.ubValue,4);
                            }  
                        }
                        else {
                            $.prompt(JSON.stringify(data['pinMsg']));
                            //$("#pinfield").val("");
                            $("#changeUserPIN").val("");
                            if(infoPinValue == ""){
                                infoPinValue="000000";
                            }
                        }
                    }
                    else
                    {

                            $.prompt('Invalid Player Details');
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal();
                            infoPinValue="";
                            infoPinValue="000000";
                            $("#hdncont1").hide(); 
                            $("#cont1").show();
                            $("#left").css("height","400px");
                            $("#ubfield").trigger('change');
                    }
                }
               
            },'json');
            
        };
        
        $.updatePin = function()
        {   
          if(infoPinValue!=""&&infoRPinValue!="")
          {
          if(infoPinValue.length==maxPinVal)
          {
          
            if(infoPinValue!=infoRPinValue)
            {
                $.prompt("New PIN and Reenter PIN did not match");
                $("#newPinField").val('');
                $("#rnewPinField").val('');
                if($('#changePinModule').is(':visible'))
                {
                 $("#changeNPIN").val('');
                 $("#changeRNPIN").val('');
                }
            
                infoPinValue="";
                infoRPinValue="";
            }
            else{ 
            $.post("../Helper/lock.php",
                {
                    data:'updatePin',
                    cardNumber:tmpUB,
                    oldP:tmpPin,
                    newP:infoPinValue     
                },function(data){
                  
                    if(data==0)
                    {
//                       $.checkIsEwallet(tmpMid,2); 
                        if(!$('#changePinModule').is(':visible'))
                        {
                            $.checkIsCardSession('',2);
                            pinNomination = false;
                        }
                        else
                        {
                            $.prompt('PIN successfully changed!');
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal(2);
                            $.lytBox("","","",false);
                            
                        }
                    }
                    else
                    {
                        
                        $.prompt("Failed to change your PIN. Please try again");
                        $("#hdncont1").hide(); 
                        $("#cont1").show();
                        $("#left").css("height","400px");
                        
                        $("#changeNPIN").val('');
                        $("#changeRNPIN").val('');
                    }
                });
            }
            }
            else
            {
                $.prompt("Maximum of six(6) numeric characters.");
                $("#newPinField").val('');
                $("#rnewPinField").val('');
                $("#changeNPIN").val('');
                $("#changeRNPIN").val('');
                infoPinValue="";
                infoRPinValue="";
            }
        }
        else
        {
            $.prompt("Please Fill in fields");
            infoPinValue="";
            infoPinValue="000000";
        }
        };
        
        
//        $.updateAttempts = function(attempts,mid,msg)
//        {   
//
//            $.post("../Helper/lock.php",
//                            {
//                                data:'updateAttempts',
//                                mid:mid,
//                                attempts:attempts  
//                            },function(){},'json');
//                      pinAttempts=0;
//                      $.prompt(msg);
//            
//        };
        
        
        $.checkSession = function(mid)
        {
            var sessionsUB ="";
            
            $.post("../Helper/lock.php",
            {
                data:'checkIfTerminalSession',
                terminalCode:terminalCode,
                option:0
            },function(data){
               
              
            sessionsUB = JSON.stringify(data['UBCard']).replace(/\"/g, "");
               
            if(JSON.stringify(data['UserMode'])!=1)
            {
               
                if(JSON.stringify(data['Count'])>0)
                {
                    
                    $.checkIsCardSession(JSON.stringify(data['MID']),0);
                    
                }
                else
                {
                 
                    $.checkIsCardSession(mid,1);

                }
            }
            else
            {
                if(sessionsUB!=infoUBValue)
                {
                  
                   
                       
                        $.checkIsCardSession(mid,1);

                }
                else
                {
                
                    $.prompt("There is an existing terminal session under your card. Please end the session first");
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    $.resetVal();
                    infoPinValue="";
                    infoPinValue="000000";
                    capsLock = true;
                    $.populatePads();
                    $("button[value=-3]").hide();
                    $.buttonInfo($.infoUBValue,0);
                }
            }
                
            },'json');
            
        };
        
        $.checkIsCardSession = function(mid,option)
        {
            var ub="";
            var option1="";

            if(mid!="")
            {
               
               ub = infoUBValue;
               option1 = 0;
            }
            else
            {
                ub = tmpUB;
                option1 = 1;
            }
            
           
            $.post("../Helper/lock.php",
            {
                
                data:'checkIsCardSession',
                ubCard:ub,
                option:option1
            },function(data){

                if(JSON.stringify(data['Count'])>0)
                {
                    
                    if(option!=2)
                    {
                        //Conversion
                        if(JSON.stringify(data['UserMode'])==0)
                        {
                            $.checkEGMSession(mid);
                        }
                        else
                        {
                            $.prompt("There is an existing terminal session under your card. Please end the session first");
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal(2);
                            $.resetVal();
                            infoPinValue="";
                            infoPinValue="000000";
                            capsLock = true;
                            $.populatePads();
                            $("button[value=-3]").hide();
                            $.buttonInfo($.infoUBValue,0);
                        }   
                    
                    }
                    else
                    {
                       
                        //login
                        if(JSON.stringify(data['SiteID'])!=siteID)
                        {
                            $.prompt("There is an existing terminal session under your card. Please end the session first");
                            $.resetVal(0);
                            $.resetVal(1);
                            $.resetVal(2);
                            $.resetVal();
                            infoPinValue="";
                            infoPinValue="000000";
                            //$("#hdncont1").hide(); 
                            //$("#cont1").show();
                            $("#left").css("height","400px");
                            //$("#ubfield").trigger('change');
                        }
                        else
                        {
                            if(JSON.stringify(data['TerminalCode']) == JSON.stringify(terminalCode) || JSON.stringify(data['TerminalCode']) == JSON.stringify(terminalCode+"VIP")){
                                $.checkEwalletSession(2);
                                //Delete existing session
                                /*$.post("../Helper/lock.php",
                                        {
                                        data:'deleteExistingSession',
                                        UBServiceLogin:JSON.stringify(data['UBServiceLogin']).replace(/\"/g, "")
                                        },function(data){
                                            if(JSON.stringify(data)!=0)
                                            {

                                                    $.prompt("There is an existing terminal session in e-SAFE. Please try again");
                                                    $.resetVal(0);
                                                    $.resetVal(1);
                                                    $.resetVal(2);
                                                    $.resetVal();
                                                   // $("#hdncont1").hide(); 
                                                    //$("#cont1").show();
                                                    $("#left").css("height","400px");
                                                    //$("#ubfield").trigger('change');
                                            }
                                            else
                                            {
                                                $.checkEwalletSession();   
                                            }

                                    },'json');*/
                            } else {
                                $.prompt("There is an existing terminal session under your card. Please end the session first");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal(2);
                                $.resetVal();
                                infoPinValue="";
                                infoPinValue="000000";
                               // $("#hdncont1").hide(); 
                                //$("#cont1").show();
                                $("#left").css("height","400px");
                                //$("#ubfield").trigger('change');
                            }
                        }
                    }
                    
                }
                else
                {
                    
                    if(option==0)
                    {
                        $.checkIsEwallet(mid,1);
                    }
                    else if(option==1)
                    {
                         $.checkEGMSession(mid);
                    }
                    else
                    {
                  
                        $.checkEwalletSession();    
                    }
                }
                
            },'json');
            
        };
       
        $.checkIsEwallet = function(mid,option)
        {
            //option=is for checking if the call is from conversion or not
            
            $.post("../Helper/lock.php",
            {
               data:'isEwallet',
               mid:mid       
            },function(data){
                
                if(data==0)
                {
                    if(option==0)
                    {
                        //tag as e-SAFE
                        $.tagEwallet(mid);
                    }
                    else if(option==1)
                    {
          
                         $.tagEwallet(mid);
  
                    }
                    else
                    {
                        $.prompt("Card is not yet an e-SAFE. If you wish to convert, kindly click the 'Sign up' link.");
                        $("#newPinField").val('');
                        $("#rnewPinField").val(''); 
                       
                        $("#hdncont1").hide();
                        $("#cont1").show();
                        $("#left").css("height","400px");
                        tmpMid="";
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#ubfield").trigger('change');
                    } 
                }
                else
                {
                    if(option==0)
                    {
                        $.prompt("Card is already e-SAFE");
                        $.lytBox("","","",false);
                        $.populatePads3();
                        $.resetVal(0);
                        $.buttonInfo($.ubValue,4);     
                    }
                    else if(option==1)
                    {
                        $.tagEwallet(tmpMid);
                    }
                    else
                    {
                        
                        $.checkPin(tmpMid);
                        
                    }
                }
            
            });
        };

        $.checkTerminaServicesSession = function(option)
        {

            $.post("../Helper/lock.php",
            {
                data:'checkTerminaServicesSession',
                terminalCode:terminalCode,
                option:0
            },function(data){
               
                if(JSON.stringify(data['Count'])>0)
                {
                      tmpServiceID = JSON.stringify(data['ServiceID']);
                      tmpUserMode = JSON.stringify(data['UserMode']);
                      tmpServiceGroupID = JSON.stringify(data['ServiceGroupID']);
                      
                 if(tmpServiceID==JSON.stringify(data['ServiceIDVIP'])){     
                     
                      $.post("../Helper/lock.php",
                      {
                          data:'countMappedCasinos',
                          TerminalID:JSON.stringify(data['TerminalID']),
                          TerminalIDVIP:JSON.stringify(data['TerminalIDVIP']),
                          option:0
                      },function(data){
                           
                            if(data<=1)
                            {
                                if(tmpUserMode==1&&tmpServiceGroupID==4)
                                {
//                                    $.checkPin(tmpMid);
                                     if(option==1){ 
                                       if(pinNomination)
                                        {
                                            $.updatePin();
                                        }
                                        else
                                        {
                                           
                                           $.checkEwalletSession(1);//for login
                                           //$.checkUBCard(1); 
                                        }
                                    }
                                    else
                                    {
                                        $.checkUBCard(0); 
                                    }
                                }
                                else
                                {
                                    if(option!=0)
                                    {
                                        $.prompt("Casino mapped is terminal based. Please click on the 'Instant Play' button.");
                                        $.resetVal(0);
                                        $.resetVal(1);
                                        $.resetVal(2);
                                        $.resetVal();
                                        infoPinValue="";
                                        infoPinValue="000000";
                                        $("#hdncont1").hide(); 
                                        $("#cont1").show();
                                        $("#left").css("height","400px");
                                        $("#ubfield").trigger('change');
                                    }
                                    else
                                    {
                                        $.checkUBCard(0); 
                                    }
                                }
                            }
                            else
                            {
                                $.prompt("Invalid terminal. Terminal has more than one(1) casino​");
                                $.resetVal(0);
                                $.resetVal(1);
                                $.resetVal(2);
                                $.resetVal();
                                infoPinValue="";
                                infoPinValue="000000";
                                $("#hdncont1").hide(); 
                                $("#cont1").show();
                                $("#left").css("height","400px");
                                $("#ubfield").trigger('change');
                            }
                           
                      });
                      
                }else
                    {
                        $.prompt("Invalid Terminal. Terminal is not properly mapped");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoPinValue="";
                        infoPinValue="000000";
                        $("#hdncont1").hide(); 
                        $("#cont1").show();
                        $("#left").css("height","400px");
                        $("#ubfield").trigger('change');
                    }
                   
            }
            else
                {
                    $.prompt("Casino not available");
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    $.resetVal(0);
                    infoPinValue="";
                    infoPinValue="000000";
                    $("#hdncont1").hide(); 
                    $("#cont1").show();
                    $("#left").css("height","400px");
                    $("#ubfield").trigger('change');
                }
            
             
            },'json');
            
            
        };
        

        $.checkEGMSession = function(mid)
        {
            $.post("../Helper/lock.php",
            {
                data:'checkEGMSession',
                mid:mid
            },function(data){
               
                if(JSON.stringify(data)==0)
                {
                      $.checkIsEwallet(mid,0);
                }
                else
                    {
                        $.prompt("There is an existing egm session under your card. Please end the session first");
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $.resetVal();
                        infoPinValue="";
                        infoPinValue="000000";
                        capsLock = true;
                        $.populatePads();
                        $("button[value=-3]").hide();
                        $.buttonInfo($.infoUBValue,0);
                    }
             
            },'json');

        };
        
        
        $.tagEwallet = function(mid)
        {

            $.post("../Helper/lock.php",
            {
                data:"tagEwallet",
                mid:mid,
                pin:infoPinValue
            },function(){

                
                $.prompt("You have successfully converted to e-SAFE");
                $.lytBox("","","",false);
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal();
                infoPinValue="";
                infoPinValue="000000";
                $("#hdncont1").hide(); 
                $("#cont1").show();
                $("#left").css("height","400px");
                
            });
            
        };
   }); 
  
  
       $.checkEwalletSession = function(option2)
        {
            
            
            var userMode="";
            var serviceGroupID="";
            
            $.post("../Helper/lock.php",
            {
                data:"checkEwalletSession",
                terminalCode:terminalCode,
                option:0
            },function(data){
               
               if(JSON.stringify(data['UBServiceLogin'])!=0)
               {
                if(JSON.stringify(data['IsEwallet'])!=0)
                {   
                    
                   if(option2 == 0)
                   {    
                        userMode =JSON.stringify(data['UserMode']);
                        serviceGroupID =JSON.stringify(data['ServiceGroupID']);
                        tmpUBserviceLogin = JSON.stringify(data['UBServiceLogin']);
                        tmpServiceID = JSON.stringify(data['ServiceID']);

                        if(userMode==1&&serviceGroupID==4)
                        {
                            tmpUBServicePassword = JSON.stringify(data['UBHashedServicePassword']);
                        }
                        if(userMode==1&&serviceGroupID!=4)
                        {
                            tmpUBServicePassword = $.parseJSON(JSON.stringify(data['UBServicePassword']));
                        }

                        tmpUBServicePassword = tmpUBServicePassword.replace(/\"/g, "");
                        
                        
                        //delete existing session
                        $.deleteExistingSession();
                   }
                   else if(option2 == 2) //for login (phase 3)
                   {    
                        userMode =JSON.stringify(data['UserMode']);
                        serviceGroupID =JSON.stringify(data['ServiceGroupID']);
                        tmpUBserviceLogin = JSON.stringify(data['UBServiceLogin']);
                        tmpServiceID = JSON.stringify(data['ServiceID']);

                        if(userMode==1&&serviceGroupID==4)
                        {
                            tmpUBServicePassword = JSON.stringify(data['UBHashedServicePassword']);
                        }
                        if(userMode==1&&serviceGroupID!=4)
                        {
                            tmpUBServicePassword = $.parseJSON(JSON.stringify(data['UBServicePassword']));
                        }

                        tmpUBServicePassword = tmpUBServicePassword.replace(/\"/g, "");
                        
                        $.insertNewSession();
                        
                   }
                   else
                   {
                       
                       if(infoPinValue!="")
                       {
                           infoUBValue = JSON.stringify(data['LoyaltyCardNumber']).replace(/\"/g, "");
                
                           $.getMID(2);//login phase 3 (pin only)
                       }
                       else
                       {
                           $.prompt("Please input PIN");
                           infoPinValue="";
                       }
                       
                   }
                   
                }
                else
                {
                    $.prompt("Terminal has an existing session.Please click on the 'Instant Play' button.");
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    infoPinValue="";
                    infoPinValue="000000";
                    $("#hdncont1").hide(); 
                    $("#cont1").show();
                    $("#left").css("height","400px");
                    $("#buttcont").hide();
                    
                }
               }
               else
               {   
                  
                   if(!option2)
                   {
                    //insert session
                    $.insertNewSession();  
                   }
                   else
                   {
                         
                       $.prompt("There is no active session.");
                       $("#pinfield").val("");
                       $("#pinfield").val("000000");
                       infoPinValue = "";
                       infoPinValue = "000000";
                       //$.showRegular(false);
                   }
                  
               }
            },'json'); 
        };
        
        $.deleteExistingSession = function()
        {

           
            $.post("../Helper/lock.php",
            {
                data:'deleteExistingSession',
                UBServiceLogin:tmpUBserviceLogin,
                UBServiceID:tmpServiceID
            },function(data){

                if(JSON.stringify(data)!=0)
                {
                    //alert("delete session: " + JSON.stringify(data)); //****
                    countTry++;
                    if(countTry<2)
                    {
                        //alert("deletesession1: " + countTry);   //****
                        $.deleteExistingSession();
                    }
                    else
                    {
                        //alert("deletesession2: " + countTry);        //****
                        $.prompt("There is an existing terminal session in e-SAFE. Please try again");
                        countTry=0;
                        $.resetVal(0);
                        $.resetVal(1);
                        $.resetVal(2);
                        $("#hdncont1").hide(); 
                        $("#cont1").show();
                        $("#left").css("height","400px");
                        $("#buttcont").hide();
                    }
                }
                else
                {
                    countTry=0;
                    $.insertNewSession();
                }
            },'json');
            
        };
        
        $.checkUBSession = function(option3){

            $.post("../Helper/lock.php",
                {
                    data:"checkEwalletSession",
                    terminalCode:terminalCode,
                    option:1
                },function(data){
                        
                        tmpServiceID = JSON.stringify(data['ServiceID']);
                        tmpUBserviceLogin = JSON.stringify(data['UBServiceLogin']);
                        tmpUBServicePassword = JSON.stringify(data['UBServicePassword']);
                        tmpUBHashedServicePassword = data['UBHashedServicePassword'];
                        
                        if(!(tmpUBserviceLogin===''||tmpUBserviceLogin===0||tmpUBserviceLogin===null||tmpUBserviceLogin==='null'||tmpUBserviceLogin===undefined||tmpUBserviceLogin==='undefined')){

                            if(option3 == 0){ //launch classic game client.
                                if(tmpUBserviceLogin != "0"){
                                    if(tmpServiceID != "19"){
                                        $.launchGame(tmpServiceID+".1",tmpUBserviceLogin,tmpUBHashedServicePassword);
                                    } else {
                                        $.launchGame(tmpServiceID,tmpUBserviceLogin,tmpUBHashedServicePassword);
                                    }
                                } else {
                                    $.checkLandingPage();
                                }
                            }else if(option3 == 1){ //launch modern game client.
                                if(tmpUBserviceLogin != "0"){
                                    $.launchGame(tmpServiceID+".2",tmpUBserviceLogin,tmpUBHashedServicePassword);
                                } else {
                                    $.checkLandingPage();
                                }
                            }else{ //end session.
                                if(tmpUBserviceLogin != "0"){
                                    $.endUBSession();
                                } else {
                                    $.checkLandingPage();
                                }
                            }                          
                        }else{
                            $.prompt("Service login is empty.");
			    infoPinValue="";
                            infoPinValue="000000";
                            $.checkLandingPage();
                        }
                        
                },'json'
            );  
               
        }
        
        $.checkLandingPage = function(){
            $.ajax({
                url:'../Helper/connector.php',
                type: 'post',
                dataType: 'json',
                async: true,
                data:{fn:function(){return 'getTerminalUserMode';},
                     TerminalCode:function(){return terminalCode;}},
                success:function(data){
                    if(data != null){
                        userMode = JSON.stringify(data['UserMode']).replace(/"/g,""); 
                        serviceCode = JSON.stringify(data['Code']).replace(/"/g,""); 
                        siteClassID = JSON.stringify(data['SiteClassID']).replace(/"/g,""); 
                        terminalType = JSON.stringify(data['TerminalType']).replace(/"/g,""); 

                        if(IsDetectTerminalType){
                            if(siteClassID == 1 || siteClassID == 2){ // Platinum/NonPlatinum site
                                window.location.href = LPewalletURL;
                            }
                        }

                        $.ajax({
                                url:'../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async:false,
                                data:{fn:function(){return 'checkForExistingSession';},
                                     TerminalCode:function(){return terminalCode;}},
                                success:function(data){
                                    SessionType = JSON.stringify(data['SessionType']);
                                    serviceid = data['ServiceID'];
                                    TransactionSummaryID = JSON.stringify(data['TransactionSummaryID']);
                                    isesafe = JSON.stringify(data['IsEwallet']); 

                                    if(SessionType == 1) { //terminal based session
                                       $.showRegular(true);
                                    } else if(isesafe == 1 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) { //e-safe, user based session
                                        $.showLobby2(true);
                                    } else if(isesafe == 0 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) { //non e-safe, user based session
                                        $.$.showRegular(true);
                                    } else { //return to the default home page if there's no active session with valid login start
                                        if(terminalType==2&&(userMode=="0"||userMode=="2")){
                                            $.prompt("Session was already ended.");
                                            infoPinValue="";
                                            infoPinValue="000000";
                                            $.showRegular(true);
                                        } else if(terminalType==2&&userMode=="1"){ 
                                            $.prompt("Session was already ended.");
                                            infoPinValue="";
                                            infoPinValue="000000";  
                                            $.showRegular(false);
                                        } else {
                                            $.prompt("Invalid terminal. Terminal is not properly mapped.");
                                            infoPinValue="";
                                            infoPinValue="000000";
                                        }
                                    }
                                }
                            });   
                    } else {
                        $.prompt("Invalid terminal. Can't get terminal details.");
                        infoPinValue="";
                        infoPinValue="000000";
                    }
                }
            });
        };
        
        $.endUBSession = function(){
            $.post("../Helper/lock.php",
                {
                    data:'deleteExistingSession',
                    UBServiceLogin:tmpUBserviceLogin,
                    UBServiceID:tmpServiceID
                },function(data){

                    if(JSON.stringify(data)!=0){
                        countTry++;
                        if(countTry<2){
                            $.endUBSession();
                        }
                        else{
                            $.prompt("There is an existing terminal session in e-SAFE. Please try again");
                            infoPinValue="";
                            infoPinValue="000000";
                            countTry=0;
                            $.checkLandingPage();
                        }
                    } else {
                        $.checkLandingPage();
                    }
                },'json'
            );                      
        };
        
        $.insertNewSession = function()
        {
           
        showLightbox(function(){
            
          
          $.post("../Helper/lock.php",
            {
                data:'insertNewSession',
                terminalCode:terminalCode.replace(/ICSA-/,''),
                ServiceID:tmpServiceID,
                ubCard:tmpUB
            },function(data){
               //alert("1: " + JSON.stringify(data['ErrorCode']) + "\n"  + tmpUBserviceLogin  + " " + tmpUBServicePassword);   //****
                if(JSON.stringify(data['ErrorCode'])==0 || JSON.stringify(data['ErrorCode'])==23)
                {
                    if(tmpUBserviceLogin==""&&tmpUBServicePassword=="")
                    { 
                        var userMode="";
                        var serviceGroupID="";

                        $.post("../Helper/lock.php",
                        {
                            data:"checkEwalletSession",
                            terminalCode:terminalCode,
                            option:0
                        },function(data){
//                            alert("2: " + userMode + " " + serviceGroupID);     //****
                            
                            userMode =JSON.stringify(data['UserMode']);
                            serviceGroupID =JSON.stringify(data['ServiceGroupID']);
                            tmpUBserviceLogin = JSON.stringify(data['UBServiceLogin']);

                            if(userMode==1&&serviceGroupID==4)
                            {
                                tmpUBServicePassword = JSON.stringify(data['UBHashedServicePassword']);
                            }
                            if(userMode==1&&serviceGroupID!=4)
                            {
                                tmpUBServicePassword = $.parseJSON(JSON.stringify(data['UBServicePassword']));
                            }

                            tmpUBServicePassword = tmpUBServicePassword.replace(/\"/g, "");
                                         
                            jQuery.fancybox.close();
                            $.checkLandingPage();
                            
                            $.ajax({
                                url:'../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async: true,
                                data:{fn:function(){return 'getTerminalSiteClassification';},
                                     TerminalCode:function(){return terminalCode;}},
                                success:function(data){
                                    if(tmpServiceID!=20)
                                        $("#platinum").attr('disabled',true);  
//                                    if(JSON.stringify(data['SiteClassificationID'])!="1")                                              
//                                        $("#platinum").attr('disabled',true);                        
                                }
                            });                     
                        
//                            $.launchGame(tmpServiceID,tmpUBserviceLogin,tmpUBServicePassword);
//                            $.resetVal(0); 
//                            $.resetVal(1);
//                            $.resetVal(2);    
//                            $("#left").css("height","400px");
//                            $("#buttcont").hide();
                            
                        });   
                    }
                    else
                    {
//                        alert("3: ");       //****
                       jQuery.fancybox.close();
                        $.checkLandingPage();
                        
                            $.ajax({
                                url:'../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async: true,
                                data:{fn:function(){return 'getTerminalSiteClassification';},
                                     TerminalCode:function(){return terminalCode;}},
                                success:function(data){
                                    if(tmpServiceID!=20)
                                        $("#platinum").attr('disabled',true);  
//                                    if(JSON.stringify(data['SiteClassificationID'])!="1")                                            
//                                        $("#platinum").attr('disabled',true);                        
                                }
                            });  

//                        $.launchGame(tmpServiceID,tmpUBserviceLogin,tmpUBServicePassword);
//                        $.resetVal(0);
//                        $.resetVal(1);
//                        $.resetVal(2); 
//                        //$("#hdncont1").hide(); 
//                        //$("#cont1").show();
//                        $("#left").css("height","400px");
//                        $("#buttcont").hide();
                    }
                }
                else
                {
//                    alert("4: "+ JSON.stringify(data['TransactionMessage']));       //****
                    jQuery.fancybox.close();
                    $.prompt(JSON.stringify(data['TransactionMessage']).replace(/\"/g, ""));
                    $.resetVal(0);
                    $.resetVal(1);
                    $.resetVal(2);
                    infoPinValue="";
                    infoPinValue="000000";
                    $("#hdncont1").hide(); 
                    $("#cont1").show();
                    $("#left").css("height","400px");
                    $("#buttcont").hide();
                }
                
            },'json');
            
            });
            
        };
  
         $.infoUBValue = function(value){
          
            infoUBValue+=value;
            $("#formCardNumber").val(infoUBValue);
        };
        
        $.infoPassValue = function(value){
          
            infoPassValue+=value;
            $("#formPassword").val(infoPassValue);   
        };
        
        $.infoPinValue = function(value){
          
            infoPinValue+=value;
            $("#formNewPIN").val(infoPinValue);   
        };
        
        $.infoRPinValue = function(value){
          
            infoRPinValue+=value;
            $("#formRePIN").val(infoRPinValue);   
        };
        
         $.ubValue = function(value){
          
            infoUBValue+=value;
            $("#ubfield").val(infoUBValue);   
        };
        
         $.PinValue = function(value){
            
            infoPinValue+=value;
            if(infoPinValue == ""){
                infoPinValue="000000";
            }
            $("#pinfield").val(infoPinValue);   
        };
        
        $.NewPinValue = function(value){
          
            infoPinValue+=value;
            $("#newPinField").val(infoPinValue);   
        };
        
        
        $.RNewPinValue = function(value){
          
            infoRPinValue+=value;
            $("#rnewPinField").val(infoRPinValue);   
        };
        
        
        
        //changePIN
        $.changePINUB = function(value)
        {
            infoUBValue+=value;
            $("#changePINUB").val(infoUBValue);
            
        };
        
        $.changePINnom = function(value)
        {
            infoPinValue+=value;
            $("#changeUserPIN").val(infoPinValue);  
        };
        
        
        $.changeNewPIN = function(value)
        {
            infoPinValue+=value;
            $("#changeNPIN").val(infoPinValue);   
            
        };
        
        $.changeNewRPIN = function(value)
        {
            infoRPinValue+=value;
            $("#changeRNPIN").val(infoRPinValue);     
        };
        
        $.isAllowedToConvert = function(ubCard)
        {
            $.ajax({
                        url:"../Helper/lock.php",
                        type: 'post',
                        dataType: 'json',
                        async:false,
                        data:{data:function(){return 'isAllowed';},
                             ubCard:function(){return ubCard;}},
                        success:function(data){
                            
                      
                            isAllowed = JSON.stringify(data);
                        }
            });
        };
        
        $.getMaxAttempts = function()
        {
            $.post("../Helper/lock.php",
            {
                data:'getMaxAttempts'
            },function(data){
             
                $.maxattempts(data);
            });
        };
        
        $.maxattempts = function(attempts)
        {
            maxAttempts = attempts*1;
        };
        
        $.getMID = function(option)
        {
            
            $.ajax({
                        url:"../Helper/lock.php",
                        type: 'post',
                        dataType: 'json',
                        async:false,
                        data:{data:function(){return 'getMID';},
                             ubCard:function(){return infoUBValue;}},
                        success:function(data){
                            
                            tmpMid = JSON.stringify(data);
                            if(option==0)
                            {
 
                                $.checkTerminaServicesSession(1);//for LOGIN    
                            }
                            else if(option==2)//for login (phase 3)
                            {
                                $.checkPin(tmpMid);
                            }
                            else
                            {
                             
                                $.checkTerminaServicesSession(0); //for Conversion/Sign up
                            } 
                        },
                        error: function(XMLHttpRequest, e){
                            alert(XMLHttpRequest.responseText);
                        }
            });
            
        };
        
         $.resetVal = function(option)
        {
            if(option==0)
            {
                infoUBValue="";
                infoPassValue="";
                infoPinValue="";
                infoPinValue="000000";
                infoRPinValue="";
            }
            else if(option==1)
            {
                tmpMid = "";
                tmpPin="";
                tmpServiceID="";
                tmpUB="";
                tmpUBServicePassword="";
                tmpUBserviceLogin="";
                tmpUserMode="";
                tmpServiceGroupID="";
            }
            else if(option==2)
            {
                $("#ubfield").val("");
                $("#pinfield").val("");
                $("#pinfield").val("000000");
                $("#newPinField").val('');
                $("#rnewPinField").val('');
                
                $("#changePINUB").val("");
                $("#changeUserPIN").val("");
                
                $("#changeNPIN").val("");
                $("#changeRNPIN").val("");  
  
            }
            else
            {
                $("#formCardNumber").val("");
                $("#formPassword").val("");
                $("#formNewPIN").val('');
                $("#formRePIN").val('');
            }
        };
        
        $.lytBox = function(val,w,h,iSet){
            
            if(iSet==true){
//              $('#blackOut').css('filter','alpha(opacity=70)');   
              $("div#blackOut").show();
              $("div#whiteBox").show();
    
              $("#whiteBox").css("width", w+"px");
              $("#whiteBox").css("height", h+"px");
              $("#whiteBox").css("margin-top:", (w/2)+"px");
              $("#whiteBox").css("margin-left", (h/2)+"px");
                
              var divHeight = $("div#whiteBox").height();
              var divWidth = $("div#whiteBox").width();
          
          
              divHeight += 88;
              var marginTop = -(divHeight /2);
              divWidth += 30 ;
              var marginLeft = -(divWidth / 2);
              

              marginTop += "px";
              marginLeft +="px";
            
              $("#whiteBox").css("margin-top", marginTop);
              $("#whiteBox").css("margin-left", marginLeft);

              $("#whiteBox").html(val);
              
          }else
          {
              $("div#blackOut").hide();
              $("div#whiteBox").hide();
          }
            
        };
        
});