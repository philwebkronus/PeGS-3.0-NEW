/** Created by: edson perez
 *  Created on: june 2, 2011
 *  Function: jquery ajax call w/ json
 **/

        jQuery(document).ready(function(){
              //this will disable all cut, copy, paste on all textbox

              jQuery('#txtpassword').live("cut copy paste",function(e) {
                  e.preventDefault();
              });
              
              //check form that do not have class txtmembership
              var allHaveClass2 = $('.frmmembership input:not(.txtmembership)').length == 0;
              
              //check if form has no txtmembership class then avoid paste event on its textboxes,
              //else allow copy paste on textboxes that has txtmembership class
              if(allHaveClass2){
                   $(":text").live("cut copy paste",function(e) {
                    e.preventDefault();
                   });
              }
              else {
                  $(":text").live("cut",function(e) {
                    e.preventDefault();
                   });
              }
          });
          
            function sendIslandID(str){
                var siteURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessSiteManagement.php";
                $.post(siteURL,{sendIslandID: str},
                function(data){
                    var options = $("#cmbregions");
                    $.each(data, function() {
                        options.append($("<option />").val(this.RegionID).text(this.RegionName));
                    });
                }, "json");
            }

            function sendRegionID(str){
                var siteURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessSiteManagement.php";
                $.post(siteURL,{sendRegionID: str},
                function(data){
                    var options = $("#cmbprovinces");
                    $.each(data, function() {
                        options.append($("<option />").val(this.ProvinceID).text(this.ProvinceName));
                    });
                }, "json");
            }

            function sendProvID(str){
                var siteURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessSiteManagement.php";
                $.post(siteURL, {sendProvID: str},
                function(data){
                    var options = $("#cmbcity");
                    $.each(data, function(){
                        options.append($("<option />").val(this.CityID).text(this.CityName));
                    });
                }, "json");
            }

            function sendCityID(str){
                var siteURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessSiteManagement.php";
                $.post(siteURL, {sendCityID: str},
                function(data){
                    var options = $("#cmbbrgy");
                    $.each(data, function(){
                        options.append($("<option />").val(this.BarangayID).text(this.BarangayName));
                    });
                }, "json");
            }

             function sendSiteID(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAppSupport.php";
                $.post(suppURL,{sendSiteID: str},
                function(data){
                    var terminal = $("#cmbterm");
                    $.each(data, function() {
                        terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                    });
                }, "json");
            }
            
            //this is for cashier per site
            function cashiersiteID(str)
            {
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAppSupport.php";
                $.post(suppURL,{cashiersiteID: str},
                function(data){
                    var cashier = $("#cmbcashier");          
                    $.each(data, function() {
                        cashier.append($("<option />").val(this.AID).text(this.UserName));                        
                    });
                }, "json");
            }
            
            //check if with passkey
            function checkwithpasskey(str)
            {
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAppSupport.php";
                $.post(suppURL,{cashierpasskey: str},
                function(data){

                     $.each(data, function(q, passkey) {
                        if(passkey > 0){
                            document.getElementById('optyes').checked=true;                         
                        }
                        else{                          
                            document.getElementById('optno').checked=true;
                        }
                    });
                }, "json");
            }

            //this is for serviceassignment
            function sendSiteID1(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessTerminalMgmt.php";
                $.post(suppURL,{sendSiteID1: str},
                function(data){                   
                    var terminal = $("#cmbterminals");
                 
                    $.each(data, function(key,value) {
                        if(this.TerminalCode != 'undefined')
                        {
                            terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));                        
                        }    
                        else{
                            alert("pass");
                        }
                        
                    });
                }, "json");
            }
            
           
           //this is for casino service view 
            function sendServiceGroupID(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "Kronus_UB/admin.ewallet/public/views/process/ProcessCasinoMgmt.php";
                $.post(suppURL,{sendServiceGroupID: str},
                function(data){                   
                    var service = $("#cmbservice");
                 
                    $.each(data, function(key,value) {
                        if(this.ServiceName != 'undefined')
                        {
                            service.append($("<option />").val(this.ServiceID).text(this.ServiceName));                        
                        }    
                        else{
                            alert("pass");
                        }
                        
                    });
                }, "json");
            }
            
            //this is for account views
            function sendAccID(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAccManagement.php";
                $.post(suppURL,{sendAccID: str},
                function(data){                
                    var acc = $("#cmbacc");
                    $.each(data, function() {
                        acc.append($("<option />").val(this.AID).text(this.UserName));                        
                    });
                }, "json");
            }
            
             //this is for siteremmitance
            function sendRemitID(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessTopUp.php";
                $.post(suppURL,{sendRemitID: str},
                function(data){                   
                    var remit = $("#cmbsiteremit");
                    $.each(data, function() {
                        remit.append($("<option />").val(this.SiteRemittanceID).text(this.SiteRemittanceID));                        
                    });
                }, "json");
            }

       //this is for siteremmitance for verified remmittances
            function sendRemitID2(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessTopUp.php";
                $.post(suppURL,{sendRemitID2: str},
                function(data){                   
                    var remit = $("#cmbsiteremit");
                    $.each(data, function() {
                        remit.append($("<option />").val(this.SiteRemittanceID).text(this.SiteRemittanceID));                        
                    });
                }, "json");
            }
            
            //this is for CS, manual redemption
            function sendSiteID2(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessCSManagement.php";
                $.post(suppURL, {sendSiteID2: str},
                function(data){
                    var terminal = $("#cmbterminal");
                    
                    $.each(data, function() {
                        terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                    });
                }, "json");
            }
            
            //send terminal id to get providers list
            function sendTerminalID(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessCSManagement.php";
                $.post(suppURL, {sendTerminalID: str},
                function(data){
                    var services = $("#cmbservices");
                    $.each(data, function() {
                        services.append($("<option />").val(this.ServiceID).text(this.ServiceName));
                        
                    });
                }, "json");
            }            
            
            //send terminal name to get providers list
            function sendterminalname(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAppSupport.php";
                $.get(suppURL, {cmbterminal: str},
                function(data){                    
                    $.each(data, function() {
                          jQuery("#txttermname").text(data.TerminalName);
                        
                    });
                }, "json");
            }            
            
            /**
             * ADDED ON JUNE 11, 2012 FOR CS E-CITY TRACKING
             * 
             */
            function sendSiteID_cs(str){
                var suppURL = window.location.protocol + "//" + window.location.host + "/" + "kronus_ub/admin.ewallet/public/views/process/ProcessAppSupport_cs.php";
                $.post(suppURL,{sendSiteID: str},
                function(data){
                    var terminal = $("#cmbterm");
                    $.each(data, function() {
                        terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                    });
                }, "json");
            }


