<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

            <div id="aside">
                <?php include "submenumaintenance.php"; ?>
            </div>
            
        </div>  <!-- end of content-container  -->
</div>
            <div id="footer">
                Copyright © 2011. Philweb Corporation. All rights reserved.
            </div>
        

        <!--  For Javascript Alert Dialog (Errors)  -->
        <?php
        if(isset($_SESSION['mess']))
        {
            $msg = $_SESSION['mess'];
            $msg = str_replace(array("\r", "\n"), '', $msg);
        ?>
            <script type="text/javascript" language="javascript">
                $(document).ready(function(){
                    <?php 
                      echo "alert('".$msg."');";
                    ?>
                });
            </script>
        <?php
        }
        if(isset($_GET['mess']))
        {
            $msg = $_GET['mess'];
            $msg = str_replace(array("\r", "\n"), '', $msg);
        ?>
            <script type="text/javascript" language="javascript">
                $(document).ready(function(){
                    <?php 
                      echo "alert('".$msg."');";
                      echo "window.location.href='".$_SERVER["PHP_SELF"]."';";
                    ?>
                });
            </script>
        <?php
        }
        unset($_SESSION['mess']);
        session_destroy();
        ?>   
    </body>
</html>