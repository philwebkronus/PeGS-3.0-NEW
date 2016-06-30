$(document).ready(function(){




$.getMaxAttempts();
     
//Login for ewallet and for conversion disabled

 //$("#ubfield").attr('disabled',true);
 //$("#pinfield").attr('disabled',true);
 //$("#Login").attr('disabled',true);
 //$("#signUP").attr('disabled',false);
 
 //*********************************

//$("#pinfield").attr('disabled',true);

$("#signUP").click(function(){

        $.checkTerminalType();

        if(terminalType==0)
        {
            $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
            infoPinValue="";
            infoPinValue="000000";
        }
        else if(terminalType==1)
        {
            $.prompt("Invalid terminal. Terminal should be setup as genesis");
            infoPinValue="";
            infoPinValue="000000";
        } else
        {
        //showPinBox(true);
         $.resetVal(0);
         $.resetVal(2);
         $.createKEW();
        //createForm();
        }
 });
 
 
 //change pin
 
 $("#changeuserPin").click(function(){
    //localStorage.clear();
    //change PIN
        $.createChangePin();
        $.resetVal(0);
        $.resetVal(2);
     
 });
 
 
 
$('body').on('click',"#formCardNumber",function(){
    
    capsLock = true;
    $.populatePads();
    $("button[value=-3]").hide();
    $.buttonInfo($.infoUBValue,0);
   
});

$('body').on('click',"#formPassword",function(){
    capsLock = false;
    $.populatePads();
     $("tr.fourth").css({
       "position":"absoulte",
       "margin-left":"0px"
    });
    $("button[value=-3]").show();
    $.buttonInfo($.infoPassValue,1);

});

$('body').on('click',"#formNewPIN",function(){
    
    $.populatePads2();
    $.buttonInfo($.infoPinValue,2);

});

$('body').on('click',"#formRePIN",function(){
    
    $.populatePads2();
    $.buttonInfo($.infoRPinValue,3);

});


$("#ubfield").click(function(){

         
         $.populatePads3();
         $.buttonInfo($.ubValue,4);
         
});


$("#pinfield").click(function(){

    
        $.populatePads4();
         
         //$("#pinfield").val('');
         $.buttonInfo($.PinValue,5);
         
        
});

$('body').on("click","#Login",function(){

   if($("#pinfield").val() != ''){
       $.checkTerminalType();
       
        if(terminalType==0)
        {
            $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
            infoPinValue="";
            infoPinValue="000000";
        }
        else if(terminalType==1)
        {
            $.prompt("Invalid terminal. Terminal should be setup as genesis");
            infoPinValue="";
            infoPinValue="000000";
            $.resetVal(0);
            $.resetVal(1);
            $.resetVal(2);
            $.resetVal();
            $("#ubfield").trigger('change');

    } else
        {
            
             $.getMID(0);// 0 for login
        }
   } else {
       $.prompt("Please input PIN");
       infoPinValue="";
       $.showRegular(false);
   }
    //$.showLobby2(true);

});


$("#newPinField").click(function(){
         $.populatePads4();
         
         infoPinValue="";
         infoRPinValue="";
         $("#newPinField").val("");
         $("#rnewPinField").val("");
         $.buttonInfo($.NewPinValue,6);
         $("#buttcont").css('margin-top','75px');
        
         
});

$("#rnewPinField").click(function(){
        
         $.populatePads4();
         infoRPinValue="";
         $("#rnewPinField").val("");
         $.buttonInfo($.RNewPinValue,7);

         
});

//validation of inputs

$('body').on('change',"#formCardNumber",function(){
   
   
   
    if(infoUBValue!="")
    {
        $("#formPassword").removeAttr('disabled');
        $("button[value=-4]").attr("disabled",false);

    }
    else
    {
        $("#formPassword").attr('disabled',true);
        $("#formNewPIN").attr('disabled',true);
        $("#formRePIN").attr('disabled',true);
        set = true;
        $("button[value=-2]").attr("disabled",set);
        $("button[value=-4]").attr("disabled",true);
       

    }
   
});

$('body').on('change',"#formPassword",function(){
    
   
    if(infoPassValue!="")
    {
        $("#formNewPIN").removeAttr('disabled');
    }
    else
    {
        $("#formNewPIN").attr('disabled',true);
        $("#formRePIN").attr('disabled',true);
        set = true;
        $("button[value=-2]").attr("disabled",set);

    }
    

});

$('body').on('change',"#formNewPIN",function(){
    
  
   if(infoPinValue!="")
    {
        $("#formRePIN").removeAttr('disabled');
    }
    else
    {
        $("#formRePIN").attr('disabled',true);
        set = true;
        $("button[value=-2]").attr("disabled",set);

    }
    
});

$('body').on('change',"#formRePIN",function(){
   
   if(infoRPinValue!="")
    {
         set = false;
         
         $("button[value=-2]").attr("disabled",set);
    }
    else
    {
        set = true;
      
        $("button[value=-2]").attr("disabled",set);
    }
    
  
});

$('body').on('change',"#ubfield",function(){
   
    if(infoUBValue!="")
    {
        $("#pinfield").attr('disabled',false);
        $("button[value=-4]").attr("disabled",false);
        $("button[value=-2]").attr("disabled",false);

    }
    else
    {
        $("#pinfield").attr('disabled',true);
        $("button[value=-4]").attr("disabled",true);
        $("button[value=-2]").attr("disabled",true);

    }
    
});

$('body').on('change',"#pinfield",function(){
    
    if(infoPinValue!="")
    {
         $("button[value=-2]").attr("disabled",false);
    }
    else
    {
         $("button[value=-2]").attr("disabled",true);
    }
    
});

$("#casino").click(function(){
 $.checkTerminalType();
    
   if(terminalType==2)
   {
        $.checkSession2();
   } else if(terminalType == 0){
        $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
        infoPinValue="";
        infoPinValue="000000";
        return false;
   }
   else
   {
    $.prompt("Invalid terminal. Terminal should be setup as genesis");
    $.resetVal(0);
    $.resetVal(1);
    $.resetVal(2);
    $.resetVal();
    infoPinValue="";
    infoPinValue="000000";
   }
});

$("#casinomm").click(function(){
 $.checkTerminalType();
 
   if(terminalType==2)
   {
        $.checkSession2();
   } else if(terminalType == 0){
        $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
        infoPinValue="";
        infoPinValue="000000";
        return false;
   }
   else
   {
    $.prompt("Invalid terminal. Terminal should be setup as genesis");
    $.resetVal(0);
    $.resetVal(1);
    $.resetVal(2);
    $.resetVal();
    infoPinValue="";
    infoPinValue="000000";
   }

});

$("#casinovv").click(function(){
 $.checkTerminalType();
 
   if(terminalType==2)
   {
        $.checkSession2();
   } else if(terminalType == 0){
        $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
        infoPinValue="";
        infoPinValue="000000";
        return false;
   }
   else
   {
    $.prompt("Invalid terminal. Terminal should be setup as genesis");
    $.resetVal(0);
    $.resetVal(1);
    $.resetVal(2);
    $.resetVal();
    infoPinValue="";
    infoPinValue="000000";
   }

});

$(".boxclose").live('click', function(){
   //alert("box");
   if(!$('#changePinModule').is(':visible')){
       
          $("#blackwrapper").hide();
          $("#prompt").hide();
       
        if (!$('div#kewID').is(':visible'))
        {
            $("#blackwrapper").hide();
            $("#prompt").hide();
        }
        else
        {
            if (!$('div#prompt').is(':visible'))
            {
                $.lytBox("","","",false);
                $("#whiteBox").css("background-color", "");
                $("#whiteBox").css("background", "rgba(0, 0, 0, .8)");
                $("#whiteBox").css("background", "transparent");
                $("#whiteBox").css("filter", "progid:DXImageTransform.Microsoft.gradient(startColorstr=#B3181818,endColorstr=#B3181818)");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal(2);
                
            }
            else
            {
                $("#blackwrapper").hide();
                $("#prompt").hide();
            } 
        }
    }
    else
    {
        if (!$('div#prompt').is(':visible'))
            {
                $.lytBox("","","",false);  
                $("#whiteBox").css("background-color", "");
                $("#whiteBox").css("background", "rgba(0, 0, 0, .8)");
                $("#whiteBox").css("background", "transparent");
                $("#whiteBox").css("filter", "progid:DXImageTransform.Microsoft.gradient(startColorstr=#B3181818,endColorstr=#B3181818)");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal(2);
            }
            else
            {
                
                $("#blackwrapper").hide();
                $("#prompt").hide();
            } 
    }
    
});
$('body').on('click',"#isagree",function(){
   
   
    if($("#isagree").is(':checked'))
    {
        $("#formCardNumber").attr('disabled',false);
        $("#formPassword").attr('disabled',false);
        $("#formNewPIN").attr('disabled',false);
        $("#formRePIN").attr('disabled',false);
        $("button").attr('disabled',false);
        $.buttonInfo($.infoUBValue,0);
    }
    else
    {
        $("#formCardNumber").attr('disabled',true);
        $("#formPassword").attr('disabled',true);
        $("#formNewPIN").attr('disabled',true);
        $("#formRePIN").attr('disabled',true);
        $("button").attr('disabled',true);  
    }
   
});

//$('body').on("dblclick",".shift",function(){
//    
//   capsLock=true;
//   isAlwaysCaps = true;
//    
//});


//changepin


$('body').on('click',"#changePINUB",function(){
    
  
     $.populatePads5();
     $.buttonInfo($.changePINUB,8);
     
           
});


$('body').on('change',"#changePINUB",function(){
   
    if(infoUBValue!="")
    {
        $("#changeUserPIN").attr('disabled',false);
        $("button[value=-4]").attr("disabled",false);
        $("button[value=-2]").attr("disabled",false);

    }
    else
    {
        $("#changeUserPIN").attr('disabled',true);
        $("button[value=-4]").attr("disabled",true);
        $("button[value=-2]").attr("disabled",true);
    }
    
});


$('body').on('click',"#changeUserPIN",function(){
    
         $.populatePads6();
         infoPinValue="";
         $("#changeUserPIN").val('');
         $.buttonInfo($.changePINnom,9);
     
          
});


$('body').on('change',"#changeUserPIN",function(){
    
    if(infoPinValue!="")
    {
       
         $("button[value=-2]").attr("disabled",false);
    }
    else
    {
       
         $("button[value=-2]").attr("disabled",true);
    }
    
});


//new Pin inputs
$('body').on('click',"#changeNPIN",function(){
    
    
         $.populatePads6();
         
         infoPinValue="";
         infoRPinValue="";
         $("#changeNPIN").val("");
         $("#changeRNPIN").val("");
         $.buttonInfo($.changeNewPIN,10);
         
         if(screen_width >= 800 && screen_width <= 1000) {
            $('#buttcont2').css("margin-top","-127px");
        } else {
            $('#buttcont2').css("margin-top","-135px");
        }
    
          
});

$('body').on('change',"#changeNPIN",function(){
    
    if(infoPinValue!="")
    {
         $("#changeRNPIN").attr('disabled',false);
         $("button[value=-2]").attr("disabled",false);
    }
    else
    {
         $("#changeRNPIN").attr('disabled',true);
         $("button[value=-2]").attr("disabled",true);
    }
    
});

$('body').on('click',"#changeRNPIN",function(){
    
    
         $.populatePads6();
         
         infoRPinValue="";
         $("#changeRNPIN").val("");
         $.buttonInfo($.changeNewRPIN,11);

          
});


$('body').on('change',"#changeRNPIN",function(){
    
    if(infoRPinValue!="")
    {
         $("button[value=-2]").attr("disabled",false);
    }
    else
    {
         $("button[value=-2]").attr("disabled",true);
    }
    
});



$('body').on("click","#confirmchangePIN",function(){
    
   
    $.checkUBCard(2);//for change pin parameter != 1 or 0
    
});

$('body').on("click","#confirmchangePIN2",function(){
    
   
    $.updatePin();
    
});


$(document.body).mousedown(function(event) {
    var target = $(event.target);
    if (!target.parents().andSelf().is('#ubfield')
       && !target.parents().andSelf().is('#pinfield') && !target.parents().andSelf().is('#left')  
       && !target.parents().andSelf().is('#changePinModule')) { 
        
        $("#pinfield").blur();
        if($("#pinfield").val() == ""){
            $("#pinfield").val("");
            $("#pinfield").val("000000");
            infoPinValue="000000";
        }
        $("#ubfield").blur();
        
        if ($('div#buttcont').is(':visible'))
        {
            $('#buttcont').hide();
            
        }
        if($('div#buttcont2').is(':visible'))
        {
            $('#buttcont2').hide();
        }
    }
});

    $('body').on("click","#nonPlatinum",function(){
        $.checkUBSession(0);
//        $.resetVal(0);
//        $.resetVal(1);
//        $.resetVal(2); 
    });
    
    $("#casinomm_nonesafe").click(function(){
        $.checkTerminalType();

          if(terminalType==2)
          {
               $.checkSession2();
          } else if(terminalType == 0){
               $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
               return false;
          }
          else
          {
           $.prompt("Invalid terminal. Terminal should be setup as genesis");
           $.resetVal(0);
           $.resetVal(1);
           $.resetVal(2);
           $.resetVal();
          }

    });

    $('body').on("click","#platinum",function(){
        $.checkUBSession(1);
//        if(tmpServiceID==20)
//            $.launchGame(tmpServiceID+".2",tmpUBserviceLogin,tmpUBServicePassword);
//        else
//            $.launchGame(tmpServiceID,tmpUBserviceLogin,tmpUBServicePassword);
//        $.resetVal(0);
//        $.resetVal(1);
//        $.resetVal(2); 
    });

    $('body').on("click","#endSession",function(){        
        //$.endCurrentSession();
        $.checkUBSession();
        $.resetVal(0);
        $.resetVal(1);
        $.resetVal(2);
        jQuery.fancybox.close();
        $.getTerminalUserMode();
    });  

});
