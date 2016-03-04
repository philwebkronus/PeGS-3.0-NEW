<br />
<?php echo MI_HTML::dropDown($FTModel, 'transaction_type', $transaction_type);
?>
<br />

<div id="containerForceT">
    
    
</div>

<link type="text/css" href="css/le-frog/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jscripts/forcet.js"></script>
<script type="text/javascript" src="jscripts/jquery-ui-1.8.16.custom.min.js"></script>
<input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
<script type="text/javascript">
jQuery(document).ready(function(){
       
      if ($('#siteamountinfo').val() == 0){
             showLightbox(function(){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[010]: Site amount is not set.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );   
            });
        }
 });
</script>
