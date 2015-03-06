<?php
//session_start();
$pagetitle = "Batch Upload per Bank";  
include("process/ProcessBatchUploadperBank.php");
//include("process/ProcessTopUp.php");
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
?>

<div id="light" class="white_content">
    <div id="title" class="light-title"></div>
    <div id="msg" class="light-message"></div>
    <div id="button" class="light-button">
        <input type="button" onclick="javascript: document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" value="Okay"/>
</div> 
    </div>
<div id="workarea">
    <div id="fade" class="black_overlay"></div>
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  
  <form name="frmUpload" method="post" enctype="multipart/form-data">
 
      <input type="hidden" id="hidden_date" value="<?php echo date('Y-m-d');?>" />
      
      <table>
        <tr>
            <td>Bank</td>
            <td>
               <?php               
               
                 $banks = $_SESSION['banks'];
                 echo "<select id=\"cmbbankname\" name=\"cmbbankname\">";
                 echo "<option value=\"-1\">Please select</option>";

                 foreach($banks as $bank)
                 {
                    $bankID = $bank['BankID'];      
                    $bankName = $bank['BankName'];                           
                   
                       echo "<option value=\"".$bankID."\">".$bankName."</option>";
                   
                 }
                 echo "</select>";
              ?>
              
            </td>
        </tr>
        <tr>
            <td>XLS File</td>
            <td>
                <input type="file" id="file" name="file" maxlength="43" size="43" />
            </td>
        </tr>
        
     </table>
     <div id="submitarea">
         <input name="submit" type="submit" value="Submit" onclick="return checkdepositposting();"/>
     </div> 
  </form>

</div>
<?php  
    }
}
include "footer.php"; 
if($_SESSION['alert']!='')
{
    $alert = $_SESSION['alert'];
    if($alert == 'Please Select a Bank')
    {
        echo"<script>alert('Please Select a Bank');</script>";
    }
    if($alert == 'Please Specify a File')
    {
        echo"<script>alert('Please Specify a File');</script>";
    }
    if($alert == 'Invalid File')
    {
        echo"<script>alert('Invalid File');</script>";
    }
    if($alert == 'There was an error uploading the file, please try again')
    {
        echo"<script>alert('There was an error uploading the file, please try again');</script>";
    }
     if($alert == 'Error in file upload: All columns must be supplied with corresponding values')
    {
        echo"<script>alert('Error in file upload: All columns must be supplied with corresponding values');</script>";
    }
    unset ($_SESSION['alert']);

}
?>
<?php if (isset($table)) : ?>
        <script>
            document.getElementById('title').innerHTML = "Batch Upload per Bank Summary";
            document.getElementById('msg').innerHTML = "<?php echo $table;?>";
            document.getElementById('light').style.display = 'block';
            document.getElementById('fade').style.display = 'block';
        </script>  
        <?php endif; ?>
        
  

