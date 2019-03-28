<?php 
$pagetitle = "Casino Gross Hold"; 
include 'process/ProcessPagcorMgmt.php';
include "header.php";
$vaccesspages = array('11');
    $vctr = 0;
    if(isset($_SESSION['acctype']))
    {
        foreach ($vaccesspages as $val)
        {
            if($_SESSION['acctype'] == $val)
            {
                break;
            }
            else
            {
                $vctr = $vctr + 1;
            }
        }

        if(count($vaccesspages) == $vctr)
        {
            echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                         document.getElementById('blockf').style.display='block';</script>";
        }
        else
        {
?>
<div id="workarea">
    <form method="post" id="frmexport">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <iFrame style ='width: 1080px; height: 500px; ' src ="<?php echo $casinoGH_url; ?>"></iFrame>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>