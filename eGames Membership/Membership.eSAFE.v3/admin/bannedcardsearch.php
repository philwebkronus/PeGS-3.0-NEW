<?php
/*
* Description: Card Search for banned cards.
* @author: aqdepliyan
* DateCreated: 2013-06-20 09:16:20 AM
* @modified mgesguerra
*/

//Clear the session for Redemtion
if(isset($_SESSION['CardRed'])){
    unset($_SESSION['CardRed']);
}

?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function()
    {
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        $("#txtSearch").click(function(){
            $("#txtSearch").change();
            if ($("#txtSearch").val() === "")
            {
                $("#txtSearch").val("");
            }
        });
        $("#txtSearch").keyup(function(){
            $("#txtSearch").change();
            $("#btnSearch").removeAttr("disabled");
        });
        $("#txtSearch").blur(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").change(function(){
            if ($("#txtSearch").val() === "")
            {
                $("#btnSearch").attr("disabled", "disabled");
                $("#txtSearch").val("");
            }
            else
            {
                $("#btnSearch").removeAttr("disabled");
            }
            
        });
        $("#btnClear").click(function(){
            $("#txtSearch").val("");
        });
        
    
    });
</script>
<div class="searchbar formstyle">
        <?php echo $txtSearch; ?><?php echo $btnSearch; ?><?php echo $btnClear; ?>
</div>