<?php
/**
 * Created By: JunJun S. Hernandez
 * Created On: March 18, 2014
 * Purpose: Parameters Maintenance
 */
$pagetitle = "Update Parameter";
include 'process/ProcessParameters.php';
include 'header.php';
?>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
        var ParamID = getUrlVars()["pid"];
        $.ajax({
                            url: 'process/ProcessParameters.php',
                            type: 'POST',
                            datatype: 'json',
                            data: {
                                ParamID: function() {
                                    return ParamID;
                                },
                                GetParamData: function() {
                                    return 'GetParamData';
                                }
                            },
                            success: function(data) {
                                var parameterData = $.parseJSON(data);
                                var msg = parameterData.msg;
                                var ErrorCode = parameterData.ErrorCode;
                                if(ErrorCode > 0) {
                                    alert(msg);
                                    window.location.href = 'parametersview.php';
                                } else {
                                    var name = parameterData.ParamName;
                                    var value = parameterData.ParamValue;
                                    var description = parameterData.ParamDescription;
                                    var id = parameterData.ParamID;
                                    $("#txtParamName").val(name);
                                    $("#txtParamValue").val(value);
                                    $("#txtParamDescription").val(description);
                                    $("#hdnTxtParamID").val(id);
                                }
                            },
                            error: function(data) {
                                $("#error_message").html("<span style='color:red'>AJAX Error!</span>");
                            }
                        });
    });

    $("#btnSubmit").live("click", function(event) {
        event.preventDefault();
        if (document.getElementById('txtParamName').value == "" || (document.getElementById('txtParamName').value.indexOf(" ") == 0))
        {
            alert("Blank or Parameter Name with leading space/s is/are not allowed");
            return false;
        }
        else
        {
            if (document.getElementById('txtParamValue').value == "" || (document.getElementById('txtParamValue').value.indexOf(" ") == 0))
            {
                alert("Blank or Parameter Value with leading space/s is/are not allowed");
                return false;
            } else {
                if (document.getElementById('txtParamDescription').value == "" || (document.getElementById('txtParamDescription').value.indexOf(" ") == 0))
                {
                    alert("Blank or Parameter Description with leading space/s is/are not allowed");
                    return false;
                } else {
                    var txtParamName = $("#txtParamName").val();
                    var txtParamValue = $("#txtParamValue").val();
                    var txtParamDescription = $("#txtParamDescription").val();
                    var ParamID = getUrlVars()["pid"];
                    $.ajax({
                        url: 'process/ProcessParameters.php',
                        type: 'POST',
                        datatype: 'json',
                        data: {
                            ParamName: function() {
                                return txtParamName;
                            },
                            ParamValue: function() {
                                return txtParamValue;
                            },
                            ParamDescription: function() {
                                return txtParamDescription;
                            },
                            UpdateParameter: function() {
                                return 'UpdateParameter';
                            },
                            ParamID: function() {
                                return ParamID;
                            }
                        },
                        success: function(data) {
                            var parameterData = $.parseJSON(data);
                            var msg = parameterData.msg;
                            var ErrorCode = parameterData.ErrorCode;
                            alert(msg);
                            if (ErrorCode == 0 || ErrorCode == 1) {
                                window.location.href = 'parametersview.php';
                            }
                        },
                        error: function(data) {
                            $("#error_message").html("<span style='color:red'>AJAX Error!</span>");
                        }
                    });
                }
            }
        }
    });
    
    function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
        function(m,key,value) {
		vars[key] = value;
	});
	return vars;
    }

</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br /> 
    <form method="post" action="process/ProcessParameters.php">
        <table>
            <tr><td>Name</td><td><input type="text" id="txtParamName" name="txtParamName" value="" readonly style="width: 350px;" /></td></tr>
            <tr><td>Value</td><td><input type="text" id="txtParamValue" name="txtParamValue" value="" style="width: 350px;" /></td></tr>
            <tr><td>Description</td><td><input type="text" id="txtParamDescription" name="txtParamDescription" value="" style="width: 350px;" /></td></tr>
        </table>
        <input type="hidden" id="hdnTxtParamID" name="hdnTxtParamID" value="" />
        <div id="submitarea">
            <input type="button" value="Cancel" onclick="window.location.href='parametersview.php'"/>
            <input type="button" value="Submit" id="btnSubmit" />
        </div>
    </form>
</div>
<?php
include 'footer.php';
?>