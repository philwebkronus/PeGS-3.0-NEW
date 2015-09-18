<?php
$pagetitle = "View Access Rights";
include 'process/ProcessMenuMaintenance.php';
include 'header.php';

$vaccesspages = array('1');
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
        $vrightID = 0;
        $vsubmenu = '';
        $vacctype = '';
        if(isset ($_SESSION['accessdetails']))
        {
            $raccess = $_SESSION['accessdetails'];
            $vrightID = $raccess['RightID'];
            $vsubmenu = $raccess['Submenu'];
            $vacctype = $raccess['AccountType'];
            echo "<script type=\"text/javascript\">
                  jQuery(document).ready(function(){
                  document.getElementById('light').style.display='block';
                  document.getElementById('fade').style.display='block';
                  });
                  </script>";
            unset($_SESSION['accessdetails']);
        }
?>

<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="process/ProcessMenuMaintenance">
        <input type="hidden" name="page" value="DeactivateAccessRights" />
        <table>
            <tr>
                <td>Account Types</td>
                <td>
                    <select id="cmbacctype" name="cmbacctype">
                        <option value="0">All</option>
                    </select>
                </td>
            </tr>
        </table>
        <br />
        <div align="center" id="pagination">
          <!-- for viewing of access rights-->
          <table border="1" id="rights"></table>
          <div id="pager1"></div>
        </div>
        <div id="light" class="white_confirm">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <input type="hidden" id="rightid" name="rightid" value="<?php echo  $vrightID; ?>" />
            <input type="hidden" id="accesstype" name="accesstype" value="<?php echo $vacctype; ?>" />
            <input type="hidden" name="submenu" id="submenu" value="<?php echo $vsubmenu; ?>"/>
            <br />
            <p>Are you sure you want to terminate the access right of 
               <?php echo $vacctype." in ".$vsubmenu." submenu ?"; ?>
            </p>
            <p align="center"></p>
            <input type="submit" value="OK" style="float: left;"/>
            <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessMenuMaintenance.php';
        
        //ajax: get all account types
        jQuery.ajax({
            url : url,
            type: 'post',
            data: {page: function(){ return 'GetAccountTypes'}},
            dataType: 'json',
            success: function(data){
                var acct = jQuery("#cmbacctype");
                jQuery.each(data, function(){
                    acct.append(jQuery("<option />").val(this.AccountTypeID).text(this.Name));
                });
            },
            error: function(XMLHttpRequest, e)
            {
                alert(XMLHttpRequest.responseText);
                if(XMLHttpRequest.status == 401)
                {
                    window.location.reload();
                }
            }
        });
        
        //this part displays the access rights  
        viewaccessrights(url, jQuery("#cmbacctype").val());
        
        jQuery("#cmbacctype").live('change', function(){
            jQuery("#rights").GridUnload();
            viewaccessrights(url, jQuery("#cmbacctype").val());
        });
    });
    
    
    function viewaccessrights(url, acctype)
    {
        jQuery("#rights").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'AccessRightsView';},
                            acctypeid: function(){return acctype;}
                          },
                datatype: "json",
                colNames:['Account Type','Menu Name','Submenu Name','Order ID','Default URL','Default URL2','Action'],
                colModel:[
                           {name:'AccountType', index:'AccountType', align:'left',width: 100},
                           {name:'MenuName', index:'MenuName', align:'left',width: 100},
                           {name:'SubMenuName', index:'SubMenuName', align:'left'},
                           {name:'OrderID',index:'OrderID', align: 'center',width:70},
                           {name:'DefaultURL',index:'DefaultURL', align: 'left',width:100},
                           {name:'DefaultURL2', index: 'DefaultURL2', align: 'left'},
                           {name:'button', index: 'button', width:120, align: 'center',sortable:false}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 240,
                width: 1000,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Access Rights",
                gridview: true
           });
       jQuery("#rights").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false}); 
    }
</script>
<?php  
    }
}
include 'footer.php'; 
?>