

 $.buttonInfo = function(func,inPut)
{
              
            var Field = "";        
            var maxInput = "";
            switch(inPut){
                case 0:
                    Field = $("#formCardNumber"); 
                    maxInput = maxUBcardVal;
                    break;
                case 1:
                     Field = $("#formPassword");
                     maxInput = maxPassVal;
                     break;
                case 2:
                     Field = $("#formNewPIN");
                     maxInput = maxPinVal;
                    break;
                case 3:
                     Field = $("#formRePIN");
                     maxInput = maxPinVal;
                     break;
                 
                case 4:
                     Field = $("#ubfield");
                     maxInput = maxUBcardVal;
                     break;
                 case 5:
                     Field = $("#pinfield");
                     maxInput = maxPinVal;
                     break;
                     
                case 6:
                     Field = $("#newPinField");
                     maxInput = maxPinVal;
                     break;
                 case 7:
                     Field = $("#rnewPinField");
                     maxInput = maxPinVal;
                     break;    
                 
                case 8:
                    Field = $("#changePINUB");
                    maxInput = maxUBcardVal;  
                    break;
                
                case 9:
                    Field = $("#changeUserPIN");
                    maxInput = maxPinVal;
                    break;
                    
                case 10:
                    Field = $("#changeNPIN");
                    maxInput = maxPinVal;
                    break;
                 
                case 11:
                    Field = $("#changeRNPIN");
                    maxInput = maxPinVal;
                    break;
                    
                 default:
                     break;
            }
            
          
                        $('body').off('click', 'button').on('click','button',function(){
                               
                               var val="";
                               
                               val=$(this).attr("value");
                               if(val==-1)
                               {
                                     Field.val("");
                                     if(inPut==0){
                                        infoUBValue="";Field.trigger('change');
                                        $.resetVal(0);
                                        $.resetVal();
                                        capsLock = true;
                                        $.populatePads();
                                        $("button[value=-3]").hide();
                                        $.buttonInfo($.infoUBValue,0);
                                     }
                                     if(inPut==1){infoPassValue="";Field.trigger('change');}
                                     if(inPut==2){infoPinValue="";Field.trigger('change');}
                                     if(inPut==3){infoRPinValue="";Field.trigger('change');}
                                     if(inPut==4){infoUBValue="";Field.trigger('change');}
                                     if(inPut==5){infoPinValue="";Field.trigger('change');}
                                     if(inPut==6){infoPinValue="";Field.trigger('change');}
                                     if(inPut==7){infoRPinValue="";Field.trigger('change');}
                                     
                                     if(inPut==8){infoUBValue="";Field.trigger('change');}
                                     if(inPut==9){infoPinValue="";Field.trigger('change');}
                                     if(inPut==10){infoPinValue="";Field.trigger('change');}
                                     if(inPut==11){infoRPinValue="";Field.trigger('change');}
   
                               }
                               else if(val==-2)
                               {
                 
                                   if(inPut!=4&&inPut!=5&&inPut!=6&&inPut!=7&&inPut!=8
                                            &&inPut!=9&&inPut!=10&&inPut!=11)
                                   {
                                    
                                        
                                        //$.checkUBCard(0);//for conversion function
                                        $.getMID(1);
                                       
                                   }
                                   else if(inPut!=6&&inPut!=7)
                                   {
                                     if(inPut==4)
                                     {
                                        $("#buttcont").hide();
                                        $("#pinfield").trigger('click');
                                         
                                     }
                                     else if(inPut==8)
                                     {
                                        $("#buttcont2").hide();
                                        $("#changeUserPIN").trigger('click');

                                     }
                                     else if(inPut==10)
                                      {
                                          $("#buttcont2").hide();
                                          $("#changeRNPIN").trigger('click'); 
                                           
                                      }
                                     else
                                     {
                                         $("#buttcont").fadeOut(500);
                                         $("#buttcont2").fadeOut(500);
                                     }
                                      
                                   }
                                   else
                                   {
                                       if(inPut==6)
                                        {
                                        $("#buttcont").hide();
                                        $("#rnewPinField").trigger('click');   
                                        }
                                        else
                                        {
                                            $("#buttcont").hide();
                                            pinNomination=true;
                                        }
                                   }
                                
                               }
                               else if(val==-3)
                               {
                                   
                                    if(inPut!=0){
                                        
                                        if(isAlwaysCaps==true)
                                        {    
                                            
                                          capsLock = false;  
                                          isAlwaysCaps = false;
                                          $.populatePads();
                                            $("tr.fourth").css({
                                             "position":"absoulte",
                                             "margin-left":"0px"
                                          });
                                        }
                                        else
                                        {
                                            capsLock=!capsLock;
                                            $.populatePads(); 
                                                $("tr.fourth").css({
                                                  "position":"absoulte",
                                                  "margin-left":"0px"
                                               });
                                            capsLock=!capsLock; 
                                        }
                                   }
                               }
                               else if(val==-4)
                               {
                                   if(inPut==0)
                                   { 
                                      
                                      Field.val(Field.val().substring(0, Field.val().length-1));
                                      infoUBValue = infoUBValue.substring(0,infoUBValue.length-1); 
                                     
                               
                                   }
                                   if(inPut==1)
                                   {
                                      Field.val(Field.val().substring(0, Field.val().length-1));
                                      infoPassValue = infoPassValue.substring(0,infoPassValue.length-1); 
                                   }
                                   
                                   if(inPut==4)
                                   {
                                      Field.val(Field.val().substring(0, Field.val().length-1));
                                      infoUBValue = infoUBValue.substring(0,infoUBValue.length-1); 
                                   }
                                   
                                   if(inPut==8)
                                   {
                                      Field.val(Field.val().substring(0, Field.val().length-1));
                                      infoUBValue = infoUBValue.substring(0,infoUBValue.length-1); 
                                   }
                                   
                                   Field.trigger('change');
                                   
                               }
                               else
                               {
                               if(Field.val().length < maxInput) 
                               {
                                    func(val);
                                    Field.trigger('change');
                                     if(inPut==1)
                                      {
                                        $.populatePads();
                                            $("tr.fourth").css({
                                               "position":"absoulte",
                                               "margin-left":"0px"
                                           });
                                      }
                                    if(infoUBValue!=""&&infoPinValue!=""&&infoPassValue!=""&&infoRPinValue!="")
                                    {
                                         $("button[value=-2]").attr("disabled",false);
                                    }
                               }
                           }
                               
                       
                           
                        });
                    
                        Field.trigger('change');
        };