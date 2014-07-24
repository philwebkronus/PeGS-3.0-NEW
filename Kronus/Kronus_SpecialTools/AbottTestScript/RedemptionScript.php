<script type="text/javascript" src="jscripts/jquery.min.js"></script>
<script type="text/javascript" src="jscripts/jquery-helpers.js"></script>

<div id="output">
    <div id="gb_header">&nbsp;&nbsp; CASHIER API Redemption Process</div><br />
        <div id="inputs">
            <div id="un_input">
                <label>TerminalCode: </label>
                <div id="inputBox">
                    <input type="text" name="terminalcode" id="terminalcode" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>AccountID: </label>
                <div id="inputBox">
                    <input type="text" name="acctid" id="acctid" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Amount: </label>
                <div id="inputBox">
                    <input type="text" name="amount" id="amount" value=""/>
                </div>
            </div>
            <div id="un_input">
                <input id="btnsubmit" type="submit" value="Submit" class="button" />
            </div>
        </div>
        <br/><br/><br/>
        <div>
            <label id="lbltipAddedComment"></label>
        </div>    
</div>    
    
<script>
$(document).ready(function()
{
    jQuery("#btnsubmit").click(function()
    {           jQuery.ajax({
                            url: "redemptioncontroller.php",
                            type: 'post',
                            data: {terminalcode: function() {return $("#terminalcode").val();},
                                   amount: function() {return $("#amount").val();},
                                   acctid: function() {return $("#acctid").val();}
                                },
                            dataType : 'json',  
                            success : function(data)
                            {
                                alert('Transaction Successful, Please open firebug console');
                            },
                            error : function(XMLHttpRequest, e){
                                alert(XMLHttpRequest.responseText);
                            }
                 });
        
    });

});        
</script>    