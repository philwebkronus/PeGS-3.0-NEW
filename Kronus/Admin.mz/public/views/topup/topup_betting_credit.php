<?php 
$pagetitle = "Betting Credit"; 
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
            if(isset($_GET['report']))
                $r = $_GET['report'];
            else
                $r = $param['report'];
?>
<div id="workarea">
    <div id="pagetitle">Betting Credit</div>
    <form id="frmexport" method="post">
        <br />
        <table>
            <tr>
                <td>Operator</td>
                <td>
                    <select id="sel_operator" name="sel_operator">
                        <option value="All">All</option>
                        <?php foreach($param['owner'] as $operator): ?>
                        <option value="<?php echo $operator['OwnerAID'] ?>"><?php echo $operator['Name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Site / PEGS</td>
                <td>
                    <select id="sel_site_code" name="sel_site_code">
                        <option value="All">All</option>
                    </select>
                    <label id="lblsitename"></label>
                </td>
            </tr>
            <tr>
                <td>Balance</td>
                <td>
                    <select id="selcomp">
                        <option value="">Please Select</option>
                        <option value=">">&gt;</option>
                        <option selected="selected" value="<">&lt;</option>
                        <option value=">=">&gt;=</option>
                        <option value="<=">&lt;=</option>
                    </select>
                    <input type="text" id="txtBalance" name="txtBalance" value="100,000.00" class="auto" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" id="btnSearch" value="Search" />    
        </div>
        <div align="center" id="pagination">
          <table id="bc"></table>
          <div id="pager2" style="height: 80px;">
              <div style="text-align: right;padding-right: 20px;"><b>Total:</b> <span id="total"></span></div>
              <br />
          </div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1000px;">
            <br />
            <input type="button" id="btnpdf" value="Export to PDF File" style="float: right;"/>
            <input type="button" id="btnexcel" value="Export to Excel File" style="float:right;"/>
        </div>
    </form>
</div>
<script type="text/javascript" id="yahoo">
    jQuery(document).ready(function(){
        jQuery('#sel_operator').change(function(){
            var optrval = jQuery('#sel_operator').val();
            $('#sel_site_code').html('<option value="All">All</option>');
            
            if(optrval != "All")
            {
                $.ajax({
                    url:'process/ProcessTopUpPaginate.php?action=getsites&operator='+$(this).val(),
                    type: 'post',
                    success:function(data){
                        var json = $.parseJSON(data);
                        html = '<option val="All">All</option>';
                        for(i=0;i < json.length; i++ ) {
                            html+='<option value="'+json[i].SiteID+'" label="'+json[i].SiteName + " / "+ json[i].POSAccountNo + '">'+json[i].SiteCode+'</option>';
                        }
                        $('#sel_site_code').html(html);
                    },
                    error:function(e){
                        if(e.responseText() == undefined || e.responseText() == null ||  e.responseText() == '' ) {
                            alert('Oops! Something went wrong');
                            return false;
                        }
                        alert(e.responseText());
                    }
                });
            }
        });
        
//        jQuery('#txtBalance').val('200,000.00');
//        jQuery('#selcomp').val('<');
        
        jQuery('#btnSearch').live('click',function(){
            if(jQuery('#txtBalance').val()=='' && jQuery('#selcomp').val() !='') {
                alert('Please enter balance');
                return false;
            }
            
            if(jQuery('#selcomp').val() =='' && jQuery('#txtBalance').val()!='') {
                alert('Please select comparison');
                return false;
            }
            var owner = $('#sel_operator').val();
            jQuery("#bc").jqGrid('setGridParam',{url:'process/ProcessTopUpPaginate.php?action=getbettingcredit&report=<?php echo $r; ?>&bal='+jQuery('#txtBalance').val()+
                    '&selcomp='+jQuery('#selcomp option:selected').val()+'&sel_site_id='+$('#sel_site_code').val() + '&owner='+owner}).trigger("reloadGrid")
                    +'&report=<?php echo $r; ?>'; 
        });
        
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=bettingcreditpdf&report=<?php echo $r; ?>');
            jQuery('#frmexport').submit();
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessTopUpGenerateReports.php?action=bettingcreditexcel&report=<?php echo $r; ?>');
            jQuery('#frmexport').submit();
        });
                
        jQuery("#bc").jqGrid({
            url : 'process/ProcessTopUpPaginate.php?action=getbettingcredit&report=<?php echo $r; ?>&bal='+jQuery('#txtBalance').val()+'&selcomp='+jQuery('#selcomp option:selected').val(),
            datatype: "json",
            colNames:['Site / PEGS Code','Site / PEGS Name','POS Account','Balance'],
            rowNum:10,
            rowList:[10,20,30],
            height: 280,
            width: 1000,
            pager: '#pager2',
            viewrecords: true,
            sortorder: "asc",
            caption:"Betting Credit",
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'SiteName',index:'SiteName',align:'left'},
                {name:'POSAccountNo',index:'POSAccountNo', align:'center'},
                {name:'Balance',index:'Balance',align:'right'},
            ],
             loadComplete: function(response) {
                 for(var i = 0; i < response.rows.length; i++) {
                     var bal = response.rows[i].cell[3].replace(/\,/g,'');
                     var minbal = response.rows[i].cell[4];
                     if(parseFloat(bal) < parseFloat(minbal)) {
                         $('#'+response.rows[i].id).css({'color':'red'});
                     }
                     
//                     if(parseFloat(bal) <= 100000) {
//                         $('#'+response.rows[i].id).css({'color':'red'});
//                     }
                 }
    
                 if(response.totalbalance != undefined) {
                     jQuery('#total').html(response.totalbalance);
                 } else {
                     jQuery('#total').html('');
                 }
             },            
            resizable:true
        });
        jQuery("#bc").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false,refresh:true});
    });
</script>
<?php  
    }
}
include "footer.php"; ?>