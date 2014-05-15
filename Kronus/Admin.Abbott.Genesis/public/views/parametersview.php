
<?php
/**
 * Created By: JunJun S. Hernandez
 * Created On: March 18, 2014
 * Purpose: Parameters Maintenance
 */
$pagetitle = "Parameters List";
include 'process/ProcessParameters.php';
include 'header.php';
?>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
        return loadparameters();
    });
    function loadparameters()
        {
            jQuery("#parameters").jqGrid(
                    {
                        url: 'process/ProcessParameters.php',
                        mtype: 'post',
                        postData: {
                            ParametersList: function() {
                                return 'ParametersList';
                            }
                        },
                        datatype: "json",
                        colNames: ['ParameterName', 'Value', 'Description', 'Action'],
                        colModel: [
                            {name: 'ParameterName', index: 'ParameterName', width: 150, align: 'left'},
                            {name: 'Value', index: 'Value', width: 100, align: 'left'},
                            {name: 'Description', index: 'Description', width: 150, align: 'left'},
                            {name: 'Action', index: 'Action', width: 100, align: 'center'},
                        ],
                        rowNum: 10,
                        rowList: [10, 20, 30],
                        height: 280,
                        width: 1000,
                        pager: '#pager2',
                        viewrecords: true,
                        sortorder: "asc",
                        loadonce: true,
                        caption: "Parameters"
                    });
            jQuery("#parameters").jqGrid('navGrid', '#pager2', {edit: false, add: false, del: false, search: false});
        }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br /> 
    <form method="post" action="process/ProcessParameters.php">
        <table border="1" id="parameters"></table>
        <div id="pager2"></div>
    </form>
    <div id="submitarea">
                <input type="button" value="Add New Parameter" onclick="window.location.href='parametersadd.php'"/>
    </div>
</div>
<?php
include 'footer.php';
?>