<?php  
include 'process/ProcessTopUp.php';
$pagetitle = "Increase BCF";  
include "header.php";
$vaccesspages = array('5');
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
            if((!isset($_SESSION['siteid'])) && (!isset($_SESSION['BCF'])))
            {
                echo "<script type='text/javascript'>window.location.href='topupview.php';</script>";
            }
            else
            {
              $vsiteID = $_SESSION['siteid'];
              $rbcf = $_SESSION['BCF'];
              foreach ($rbcf as $rresult)
              {
                $ramount = $rresult['Balance'];
                $rminbal = $rresult['MinBalance'];
                $rmaxbal = $rresult['MaxBalance'];
                $rpickup = $rresult['PickUpTag'];
                $rtopuptype = $rresult['TopUpType'];
              }
  }
?>
<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>        
        <form method="post" action="process/ProcessTopUp.php">
            <input type="hidden" name="page" value="UpdateSiteBalance" />
            <input type="hidden" name="txtsite" value="<?php echo $vsiteID; ?>" />
            <?php if( $rpickup == 1){ ?>
                 <input type="hidden" id="optpickyes" name="optpick" value="1" />                  
            <?php } else { ?>                  
                 <input type="hidden" id="optpickno" name="optpick" value="0" />
            <?php } ?>
            <?php if( $rtopuptype == 1){ ?>
               <input type="hidden" id="opttypeyes" name="opttype" value="1" />               
           <?php } else { ?>               
               <input type="hidden" id="opttypeno" name="opttype" value="0" />
           <?php } ?>                                  
           <table>
               <tr>
                   <td>Minimum Balance</td>
                   <td>
                       <input type="text" readonly="readonly" name="txtminbal" id="txtminbal" value="<?php echo number_format($rminbal, 2); ?>" />
                   </td>
               </tr>
               <tr>
                   <td>Maximum Balance</td>
                   <td>
                       <input type="text" readonly="readonly" name="txtmaxbal" id="txtmaxbal" value="<?php echo number_format($rmaxbal,2); ?>" />
                   </td>
               </tr>
               <tr>
                   <td>Previous Balance</td>
                   <td>
                       <input type="text" readonly="readonly" name="txtprevbal" id="txtprevbal" value="<?php echo number_format($ramount,2); ?>" />
                   </td>
               </tr>
               <tr>
                   <td>Amount Added</td>
                   <td>
                       <input class="auto" type="text" id="txtamount" name="txtamount" value="<?php echo number_format($ramount,2); ?>" onkeypress="return numberonly(event);" />
                   </td>
               </tr>
           </table>
            <div id="submitarea">
                <input type="submit" value="Submit" id="btnSubmit" onclick="return chktopupbal();"/>
            </div>                
        </form>        
</div>
<?php  
    }
}
include "footer.php"; ?>

