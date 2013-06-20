<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-04
 * Company: Philweb
 * ***************** */
?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function()
    {
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        $("#txtSearch").click(function(){
            $("#txtSearch").change();
            if ($("#txtSearch").val() == defaultvalue)
            {
                $("#txtSearch").val("");
                $("#btnSearch").attr("disabled", "disabled");
            }
        });
        $("#txtSearch").keyup(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").blur(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").change(function(){
            if ($("#txtSearch").val() == "" || $("#txtSearch").val() == defaultvalue)
            {
                $("#btnSearch").attr("disabled", "disabled");
                $("#txtSearch").val(defaultvalue);
            }
            else
            {
                $("#btnSearch").removeAttr("disabled");
            }
            
        });
        $("#btnClear").click(function(){
            $("#txtSearch").val("");
            $("#txtSearch").change();
        });
        
    
    });
</script>
</form>
<form name="frmSearch" id="frmSearch" method="post" action="" >
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>
</form>