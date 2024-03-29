    <?php 
    $pagetitle = "User Based Manual Redemption";  
    include "process/ProcessTopUp.php";
    include "header.php";
    $vaccesspages = array('5');
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
        
        $(document).ready(function(){

            var url = 'process/ProcessTopUp.php';
            
                        $('#cmbsite').live('change', function()
            {
                var site = $('#cmbsite').val();
                    if(site == "-1"){
                        $('#cmbterminal').empty();
                        $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbservices').empty();
                        $('#cmbservices').append($("<option />").val("-1").text("Please Select"));
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                        jQuery("#txttermname").text("");
                    }
                    else{
                        jQuery("#txttermname").text(" ");
                            sendSiteID2($(this).val()); // function to get TerminalID, TerminalCode

                            // this clears out sites data on combo boxes upon change of combo box
                            $('#cmbterminal').empty();
                            $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                            $('#cmbservices').empty();
                            $('#cmbservices').append($("<option />").val("-1").text("Please Select"));

                            //for displaying of site name
                            jQuery.ajax({
                                  url: url,
                                  type: 'post',
                                  data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                                  dataType: 'json',
                                  success: function(data){
                                      if(jQuery("#cmbsite").val() > 0)
                                      {
                                        jQuery("#txtsitename").text(data.SiteName+" / ");
                                        jQuery("#txtposaccno").text(data.POSAccNo);
                                      }
                                      else
                                      {   
                                        jQuery("#txtsitename").text(" ");
                                        jQuery("#txtposaccno").text(" ");
                                      }
                                  }
                                });
                    }
                 
            });

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
            
            $("#txtticketub").focus(function(){
                    $("#txtticketub").bind('paste', function(event) {
                        setTimeout(function(event) {
                            var data = $("#txtticketub").val();
                            if(!specialcharacter(data)){
                                $("#txtticketub").val("");
                                $("#txtticketub").focus();
                            }
                        }, 0);
                    });
            });
            
            $("#txtremarksub").focus(function(){
                    $("#txtremarksub").bind('paste', function(event) {
                        setTimeout(function(event) {
                            var data = $("#txtremarksub").val();
                            if(!specialcharacter(data)){
                                $("#txtremarksub").val("");
                                $("#txtremarksub").focus();
                            }
                        }, 0);
                    });
            });
            
            //oncheck event of with membership card checkbox
//            $("#checkbox1").change(function() 
//            {          
//                    
//                if($('#checkbox1').attr("checked")){
//                    document.getElementById('cmbsite').value="-1";
//                    document.getElementById('cmbterminal').value="-1";
//                    document.getElementById('txtcardnumber').value="";
//                    document.getElementById('txtsitename').textContent = "";
//                    document.getElementById('txtposaccno').textContent="";
//                    document.getElementById('txttermname').textContent="";
//                    
//                    document.getElementById('cmbsite').disabled=true;
//                    document.getElementById('cmbterminal').disabled=true;
//                    document.getElementById('txtcardnumber').disabled=false;
//                    document.getElementById('txtcardnumber').readOnly=false;
//                }
//                else{
//                    document.getElementById('cmbsite').value="-1";
//                    document.getElementById('cmbterminal').value="-1";
//                    document.getElementById('txtcardnumber').value="";
//                    document.getElementById('txtsitename').textContent="";
//                    document.getElementById('txtposaccno').textContent="";
//                    document.getElementById('txttermname').textContent="";
//                    
//                    document.getElementById('cmbsite').disabled=false;
//                    document.getElementById('cmbterminal').disabled=false;
//                    document.getElementById('txtcardnumber').disabled=true;
//                    document.getElementById('txtcardnumber').readOnly=true;
//                }
//                
//            });

            $('#cmbsite').live('change', function()
            {
                var cmbsite = document.getElementById('cmbsite').value;
                if(cmbsite == '-1'){
                    jQuery("#txttermname").text(" ");
                    $('#cmbterminal').empty();
                    $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                    jQuery("#txtsitename").text(" ");
                    jQuery("#txtposaccno").text(" ");
                }
                else{
                    jQuery("#txttermname").text(" ");
                    sendSiteID2($(this).val()); // function to get TerminalID, TerminalCode

                    // this clears out sites data on combo boxes upon change of combo box
                    $('#cmbterminal').empty();
                    $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                    //for displaying of site name
                    jQuery.ajax({
                          url: url,
                          type: 'post',
                          data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                          dataType: 'json',
                          success: function(data){
                              if(jQuery("#cmbsite").val() > 0)
                              {
                                jQuery("#txtsitename").text(data.SiteName+" / ");
                                jQuery("#txtposaccno").text(data.POSAccNo);
                              }
                              else
                              {   
                                jQuery("#txtsitename").text(" ");
                                jQuery("#txtposaccno").text(" ");
                              }
                          },
                          error : function(XMLHttpRequest, e)
                          {
                              alert(XMLHttpRequest.responseText);
                              if(XMLHttpRequest.status == 401)
                              {
                                  window.location.reload();
                              }
                           }
                    });
                }

            });

            $('#cmbterminal').live('change', function(){
                var terminal = jQuery("#cmbterminal").val();
                if(terminal != "-1")
                {
                    sendTerminalID($(this).val()); //// function to get ServiceID, ServiceCode

                    var terminalcode = ($(this).find("option:selected").text());
                    document.getElementById('txtcardnumber').value="";
                    document.getElementById('txtterminal').value = terminalcode;
                    //for displaying of terminal name
                    jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {cmbterminal: function(){return jQuery("#cmbterminal").val();}},
                        dataType: 'json',
                        success: function(data){
                            jQuery("#txttermname").text(data.TerminalName);
                            jQuery("#terminalcode").val(data.TerminalCode);
                            var services = jQuery("#cmbterminal").val();
                            var provider = ($(this).find("option:selected").text());
                            var url = 'process/ProcessTopUp.php';
                            document.getElementById('txtservices').value = provider;
                            if(services != "-1")
                            {
                            jQuery.ajax({
                                    url: url,
                                    type: 'post',
                                    data: {
                                        cmbservices: function(){return jQuery("#cmbterminal").val();}},
                                    dataType: 'json',
                                    success: function(data){
                                        if(data.loyaltyCard == null)
                                        {
                                            alert('User Based Manual Redemption: Cant get Membership Card Number');

                                        }
                                        else
                                        {
                                            jQuery("#txtcardnumber").val(data.loyaltyCard);
                                            document.getElementById('txtcardnumber').disabled=false;
                                            document.getElementById('txtcardnumber').readOnly = true
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
                            }
                            else{

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

                }
                else
                {
                    jQuery("#txttermname").text("");
                }
            });

            /**
            * Submit button for redeem
            * Validates if ticket id has value 
            * Submits form values
            */
            $('#btnWithdrawub').click(function()
            {
                if(document.getElementById('txtticketub').value == "" || 
                     (document.getElementById('txtticketub').value.indexOf(" ") == 0))
                {
                    alert("Blank or Ticket ID with leading space/s is/are not allowed");
                    $('#error').attr('style','display:block');
                    return false;
                }
                else
                {
                        $('#light1').hide();
                        $('#lightub').hide();
                        $('#fadeub').hide();
                        document.getElementById('loading').style.display='block';
                        document.getElementById('fade3').style.display='block';
                        jQuery.ajax(
                        {
                        url: url,
                        type: 'post',
                        data: {page: function(){ return "Withdraw";},
                                txtterminal: function() {return $("#txtterminal").val();},
                                terminalcode: function() {return $("#terminalcode").val()},
                                txtservices: function() {return $("#txtservices").val();},
                                cmbterminal: function() {return $("#cmbterminal").val();},
                                cmbsite: function() {return $("#cmbsite").val();},
                                chkbalance: function() {return $("#chkbalance").val();},
                                txtterminalid: function() {return $("#txtterminalid").val();},
                                txtserviceid: function() {return $("#txtserviceid").val();},
                                txtticketub: function() {return $("#txtticketub").val();},
                                txtamount2: function() {return $("#txtamount2").val();},
                                hdnamtwithdraw: function() {return $("#hdnamtwithdraw").val();}, 
                                txtremarksub: function() {return $("#txtremarksub").val();},
                                txtmid: function() {return $("#txtmid").val();},
                                txtisvip: function() {return $("#txtisvip").val();},
                                txtusermode: function() {return $("#txtusermode").val();},
                                txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                              },
                        dataType : 'json',  
                        success : function(data)
                        {
                            document.getElementById('loading').style.display='none';
                            document.getElementById('fade3').style.display='none';
                            if (data == "Please select Site ID") {
                                $('#light1').show();
                                $('#lightub').show();
                                $('#fadeub').show();
                            }
                            jQuery("#txttermname").text(" ");
                            $('#cmbterminal').empty();
                            $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                            $('#cmbsite').val("-1");
                            jQuery("#txtsitename").text(" ");
                            jQuery("#txtposaccno").text(" ");
                            jQuery("#txtcardnumber").val("");
                            jQuery("#txtticketub").val("");
                            jQuery("#txtremarksub").val("");
                            alert(data);
                        },
                        error : function(XMLHttpRequest, e)
                        {
                            alert(XMLHttpRequest.responseText);
                            if(XMLHttpRequest.status == 401)
                            {
                                window.location.reload();
                            }
                         }
                    });
                }
             });

             /**
             * Onclick : Displays the actual balance and membership card 
             *           information
             */
             $('#btnSubmit').click(function()
             {   
//                //check if checkbox is checked
//                var checkbox = document.getElementById('checkbox1').checked;
//                
//                //without membership card number
//                if(checkbox == false)
//                {
//                    if(document.getElementById('cmbsite').value == "-1")
//                    {
//                        alert("Please select site");
//                        document.getElementById('cmbsite').focus();
//                        return false;
//                    }
//                    if(document.getElementById('cmbterminal').value == "-1")
//                    {
//                        alert("Please select terminal");
//                        document.getElementById('cmbterminal').focus();
//                        return false;
//                    }
//
//                    //call method to display card and balance information
//                    getMembershipAndCasinoInfo(url);
//                }
//                //if membership card checkbox was enabled
//                else
//                {
                    if(document.getElementById('txtcardnumber').value == "" || document.getElementById('txtcardnumber').length == 0)
                    {
                        alert("Please Input Membership Card Number");
                        document.getElementById('txtcardnumber').focus();
                        return false;
                    }
                    
                    //call method to display card and balance information
                    getMembershipAndCasinoInfo(url);
//                }

            });
            $("#btnSubmitAmt").live('click', function(){
                if ($("#txtamtwithdraw").val().trim() !== "") {
                    var balance = parseFloat($("#txtamount2").val());
                    var amt_to_withdraw = parseFloat($("#txtamtwithdraw").val().trim());
                
                    //check if amount to withdraw is less than or equal balance. 
                    if (amt_to_withdraw <= balance) {
                        $("#hdnamtwithdraw").val(amt_to_withdraw);
                        document.getElementById('loading').style.display='none';
                        document.getElementById('lightamt').style.display='none';
                        document.getElementById('lightub').style.display='block';
                        document.getElementById('fadeub').style.display='block';
                    }
                    else {
                        alert('Amount should be less than or equal to Casino balance.');
                    }
                }
                else {
                    alert('Please enter amount.');
                }
            });
      });
              
      /*function for redeem button
       * basically hides card information lightbox and
       * shows withdraw form
       */
      function RedeemUB(bal,id,terminalid,isewallet)
      { 
            if(bal == 0){
              alert("Balance is zero");
              $('#loading').hide();
              $('#light3').hide();
              $('#fade3').hide();
            }
            else
            {
              var url = 'process/ProcessTopUp.php';
              jQuery.ajax({
                  url: url,
                  type: 'post',
                  dataType: 'json', 
                  data: {
                      page: function(){ return "CheckSiteID";},
                      txtcardnumber: function(){ return $("#txtcardnumber").val();},
                      txtserviceid: function(){ return id;},
                      txtusermode: function(){ return $("#txtusermode").val();}
                  },
                  success: function(data){
                    if (data.TransCode == 1) {
                        alert(data.TransMsg);
                    }
                    else {
                        $('#loading').hide();
                        $('#light3').hide();
                        $('#fade3').hide();

                        document.getElementById('txtterminalid').value = terminalid;
                        document.getElementById('txtserviceid').value = id;
                        document.getElementById('txtamount2').value = bal;
                        if (isewallet == 1) { //if serviceID is 20, add button
                            $("#txtamtwithdraw").val('');
                            document.getElementById('loading').style.display='none';
                            document.getElementById('light1').style.display='none';
                            document.getElementById('lightamt').style.display='block';
                            document.getElementById('fade3').style.display='block';
                        }
                        else {
                            document.getElementById('loading').style.display='none';
                            document.getElementById('light1').style.display='none';
                            document.getElementById('lightub').style.display='block';
                            document.getElementById('fadeub').style.display='block';
                        }
//                        if(data.TransRequestLogID > 0){
//                            $('#cmbsiteiddata').hide();
//                            return false;
//                        }
                    }
                  }
              });
              
            }

            }   
        
      //call method to display card and balance information
      function getMembershipAndCasinoInfo(url){
            //ajax call: Get membership information
            jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {page: function(){ return "GetLoyaltyCard";},
                           chkbalance: function() {return $("#chkbalance").val();},
                           txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                          },
                    dataType : 'json',
                    success : function(data)
                    {
                        
                        $.each(data, function(i,user)
                        {
                            var isewallet = this.IsEwallet;
                            
                            if(this.StatusCode == 9){
                                alert("Card is Banned");
                            }
                            //catch if membsership card number is invalid
                            if(this.CardNumber == null)
                            {
                                alert("Invalid Card Number");
                                $('#loading').hide();
                                $('#light3').hide();
                                $('#fade3').hide();
                                window.location.reload();
                            }
                            else
                            {
                                document.getElementById('loading').style.display='block';
                                document.getElementById('fade3').style.display='block';
                                  /* if membership card is valid, get casino array
                                   * then display pop-up box showing the card information
                                   * as well as its actual balance
                                   **/
                                  jQuery.ajax(
                                  {
                                      url: url,
                                      type: 'post',
                                      data: {page: function(){ return "GetCasino";},
                                              txtterminal: function() {return $("#txtterminal").val();},
                                              terminalcode: function() {return $("#terminalcode").val()},
                                              txtservices: function() {return $("#txtservices").val();},
                                              cmbterminal: function() {return $("#cmbterminal").val();},
                                              cmbsite: function() {return $("#cmbsite").val();},
                                              chkbalance: function() {return $("#chkbalance").val();},
                                              txtterminalid: function() {return $("#txtterminalid").val();},
                                              txtserviceid: function() {return $("#txtserviceid").val();},
                                              txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                                            },
                                      dataType : 'json',  
                                      success : function(data)
                                      {
                                          if(data == "No User Based."){
                                                $('#loading').hide();
                                                document.getElementById('loading').style.display='none';
                                                document.getElementById('fade3').style.display='none';
                                                document.getElementById('light4').style.display='block';
                                                document.getElementById('fade4').style.display='block';
                                                $('#userdatamsg').html("No user-based account assigned on this card.");
                                           } else {
                                               var tblRow = "<thead>"
                                                    +"<tr>"
                                                    +"<th colspan='3' class='header'>User Based Redemption </th>"
                                                    +"</tr>"
                                                    +"<tr>"
                                                    +"<th>Casino</th>"
                                                    +"<th>Balance</th>"
                                                    +"<th>Action</th>"
                                                    +"</tr>"
                                                    +"</thead>";
                                            
                                              $.each(data, function(i,user)
                                              {
                                                      $('#loading').hide();
                                                      document.getElementById('loading').style.display='none';
                                                      document.getElementById('light3').style.display='block';
                                                      document.getElementById('fade3').style.display='block';
                                                      document.getElementById('txtmid').value = this.MemberID;
                                                      document.getElementById('txtusermode').value = 1;                                                   
                                                      if(this.Balance == 0 || this.Balance == 'Error: Cannot get balance' || this.Balance == 'UserBased Manual Redemption: InActive Casino')
                                                      {

                                                          tblRow +=
                                                                  "<tbody>"
                                                                  +"<tr>"
                                                                  +"<td>"+this.ServiceName+"</td>"   
                                                                  +"<td align='right'>"+this.Balance+"</td>"
                                                                  +"<td align='center' style='width: 100px;'><input type=\"button\" id=\"redeem\" name=\"redeem\" value=\"Redeem\" onclick=\" RedeemUB("+this.Balance+","+this.ServiceID+","+this.TerminalID+","+isewallet+")\"disabled /></td>"
                                                                  +"</tr>"
                                                                  +"</tbody>";
                                                                  $('#userdata3').html(tblRow);
                                                      }     
                                                      else
                                                      {
                                                          var balance = CommaFormatted(this.Balance);
                                                          tblRow +=
                                                                  "<tbody>"
                                                                  +"<tr>"
                                                                  +"<td>"+this.ServiceName+"</td>"   
                                                                  +"<td align='right'>"+balance+"</td>"
                                                                  +"<td align='center' style='width: 100px;'><input type=\"button\" id=\"redeem\" name=\"redeem\" value=\"Redeem\" onclick=\" RedeemUB("+this.Balance+","+this.ServiceID+","+this.TerminalID+","+isewallet+")\" /></td>"
                                                                  +"</tr>"
                                                                  +"</tbody>";
                                                                  $('#userdata3').html(tblRow);
                                                      }
                                              });
                                           }
                                          
                                         
                                      },
                                      error : function(XMLHttpRequest, e)
                                      {
//                                          alert(XMLHttpRequest.responseText);
//                                          if(XMLHttpRequest.responseText == 401){
//                                              window.location.reload();
//                                          }
                                          var result = JSON.parse(XMLHttpRequest.responseText);
                                          var tblRow = "<thead>"
                                                +"<tr>"
                                                +"<th colspan='3' class='header'>User Based Redemption </th>"
                                                +"</tr>"
                                                +"<tr>"
                                                +"<th>Casino</th>"
                                                +"<th>Balance</th>"
                                                +"<th>Action</th>"
                                                +"</tr>"
                                                +"</thead>";

                                          $.each(result, function(i,user)
                                          {
                                                  $('#loading').hide();
                                                  document.getElementById('loading').style.display='none';
                                                  document.getElementById('light3').style.display='block';
                                                  document.getElementById('fade3').style.display='block';
                                                  document.getElementById('txtmid').value = this.MemberID;
                                                  document.getElementById('txtusermode').value = 1;
                                                  if(this.Balance == 0 || this.Balance == 'Error: Cannot get balance' || this.Balance == 'UserBased Manual Redemption: InActive Casino')
                                                  {

                                                      tblRow +=
                                                              "<tbody>"
                                                              +"<tr>"
                                                              +"<td>"+this.ServiceName+"</td>"   
                                                              +"<td align='right'>"+this.Balance+"</td>"
                                                              +"<td align='center' style='width: 100px;'><input type=\"button\" id=\"redeem\" name=\"redeem\" value=\"Redeem\" onclick=\" RedeemUB("+this.Balance+","+this.ServiceID+","+this.TerminalID+")\" disabled/></td>"
                                                              +"</tr>"
                                                              +"</tbody>";
                                                              $('#userdata3').html(tblRow);
                                                  }     
                                                  else
                                                  {
                                                      var balance = CommaFormatted(this.Balance);
                                                      tblRow +=
                                                              "<tbody>"
                                                              +"<tr>"
                                                              +"<td>"+this.ServiceName+"</td>"   
                                                              +"<td align='right'>"+balance+"</td>"
                                                              +"<td align='center' style='width: 100px;'><input type=\"button\" id=\"redeem\" name=\"redeem\" value=\"Redeem\" onclick=\" RedeemUB("+this.Balance+","+this.ServiceID+","+this.TerminalID+")\" /></td>"
                                                              +"</tr>"
                                                              +"</tbody>";
                                                              $('#userdata3').html(tblRow);
                                                  }
                                          });
                                       }
                                  });
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
        <div id="pagetitle"><?php echo "$pagetitle";?></div>
            <br />
            <input type="hidden" name="chkbalance" id="chkbalance" value="CheckBalance" />
            <form method="post" action="process/ProcessTopUp.php" id="frmredemption" class="frmmembership" name="frmcs">
                <input type="hidden" name="page" id="page" value="ManualRedemptionUB" />
                <input type="hidden" name="txtterminal" id="txtterminal"/>
                <input type="hidden" name="txtservices" id="txtservices" />
                <input type="hidden" name="terminalcode" id="terminalcode" />
                <input type="hidden" name="txtok" id="txtterminalid" />
                <input type="hidden" name="txtusermode" id="txtusermode" />
                <input type="hidden" name="txtok1" id="txtserviceid" />
                <input type="hidden" name="txtmid" id="txtmid" />
                <input type="hidden" name="txtisvip" id="txtisvip" />
                <table>
<!--                    <tr>
                        <div id="check1">
                        <input type="checkbox" name="checkbox1" id="checkbox1" value="1" > With Membership Card Number
                        </div>

                    </tr>
                    <tr>
                        <td width="130px">Site / 
</td>
                        <td>
                        //<?php
//                            $vsite = $_SESSION['sites'];
//                            echo "<select id=\"cmbsite\" name=\"cmbsite\">";
//                            echo "<option value=\"-1\">Please Select</option>";
//
//                            foreach ($vsite as $result)
//                            {
//                                 $vsiteID = $result['SiteID'];
//                                 $vorigcode = $result['SiteCode'];
//
//                                 //search if the sitecode was found on the terminalcode
//                                 if(strstr($vorigcode, $terminalcode) == false)
//                                 {
//                                    $vcode = $vorigcode;
//                                 }
//
//                                 else
//                                 {
//                                   //removes the "icsa-"
//                                   $vcode = substr($vorigcode, strlen($terminalcode));
//                                 }
//                                 if($vsiteID <> 1)
//                                 {
//                                    echo "<option value=\"".$vsiteID."\">".$vcode."</option>"; 
//                                 }
//                            }
//                            echo "</select>";
//                        ?>
                            <label id="txtsitename"></label><label id="txtposaccno"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>Terminals</td>
                        <td>
                            <select id="cmbterminal" name="cmbterminal">
                                <option value="-1">Please Select</option>
                            </select>
                            <label id="txttermname"></label>
                        </td>
                    </tr>-->
                    <tr>
                    <td>
                        Card Number
                        <input type="input" size="30"  id="txtcardnumber" class="txtmembership" name="txtcardnumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
                        <div for="txtcardnumber" align='center'>Membership | Temporary</div>
                    </td>
                    </tr>

                </table>
                <div id="loading"></div>
                <div id="submitarea"> 
                    <input type="button" value="Submit" id="btnSubmit"/>
                </div>

                <div id="cont">
                  <div id="light1" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
                  <div class="close_popup" id="btnClose" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none';"></div>
                  <input type="hidden"  name="Withdraw" value="WithdrawUB" />
                    <br />
                    <div id="userdata"></div>

                    <input type="hidden" id="txtamount" name="txtamount"/>
                    <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none'" />
                    <input type="button" id="btnok" value="OK" style="margin-left: 130px; display: none;" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none'" />
                    <input type="button" style="float: left;" value="Redeem" id="btnWithdraw" class="btnWithdraw" />
                  </div>
                    
                  <div id="light4" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
                  <div class="close_popup" id="btnClose" onclick="document.getElementById('light4').style.display='none';document.getElementById('fade4').style.display='none';"></div>
                    <br />
                    <br />
                    <br />
                    <div id="userdatamsg" style="text-align: center"></div>
                    <br />
                    <br />
                    <br />
                    <br />
                    <input type="button" value="Ok" style="float: right;" onclick="document.getElementById('light4').style.display='none';document.getElementById('fade4').style.display='none'" />
                  </div>
                    <div id="fade4" class="black_overlay"></div>

                  <div id="light2" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
                  <div class="close_popup" id="btnClose" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none';"></div>
                  <input type="hidden"  name="Withdraw" value="WithdrawUB" />
                    <br />
                    <div id="userdata"></div>
                    <input type="hidden" id="txtamount2" name="txtamount2"/>
                    <input type="hidden" id="hdnamtwithdraw" name="hdnamtwithdraw" />
                    <table>                    
                        <tr>
                            <td>Ticket ID <span style='color:red'>*<div id="error" style="display:none">Required</div></span></td>
                            <td><input type="text" id="txtticket" name="txtticket" maxlength="20" onkeypress='return numberandletter1(event);'/></td>
                        </tr>
                        <tr>
                            <td>Remarks:</td>
                            <td><textarea cols="23" rows="7" maxlength="250" id="txtremarks" name="txtremarks" onkeypress='return numberandletter1(event);'></textarea></td>
                        </tr>
                    </table>
                    <input type="hidden"  name="Withdraw" value="WithdrawUB" />
                    <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none'" />
                    <input type="button" id="btnok" value="OK" style="margin-left: 130px; display: none;" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none'" />
                </form>
                    <input type="button" style="float: left;" value="Redeem" id="btnWithdraw1"  />
                  </div>
                  <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
                <!---------------------------CASINO BALANCE INFORMATION----------------------------->
                <div id="light3" class="white_page">
                    <div class="close_popup" id="btnClose" onclick="document.getElementById('light3').style.display='none';document.getElementById('fade3').style.display='none';"></div>
                    <input type="hidden" name="txtsitecode" id="txtsitecode" />
                    <table id="userdata3" class="tablesorter" align="center">
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr align="right">
                            <br />

                        </tr>
                    </table>
                    <br />
                    <div align="center">
                        <input type="button" id="btnok" value="Cancel" style="margin-left: 600px;" onclick="document.getElementById('light3').style.display='none';document.getElementById('fade3').style.display='none'" />
                    </div>        
                </div>
                <!----------------------------------------------------------------------------------->
                <!------------------------------ENTER PARTIAL AMOUNT TO WITHDRAW--------------------->
                <div id="lightamt" class="white_page">
                    <div class="close_popup" id="btnClose" onclick="document.getElementById('lightamt').style.display='none';document.getElementById('fade3').style.display='none';"></div>
                    <input type="hidden" name="txtsitecode" id="txtsitecode" />
                    <table align="center" class="tablesorter">
                        <thead>
                            <tr>
                                <th class="header" colspan="2">Partial Withdraw</th>
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td align="left" style="width:250px;"><div style="padding:15px;">Please enter amount to withdraw:</div> </td>
                                <td><div style="padding:15px;"><input type="text" id="txtamtwithdraw" name="txtamtwithdraw" placeholder="Enter amount" onkeypress="return isNumberKey(event);" /></div></td>
                            </tr>    
                        </tbody>    
                    </table>
                    <br />
                    <div style="float:right">
                        <input type="button" id="btnSubmitAmt" value="Next" style="margin-right: 5px;" />
                        <input type="button" id="btnok" value="Cancel" style="" onclick="document.getElementById('lightamt').style.display='none';document.getElementById('fade3').style.display='none'" />      
                    </div>
                </div>
                <!-------------------------------------------------------------------------------------->
                <div id="fade3" class="black_overlay"></div>
                <!-----------------------LAST POP-UP FOR UB REDEMPTION-------------------------------->
                <div id="lightub" class="white_content" oncontextmenu="return false" style="width: 308px; height:312px;">
                  <div class="close_popup" id="btnClose" onclick="document.getElementById('fade3').style.display='none';document.getElementById('lightub').style.display='none';document.getElementById('fadeub').style.display='none';"></div>
                  <input type="hidden"  name="Withdraw" value="WithdrawUB" />
                    <br />
                    <div id="userdata"></div>
                    <input type="hidden" id="txtamount2" name="txtamount2"/> 
                    <table>      
                        <tr>
                            <td>Ticket ID<span style='color:red'>*<div id="error" style="display:none">Required</div></span></td>
                            <td><input type="text" id="txtticketub" name="txtticketub" maxlength="20" onkeypress='return numberandletter1(event);'/></td>
                        </tr>
                        <tr id="cmbsiteiddata">
                            <td>Site / PEGS<span style='color:red'>*<div id="error" style="display:none">Required</div></span></td>
                            <td>
                            <?php
                                $vsite = $_SESSION['sites'];
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
                                     if($vsiteID <> 1)
                                     {
                                        echo "<option value=\"".$vsiteID."\">".$vcode."</option>"; 
                                     }
                                }
                                echo "</select>";
                            ?>
                                <label id="txtsitename"></label><label id="txtposaccno"></label>
                            </td>
                        </tr>
                        <tr>
                            <td>Remarks:</td>
                            <td><textarea cols="23" rows="7" maxlength="250" id="txtremarksub" name="txtremarksub" onkeypress='return numberandletter1(event);'></textarea></td>
                        </tr>
                    </table>
                    <!------------------------------------------------------------------------------>
                    <input type="hidden"  name="Withdraw" value="WithdrawUB" />
                    <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('fade3').style.display='none';document.getElementById('lightub').style.display='none';document.getElementById('fadeub').style.display='none'" />
                    <input type="button" id="btnok" value="OK" style="margin-left: 130px; display: none;" onclick="ddocument.getElementById('lightub').style.display='none';document.getElementById('fadeub').style.display='none'" />
                     </form>
                    <input type="button" style="float: right; margin-right: 5px;" value="Redeem" id="btnWithdrawub"  />
                  </div>
                  <div id="fadeub" class="black_overlay" oncontextmenu="return false"></div>
                </div>

    </div>
    <?php  
        }
    }
    include "footer.php"; ?>
