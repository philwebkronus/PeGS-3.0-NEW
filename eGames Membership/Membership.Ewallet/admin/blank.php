<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$currentpage = "Administration";


?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />

<div align="center">
    </form>
    <form name="updateplayerstatus" id="updateplayerstatus" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content"> 

            </div>
                
        </div>
    </form>
</div>
<?php include("footer.php"); ?>