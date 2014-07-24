<script type="text/javascript" src="jscripts/jquery.min.js"></script>
<script type="text/javascript" src="jscripts/jquery-helpers.js"></script>

<div id="output">
    <div id="gb_header">&nbsp;&nbsp; CASHIER API</div><br />
        <div id="inputs">
            <div id="un_input">
                <label>Method: </label>
                <div id="inputBox">
                    <select name="method" id="method">
                       <option value="-1">Select One</option>
                       <option value="1">GetPIDFromLogin</option>
                       <option value="2">GetAccountInfoByPID</option>
                       <option value="3">GetAccountBalance</option>
                       <option value="4">Login</option>
                       <option value="5">DepositGeneric</option>
                       <option value="6">WithdrawGeneric</option>
                       <option value="7">TransactionSearchInfo</option>
                    </select>
                </div>
            </div>
            <div id="un_input">
                <label>Username: </label>
                <div id="inputBox">
                    <input type="text" name="username" id="username" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>PID: </label>
                <div id="inputBox">
                    <input type="text" name="pid" id="pid" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Password: </label>
                <div id="inputBox">
                    <input type="text" name="pw" id="pw" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Amount: </label>
                <div id="inputBox">
                    <input type="text" name="amount" id="amount" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Tracking1: </label>
                <div id="inputBox">
                    <input type="text" name="tracking1" id="tracking1" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Tracking2: </label>
                <div id="inputBox">
                    <input type="text" name="tracking2" id="tracking2" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Tracking3: </label>
                <div id="inputBox">
                    <input type="text" name="tracking3" id="tracking3" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Tracking4: </label>
                <div id="inputBox">
                    <input type="text" name="tracking4" id="tracking4" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>SessionID: </label>
                <div id="inputBox">
                    <input type="text" name="sessionid" id="sessionid" value=""/>
                </div>
            </div>
            <div id="un_input">
                <input id="btnsubmit" type="submit" value="Submit" class="button" />
            </div>
        </div>
</div>    
    
<script>
$(document).ready(function()
{
    jQuery("#btnsubmit").click(function()
    {           jQuery.ajax({
                            url: "http://localhost/AbottAPITest/cashiercontroller.php",
                            type: 'post',
                            data: {method: function() {return $("#method").val();},
                                    username: function() {return $("#username").val();},
                                    pid: function() {return $("#pid").val();},
                                    password: function() {return $("#pw").val();},
                                    amount: function() {return $("#amount").val();},
                                    tracking1: function() {return $("#tracking1").val();},
                                    tracking2: function() {return $("#tracking2").val();},
                                    tracking3: function() {return $("#tracking3").val();},
                                    tracking4: function() {return $("#tracking4").val();},
                                    sessionid: function() {return $("#sessionid").val();}
                                },
                            dataType : 'json',  
                            success : function(data)
                            {
                                 alert(data);
                            },
                            error : function(XMLHttpRequest, e){
                                alert(XMLHttpRequest.responseText);
                            }
                 });
        
    });

});        
</script>    