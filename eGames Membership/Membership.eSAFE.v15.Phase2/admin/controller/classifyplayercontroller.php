<?php

require_once("../../init.inc.php");

App::LoadModuleClass("Membership", "RefVip");

$vip_levels = new RefVip();
$serviceid = $_GET['serviceid'];
$data = $classification_types = $vip_levels->getVipNameByServiceId($serviceid);;
echo "<option value=''>Select One</option>";
foreach ($data as $vip)
{
    echo "<option value='".$vip['VIPLevelID']."'>".$vip['Name']."</option>";
}
?>
