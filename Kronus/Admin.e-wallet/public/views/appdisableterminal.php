<?php 
$pagetitle = "Disable Terminal";  
include 'process/ProcessAppSupport.php';
include "header.php";
$vaccesspages = array('9');
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
            if(isset($_SESSION['cshmacid']))
            {
                $vcshmacid = $_SESSION['cshmacid'];
                echo "<script type=\"text/javascript\">
                      jQuery(document).ready(function(){
                      document.getElementById('light').style.display='block';
                      document.getElementById('fade').style.display='block';
                      });
                      </script>";
                unset($_SESSION['cshmacid']);
            }
            else
            {
                $vcshmacid = '';
            }
?>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="process/ProcessAppSupport.php">
      <input type="hidden" id="disableterm" name="disableterm" value="DisableTerminal" />
        <br />
        <table>
            <tr>
                <td>Site / PEGS</td>
                <td>
                    <?php
                        $vsite = $_SESSION['siteids'];
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                        echo "<option value=\"-1\">Please Select</option>";

                        foreach ($vsite as $result)
                        {
                             $vsiteID = $result['SiteID'];
                             $vorigcode = $result['SiteCode'];
                             
                             //search if the sitecode was found on the terminalcode
                             if(strstr($vorigcode, $terminalcode) == false)
                             {
                                $vcode = $vorigcode;
                             }
                             else
                             {
                               //removes the "icsa-"
                               $vcode = substr($vorigcode, strlen($terminalcode));
                             }
                             if($vsiteID <> 1)
                             {
                               echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                             }
                        }
                        echo "</select>";
                    ?>
                         <label id="txtsitename"></label>
                </td>
            </tr>
        </table>
        <div id="light" class="white_confirm">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <input type="hidden" id="txtmacid" name="txtmacid" value="<?php echo  $vcshmacid; ?>" />
            <input type="hidden" name="page2" value='DisableTerminal' />
            <br />
            <p>Are you sure you want to disable this cashier terminal?</p>
            <p>Please input remarks to continue</p>
            <table align="center">
                <tr>
                    <td>
                        <input type="text" id="txtremarks" name="txtremarks" onkeypress="return numberandletter1(event);"/>
                    </td>
                </tr>
            </table>
            <br />
            <input type="submit" value="OK" style="float: left;"/>
            <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
        </div>
        <div id="fade" class="black_overlay"></div>
        <div align="center" id="pagination">
            <table border="1" id="userdata">

            </table>
            <div id="pager2"></div>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessAppSupport.php'; 
        jQuery('#cmbsite').live('change', function()
        {
            if(jQuery('#cmbsite').val() == "-1")
            {
                jQuery("#txtsitename").text(" ");
            }
            else
            {
                var siteid = jQuery('#cmbsite').val();
                //this will display the sitename
                jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                    dataType: 'json',
                    success: function(data){
                        if(jQuery("#cmbsite").val() > 0)
                        {
                            jQuery("#txtsitename").text(data.SiteName);
                        }
                        else
                        {   
                            jQuery("#txtsitename").text(" ");
                        }
                    },
                    error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                    }
                });
                
                getcashiermachine(url, siteid); //call cashier machine info jqgrid
            }
        }); 
    });
    
    function getcashiermachine(url, siteid)
    {
        $('#userdata').GridUnload();;
        jQuery("#userdata").jqGrid({
                url:url,
                mtype: 'post',
                postData: {
                            paginate: function() {return 'ViewMachineInfo';},
                            siteid : function() {return siteid}
                          },
                datatype: "json",
                colNames:['Site Code', 'Computer Name', 'CPU ID', 'BIOS Serial Number', 'MAC Address', 
                          'Motherboard Serial Number', 'OS ID','IP Address','Action'],
                colModel:[
                        {name:'SiteCode',index:'SiteCode',align: 'center', width: 100},
                        {name:'ComputerName',index:'ComputerName', align: 'center'},
                        {name:'CPUID',index:'CPUID', align: 'left'},
                        {name:'BiosSerial',index:'BiosSerial', align: 'left'},
                        {name:'MacAddress',index:'MacAddress', align: 'left'},
                        {name:'MotherBoard',index:'MotherBoard', align: 'left'},
                        {name:'OSID',index:'OSID', align: 'center'},
                        {name:'IPAddress', index:'IPAddress', align:'center'},
                        {name:'button', index: 'button', width:120, align: 'center'}
                ],

                rowNum:10,
                rowList:[10,20,30],
                height: 250,
                width: 1100,
                pager: '#pager2',
                refresh: true,
                viewrecords: true,
                sortorder: "asc",
                caption:"Cashier Machine Information"
        });
        jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
    }
</script>
<?php  
    }
}
include "footer.php"; ?>