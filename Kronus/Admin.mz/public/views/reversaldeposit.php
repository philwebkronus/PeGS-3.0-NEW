<?php $pagetitle = "Verify Deposits";  ?>

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
                                paginate: function() {return $("#paginate").val();},
                                cmbsite: function() {return $('#cmbsite').val();}
                                  },
                        datatype: "json",
                        colNames:['Remittance Type','Bank Name', 'Branch', 'Amount', 'BankTransID', 'BankTransDate', 'Cheque#', 'Particulars', 'SiteID', 'Site Name', 'Status', 'Action'],
                        colModel:[
                                {name:'RemittanceTypeID',index:'RemittanceTypeID', align: 'center'},
                                {name:'BankID',index:'BankID', align: 'center'},
                                {name:'Branch',index:'Branch', align: 'left'},
                                {name:'Amount', index: 'Amount', align: 'right'},
                                {name:'BankTransactionID',index:'BankTransactionID', align: 'center'},
                                {name:'BankTransactionDate',index:'BankTransactionDate', align: 'center'},
                                {name:'ChequeNumber',index:'ChequeNumber', align: 'left'},
                                {name:'Particulars', index: 'Particulars', align: 'left'},
                                {name:'SiteID', index: 'SiteID', align: 'center'},
                                {name:'SiteName',index:'SiteName', align: 'left'},
                                {name:'Status', index: 'Status', align: 'center'},
                                {name:'button', index: 'button', align: 'center'}
                        ],

                        rowNum:10,
                        rowList:[10,20,30],
                        height: 280,
                        width: 1200,
                        pager: '#pager2',
                        viewrecords: true,
                        sortorder: "asc",
                        caption:"Topup Reversal"
                });
            jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
            $('#useraccs').trigger("reloadGrid");

            sendRemitID($(this).val());
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
            var data = 'remitpage="ViewSiteRemit"&remitid='+siteremitid;
            $.ajax({
               url : url,
               type : 'get',
               data : data,
               dataType : 'json',
               success : function(data){
                       var tblRow = "<thead>"
                                    +"<tr>"
                                    +"<th colspan='13' class='header'>Remittances</th>"
                                    +"</tr>"
                                    +"<tr>"
                                    +"<th>Remittance Type</th>"
                                    +"<th>Bank Name</th>"
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
                                    +"<th>Action</th>"
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
                        if(rstatus == '0')
                            rmessage = "Valid";
                        else if(rstatus == '2')
                            rmessage = "Pending";
                        else
                            rmessage = "Invalid"; 
                        
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
                                    +"<td><input type=\"submit\" value=\"Verified\" onclick=\"document.getElementById('frmreversal').submit();\" /></td>"
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

    <div id="pagetitle">Verify Deposits</div>
    <br />
    <input type="hidden" name="paginate" id="paginate" value="ViewTopupReversal"/>
    <form method="post" action="../process/ProcessTopUp.php" id="frmreversal">
        <input type="hidden" name="page" value="VerifiedDeposit"/>
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
    </form>
    <div id="results" style="display: none;">
        <table id="userdata" class="tablesorter">

        </table>
    </div>
    <div align="center" id="pagination">
        <table border="1" id="useraccs">

        </table>
        <div id="pager2"></div>
    </div>
</div>

<?php  include "footer.php"; ?>

