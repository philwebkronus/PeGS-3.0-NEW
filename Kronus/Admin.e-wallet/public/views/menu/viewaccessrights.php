<?php
$pagetitle = "View Access Rights";
//include '../process/ProcessMenuMaintenance.php';
include 'menuheader.php';
?>

<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="#">
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
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = '../process/ProcessMenuMaintenance.php';
        
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
                colNames:['Account Type','Menu Name','Submenu Name','Order ID','Default URL','Default URL2'],
                colModel:[
                           {name:'AccountType', index:'AccountType', align:'left'},
                           {name:'MenuName', index:'MenuName', align:'left'},
                           {name:'SubMenuName', index:'SubMenuName', align:'left'},
                           {name:'OrderID',index:'OrderID', align: 'center'},
                           {name:'DefaultURL',index:'DefaultURL', align: 'left'},
                           {name:'DefaultURL2', index: 'DefaultURL2', align: 'left'}
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
<?php include 'menufooter.php'; ?>