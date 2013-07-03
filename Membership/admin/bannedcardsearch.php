<?php
/*
* Description: Card Search for banned cards.
* @author: aqdepliyan
* DateCreated: 2013-06-20 09:16:20 AM
*/
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
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>