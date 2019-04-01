var alphabetArray = ['Q','W','E','R','T','Y','U','I','O','P',
                             'A','S','D','F','G','H','J','K','L',
                             'Z','X','C','V','B','N','M'];

                         
var number = [0,1,2,3,4,5,6,7,8,9];                     
 
 
 
 $.populatePads = function(){
            
                    
                  var i;
                  var tb = '<table cellpadding="0"><tr>';

                   for(i=1;i<=10;i++){
                       if(i<=9){
                       tb+='<td><button id=padbut value='+i+'>'+i+'</button></td>';
                       }
                       if(i>9&&i<=10){
                         tb+='<td><button id=padbut value=0>0</button></td>';  
                       }        
                   }
                   tb+='</tr><tr><td>&nbsp;</td></tr><tr>';
               
                if(capsLock==true){
                       
                    for(i=0;i<=25;i++){
                        if(i!=10&&i!=19){
                            tb+='<td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';  
                        }else{
                            
                                if(i>=19){
                                tb+='<td ><button id=padbut value=-2 class="dne">OK</button></td>'
                                   +'</tr><tr class="fourth"><td><button value=-3 class="shift">SHIFT</button></td><td>'
                                   +'<button class="clr" value=-1>CLEAR</button></td>'
                                    +'<td colspan="1"><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                                }else{
                                tb+='</tr><tr><td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                                }
                        }
                    }
                }else{
                    for(i=0;i<=25;i++){
                        if(i!=10&&i!=19){
                            tb+='<td><button id=padbut value='+alphabetArray[i].toLowerCase()+'>'+alphabetArray[i].toLowerCase()+'</button></td>';  
                        }else{
                            if(i>=19){
                                tb+='<td ><button id=padbut value=-2 class="dne">OK</button></td>'
                                   +'</tr><tr style="margin-left:0px;"><td><button  value=-3 class="shift">SHIFT</button>'
                                   +'</td><td ><button class="clr" value=-1>CLEAR</button></td>'
                                    +'<td colspan="1"><button id=padbut value='+alphabetArray[i].toLowerCase()+'>'+alphabetArray[i].toLowerCase()+'</button></td>';
                            }else{
                                tb+='</tr><tr><td><button id=padbut value='+alphabetArray[i].toLowerCase()+'>'+alphabetArray[i].toLowerCase()+'</button></td>';
                         }
                        }
                    }
                }
                tb+='<td><button id=padbut value=-4 class="back">◄</button></td>'
                      +'</tr></table>';

                    
                    $('#infoBut').html(tb);
                    
                    $.keyBoardCss1();

        };
        
        $.populatePads2 = function(){
            $.generateRandom();
            var i;
            var tb = '<center><table><tr>';
                
                for(i=1;i<=9;i++){ 
                   if(i!=4&&i!=7){
                       tb+='<td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }else{
                        tb+='</tr><tr><td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }
                }
                  tb+='<tr><td><button class="clr" id=padbut value=-1>CLEAR</button></td>'
                      +'<td><button id=padbut value='+number[0]+'>'+number[0]+'</button></td></td>'+
                       '<td><button class="dne" id=padbut value=-2>OK</button></td></tr></table></center>';


               

                   $('#infoBut').html(tb);
                   $.keyBoardCss2();
        };
        
        $.populatePads3 = function(){
            
                var i;
                var tb = '<center><table cellpadding="0"><tr>';



               for(i=1;i<=10;i++){
                   if(i<=9){
                   tb+='<td><button id=padbut value='+i+'>'+i+'</button></td>';
                   }
                   if(i>9&&i<=10){
                     tb+='<td><button id=padbut value=0>0</button></td>';  
                   }        
               }
               tb+='</tr><tr><td>&nbsp;</td></tr><tr>';
                for(i=0;i<=25;i++){
                    if(i!=10&&i!=19){
                        tb+='<td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';  
                    }else{
                        if(i>=19){
                            tb+='</tr><tr class="third"><td><button class="clr" value=-1>CLEAR</button></td>'+
                                    '<td ><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                        }else{
                            tb+='</tr><tr class="second"><td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                     }
                    }

                }

                  tb+='<td><button id=padbut value=-2 class="dne">OK</button></td>'
                          +'<td><button id=padbut value=-4 class="back">◄</button></td>'
                          +'</tr></table></center>';
                  
                  $('#buttcont').html(tb);
                  
                  $.keyBoardCss3();
                  
                  $("#buttcont").fadeIn(500);

        };
        
        $.populatePads4 = function(){
            $.generateRandom();
            var i;
            var tb = '<center><table><tr>';
                
                for(i=1;i<=9;i++){ 
                   if(i!=4&&i!=7){
                       tb+='<td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }else{
                        tb+='</tr><tr><td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }
                }
                  tb+='<tr><td><button class="clr" id=padbut value=-1>CLEAR</button></td>'
                      +'<td><button id=padbut value='+number[0]+'>'+number[0]+'</button></td></td>'+
                       '<td><button class="dne" id=padbut value=-2>OK</button></td></tr></table></center>';

               $('#buttcont').html(tb);
              
              $.keyBoardCss4();
              
              $("#buttcont").fadeIn(500);
        };
        
        
        
        
        //for change pin module (keyboard and pin)
        $.populatePads5 = function()
        {
            var i;
                var tb = '<center><table cellpadding="0"><tr>';
               for(i=1;i<=10;i++){
                   if(i<=9){
                   tb+='<td><button id=padbut value='+i+'>'+i+'</button></td>';
                   }
                   if(i>9&&i<=10){
                     tb+='<td><button id=padbut value=0>0</button></td>';  
                   }        
               }
               tb+='</tr><tr><td>&nbsp;</td></tr><tr>';
                for(i=0;i<=25;i++){
                    if(i!=10&&i!=19){
                        tb+='<td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';  
                    }else{
                        if(i>=19){
                            tb+='</tr><tr class="third"><td><button class="clr" value=-1>CLEAR</button></td>'+
                                    '<td ><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                        }else{
                            tb+='</tr><tr class="second"><td><button id=padbut value='+alphabetArray[i]+'>'+alphabetArray[i]+'</button></td>';
                     }
                    }

                }

                  tb+='<td><button id=padbut value=-2 class="dne">OK</button></td>'
                          +'<td><button id=padbut value=-4 class="back">◄</button></td>'
                          +'</tr></table></center>';
                  
                  
                  $('#buttcont2').html(tb);
                  
                  $.keyBoardCss5();
                  
                  
                  
                  $("#buttcont2").fadeIn(500);
                  
        };
    
        $.populatePads6 = function()
        {
          
            $.generateRandom();
            var i;
            var tb = '<center><table><tr>';
                
                for(i=1;i<=9;i++){ 
                   if(i!=4&&i!=7){
                       tb+='<td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }else{
                        tb+='</tr><tr><td><button id=padbut value='+number[i]+'>'+number[i]+'</button></td>';
                   }
                }
                  tb+='<tr><td><button class="clr" id=padbut value=-1>CLEAR</button></td>'
                      +'<td><button id=padbut value='+number[0]+'>'+number[0]+'</button></td></td>'+
                       '<td><button class="dne" id=padbut value=-2>OK</button></td></tr></table></center>';

               $('#buttcont2').html(tb);
              
               $.keyBoardCss6();
               
               $("#buttcont2").fadeIn(500);
            
        };
        
        
        
        //re-arranging of numpad numbers
         $.generateRandom = function()
        {
         
            for (var i = number.length - 1; i > 0; i--) 
            {
                var j = Math.floor(Math.random() * (i + 1));
                var temp = number[i];
                number[i] = number[j];
                number[j] = temp;
            }
        
        };
        
        