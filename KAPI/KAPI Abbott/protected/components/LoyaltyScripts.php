<?php

    // call loyalty portal
    function redirectLoyaltyPortal(){
        Utilities::log(Yii::app()->params['loyalty_portal']);exit;
        echo '<script type="text/javascript">window.open("'.Yii::app()->params['loyalty_portal'].'","LoyaltyPortal","height=550, scrollbars=1, resizable=1, width=900")</script>';
    }
?>