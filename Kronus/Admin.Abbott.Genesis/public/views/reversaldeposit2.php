<?php $pagetitle = "Update Verified Deposits";  ?>

<?php include '../process/ProcessTopUp.php'; ?>

<?php  include "header.php"; ?>

<div id="workarea">
    
    
    <script type="text/javascript">
        $(document).ready(function(){
            $('#cmbsite').live('change', function(){
                       jQuery("#useraccs").jqGrid({
                            
                            url:'../process/ProcessTopUp.php',
                            mtype: 'post',
                            postData: {
                                    paginate2: function() {return $("#paginate2").val();},
                                    cmbsite: function() {return $('#cmbsite').val();}
                                      },
                            datatype: "json",
                            colNames:['SiteRemittanceID','Remittance Type','BankID', 'Branch', 'Amount', 'BankTransID', 'BankTransDate', 'Cheque#', 'Particulars', 'SiteID', 'Site Name', 'Status', 'Action'],
                            colModel:[
                                    {name:'SiteRemittanceID',index:'SiteRemittanceID', width:200, align: 'center'},
                                    {name:'RemittanceName',index:'RemittanceName', width:190, align: 'center'},
                                    {name:'BankID',index:'BankID', width:85, align: 'center'},
                                    {name:'Branch',index:'Branch', width:100, align: 'left'},
                                    {name:'Amount', index: 'Amount', width:200, align: 'right'},
                                    {name:'BankTransactionID',index:'BankTransactionID', width:170, align: 'center'},
                                    {name:'BankTransactionDate',index:'BankTransactionDate', width:200, align: 'center'},
                                    {name:'ChequeNumber',index:'ChequeNumber', width:120, align: 'left'},
                                    {name:'Particulars', index: 'Particulars', width:200, align: 'left'},
                                    {name:'SiteID', index: 'SiteID', width:80, align: 'center'},
                                    {name:'SiteName',index:'SiteName', width:170, align: 'left'},
                                    {name:'Status', index: 'Status', width:80, align: 'center'},
                                    {name:'button', index: 'button', width:260, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1100,
                            pager: '#pager2',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Topup Verified Deposits"
                    });
                jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
                $('#useraccs').trigger("reloadGrid");
                
                sendRemitID2($(this).val());
                var site = document.getElementById('cmbsite').value;

                if(site > 0)
                    {
                        $('#pagination').show();
                        $('#results').hide();
                    }
                else{
                        $('#pagination').hide();
                        $('#results').hide();
                    }
                $('#cmbsiteremit').empty();
                $('#cmbsiteremit').append($("<option />").val("-1").text("All"));
            });
            
            $('#cmbsiteremit').live('change', function(){
                $("#userdata tbody").html("");
                var siteremitid = document.getElementById('cmbsiteremit').value;
                
               if(siteremitid > 0)
                    {
                        $('#pagination').hide();
                        $('#results').show();
                    }
                else{
                        $('#pagination').show();
                        $('#results').hide();
                    }
                    
                var url = '../process/ProcessTopUp.php';
                var data = 'remitpage2="ViewVerifiedSiteRemit"&remitid2='+siteremitid;
                $.ajax({
                   url : url,
                   type : 'get',
                   data : data,
                   dataType : 'json',
                   success : function(data){
                           var tblRow = "<thead>"
                                        +"<tr>"
                                        +"<th colspan='16' class='header'>Remittances</th>"
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>Remittance Type ID</th>"
                                        +"<th>Bank ID</th>"
                                        +"<th>Branch</th>"
                                        +"<th>Amount</th>"
                                        +"<th>Bank Transaction ID</th>"
                                        +"<th>Bank Transaction Date</th>"
                                        +"<th>Cheque Number</th>"
                                        +"<th>Account ID</th>"
                                        +"<th>Particulars</th>"
                                        +"<th>Site ID</th>"
                                        +"<th>Site Name</th>"
                                        +"<th>Status</th>"
                                        +"<th colspan=\"4\">Action</th>"
                                        +"</tr>"
                                        +"</thead>";

                        $.each(data, function(i,user){
                            var Amount = this.Amount;
                            var rstatus = this.Status;
                            var remitID = this.RemittanceTypeID;
                            var BankCode = this.BankCode;
                            var Branch = this.Branch;
                            var BankTransID = this.BankTransactionID;
                            var BankTransDate = this.BankTransactionDate;
                            var rmessage="";
                            if(rstatus == '3')
                               rmessage = "Verified";    
                            
                            if(remitID == 2)
                            {
                                BankCode = " ";
                                Branch = " ";
                                BankTransID = " ";
                                BankTransDate = " ";
                            }
                            
                            
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td>"+this.RemittanceName+"</td>"
                                        +"<td>"+BankCode+"</td>"
                                        +"<td>"+Branch+"</td>"
                                        +"<td>"+CommaFormatted(Amount)+"</td>"
                                        +"<td>"+BankTransID+"</td>"
                                        +"<td>"+BankTransDate+"</td>"
                                        +"<td>"+this.ChequeNumber+"</td>"
                                        +"<td>"+this.AID+"</td>"
                                        +"<td>"+this.Particulars+"</td>"
                                        +"<td>"+this.SiteID+"</td>"
                                        +"<td>"+this.SiteName+"</td>"
                                        +"<td>"+rmessage+"</td>"
                                        +"<td width=\"260px\"><input type=\"button\" value=\"Valid\" onclick=\"window.location.href='../process/ProcessTopUp.php?remitid2="+siteremitid+"&remitstat=0'+'&remittance2='+'UpdateVerifiedRemit';\"/><input type=\"button\" value=\"Invalid\" onclick=\"window.location.href='../process/ProcessTopUp.php?remitid2="+siteremitid+"&remitstat=1'+'&remittance2='+'UpdateVerifiedRemit';\"/></td> " 
//                                        +"<td><input type=\"submit\" class=\"bluebtn\" value=\"Submit\" onclick=\"document.getElementById('frmverifieddep').submit();\" /></td>"
                                        +"</tr>"
                                        +"</tbody>";

                                        //$(tblRow).html($("#userdata tbody"));
                                        $('#results').show();
                                        //$('#userdata').children('tbody').html(tblRow);
                                        $('#userdata').html(tblRow);
                        });
                   },
                   error : function(e) {
                       alert("error in updating status in siteremittance");
                   }
                });
            });
         });

    </script>
    <body>
        
        <div id="pagetitle">Update Verified Deposits</div>
        <br />
        <input type="hidden" name="paginate" id="paginate" value="ViewTopupVerifiedDeposit"/>
        <form method="post" action="../process/ProcessTopUp.php" id="frmverifieddep">
            <input type="hidden" name="page" value="ReversalofDeposits2"/>
            <table>
              <tr>
                   <td width="130px">Sites</td>
                   <td>
                        <?php                        
                            $vsite = $_SESSION['sites'];
                            echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                            echo "<option value=\"-1\">Please Select</option>";

                            foreach ($vsite as $result){
                                       $vsiteID = $result['SiteID'];
                                       $vname = $result['SiteName'];
                                       echo "<option value=\"".$vsiteID."\">".$vname."</option>";
                            }

                            echo "</select>";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td width="130px">Site Remittance</td>
                    <td>
                        <select id="cmbsiteremit" name="cmbsiteremit">
                            <option value="-1">Please Select</option>
                        </select>
                    </td>
                </tr>
            </table>       
            
            <br /><br />
            
           <div id="results" style="display: none;">
              <table id="userdata" class="tablesorter">
             </table>
          </div>
          <div align="center" id="pagination">
            <table border="1" id="useraccs">
            </table>
            <div id="pager2"></div>
          </div>
    </form> 
</div>
    
<?php  include "footer.php"; ?>
