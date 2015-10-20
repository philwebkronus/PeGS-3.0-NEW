<br />
<?php echo MI_HTML::dropDown($reportsFormModel, 'reports_type', $reports_type); ?>
<br />
<div id="reports_container">
    
    
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#btn_submit').live('click',function(){
            $('#hidselected_date').val($('#ReportsFormModel_date').val());
            $('#frmtranshistory').submit();
            return false;
        });
    });
</script>
<link type="text/css" href="css/le-frog/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jscripts/reports.js"></script>
<script type="text/javascript" src="jscripts/jquery-ui-1.8.16.custom.min.js"></script>