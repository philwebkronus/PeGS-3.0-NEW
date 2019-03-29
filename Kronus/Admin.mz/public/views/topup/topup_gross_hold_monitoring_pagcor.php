<?php 
$pagetitle = "Gross Hold Monitoring";
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
<form method="post" id="frmreport">
<div id="workarea">
    <div id="pagetitle">Gross Hold Monitoring</div>
    <br />
    
    <table>
        <tr>
            <td>Transaction Date</td>
            <td>
                <input type="text" value="<?php echo date('Y-m-d'); ?>" id="startdate" readonly="readonly" name="startdate" />
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
            </td>
        </tr>
        <tr>
            <td>Service Provider </td>
            <td>
                <select name="selserviceCode" id="selserviceCode">
                    <option value="-1">All</option>
                    <!-- COMMENT OUT CCT 12/13/2018 BEGIN -->
                    <?php /* foreach($param['serviceCode'] as $v): */ ?>
                        <!--
                        <option value="<?php /* echo $v['ServiceID']; */ ?>"> <?php /* echo $v['ServiceName']; */ ?></option>
                        -->
                    <?php /* endforeach; */ ?>
                    <!-- COMMENT OUT CCT 12/13/2018 END -->
                </select>       
            </td>
        </tr>
    </table>
    <div id="loading" style="position: fixed; z-index: 5000; background: url('images/Please_wait.gif') no-repeat; height: 162px; width: 260px; margin: 50px 0 0 400px; display: none;"></div>
    <div id="submitarea">
        <input type="button" value="Search" id="btnsearch"/>
    </div>
    <br />
    <div  id="tblgrosshold" style="display: none;width: 1390px; overflow-x: scroll; overflow-y: hidden; height: 100%; "> 
            <table id="tblgrossholdbody"></table>
    </div>
    <br/>
    <div  id="tbltotalgh" style="display: none; width: 1390px; overflow-x: scroll; overflow-y: hidden; height: 100px; ">
        <table id="tbltotalghbody"></table>
    </div>    
    <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div>
</form>    
<style type="text/css">
   .ui-jqgrid .ui-jqgrid-htable th div 
   {
      height: 21px; 
   }   
   
    #tblgrossholdbody 
    {
        width: 1670px;
        height: 240px;
        table-layout: fixed;
        border-collapse: collapse;
    }

    #tblgrosshold 
    {
        border-radius: 5px 5px 5px 5px;
        -moz-border-top-left-radius: 5px;
        -moz-border-top-right-radius: 5px;
        -moz-border-radius-bottomleft: 5px;
        -moz-border-radius-bottomright: 5px;   
        border-left: 2px solid #AAAAAA;
        border-top: 2px solid #AAAAAA;
        border-right: 3px solid #AAAAAA;
        border-bottom: 3px solid #AAAAAA;
    }

    #tblgrossholdbody th 
    {
        text-align: center;
        height: 25px;
    }

    #tblgrossholdbody tr:nth-child(1) th:nth-child(1) 
    {
        padding-left: 10px;
        border:none;
    }

    #tblgrossholdbody tr:nth-child(2) th:nth-child(1) 
    { 
        width: 150px; 
        border-top: 1px solid #D6EB99;
        border-right: 1px solid #D6EB99;
        border-bottom: 1px solid #D6EB99;
    }

    #tblgrossholdbody td:nth-child(1) 
    {
        width: 150px;
        border-top: 2px solid #AAAAAA;
        border-right: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tblgrossholdbody th:nth-child(2), #tblgrossholdbody th:nth-child(3), #tblgrossholdbody th:nth-child(4), #tblgrossholdbody th:nth-child(5), 
    #tblgrossholdbody th:nth-child(6), #tblgrossholdbody th:nth-child(7), #tblgrossholdbody th:nth-child(8), #tblgrossholdbody th:nth-child(9), 
    #tblgrossholdbody th:nth-child(10), #tblgrossholdbody th:nth-child(11), #tblgrossholdbody th:nth-child(12), #tblgrossholdbody th:nth-child(13), 
    #tblgrossholdbody th:nth-child(14), #tblgrossholdbody th:nth-child(15),#tblgrossholdbody th:nth-child(16),#tblgrossholdbody th:nth-child(17),
    
    #tblgrossholdbody th:nth-child(18)
    {
        width: 90px;  
        border-top: 2px solid #D6EB99;
        border-right: 2px solid #D6EB99;
        border-bottom: 2px solid #D6EB99;
    }

    #tblgrossholdbody th:nth-child(18) 
    { 
        min-width: 90px;  
        padding-right: 5px;
    }

    #tblgrossholdbody td:nth-child(2), #tblgrossholdbody td:nth-child(3), #tblgrossholdbody td:nth-child(4), #tblgrossholdbody td:nth-child(5), 
    #tblgrossholdbody td:nth-child(6), #tblgrossholdbody td:nth-child(7), #tblgrossholdbody td:nth-child(8), #tblgrossholdbody td:nth-child(9), 
    #tblgrossholdbody td:nth-child(10), #tblgrossholdbody td:nth-child(11), #tblgrossholdbody td:nth-child(12), #tblgrossholdbody td:nth-child(13), 
    
    #tblgrossholdbody td:nth-child(14),#tblgrossholdbody td:nth-child(15),#tblgrossholdbody td:nth-child(16),#tblgrossholdbody td:nth-child(17)
    {
        min-width: 90px;
        border-top: 2px solid #AAAAAA;
        border-right: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tblgrossholdbody td:nth-child(18) 
    {
        min-width: 90px;
        border-top: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tblgrossholdbody thead 
    {
        background-color: #3d561c; 
        color: white;
    }
    
    #tblgrossholdbody thead tr 
    {
        display: block;
        position: relative;
    }
    
    #tblgrossholdbody tbody 
    {
        display: block;
        overflow: auto;
        width: 100%;
        height: 290px;
    }

    #tbltotalghbody 
    {
        width: 1550px;
        height: 200px;
        table-layout: fixed;
        border-collapse: collapse;
    }

    #tbltotalgh 
    {
        border-radius: 5px 5px 5px 5px;
        -moz-border-top-left-radius: 5px;
        -moz-border-top-right-radius: 5px;
        -moz-border-radius-bottomleft: 5px;
        -moz-border-radius-bottomright: 5px;   
        border-left: 2px solid #AAAAAA;
        border-top: 2px solid #AAAAAA;
        border-right: 3px solid #AAAAAA;
        border-bottom: 3px solid #AAAAAA;
    }

    #tbltotalghbody th 
    {
        text-align: center;
        height:25px;
    }

    #tbltotalghbody tr:nth-child(1) th:nth-child(1) 
    { 
        width: 100px;   
        border-top: 1px solid #D6EB99;
        border-right: 1px solid #D6EB99;
        border-bottom: 1px solid #D6EB99;
    }

    #tbltotalghbody td:nth-child(1) 
    {
        width: 100px;  
        border-top: 2px solid #AAAAAA;
        border-right: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tbltotalghbody th:nth-child(2), #tbltotalghbody th:nth-child(3), #tbltotalghbody th:nth-child(4), #tbltotalghbody th:nth-child(5), 
    #tbltotalghbody th:nth-child(6), #tbltotalghbody th:nth-child(7), #tbltotalghbody th:nth-child(8), #tbltotalghbody th:nth-child(9), 
    #tbltotalghbody th:nth-child(10), #tbltotalghbody th:nth-child(11), #tbltotalghbody th:nth-child(12), #tbltotalghbody th:nth-child(13), 
    #tbltotalghbody th:nth-child(14), #tbltotalghbody th:nth-child(15)
    { 
        width: 100px;  
        border-top: 2px solid #D6EB99;
        border-right: 2px solid #D6EB99;
        border-bottom: 2px solid #D6EB99;
    }

    #tbltotalghbody th:nth-child(15) 
    { 
        min-width: 100px;  
        padding-right: 5px;
    }

    #tbltotalghbody td:nth-child(2), #tbltotalghbody td:nth-child(3), #tbltotalghbody td:nth-child(4), #tbltotalghbody td:nth-child(5), 
    #tbltotalghbody td:nth-child(6), #tbltotalghbody td:nth-child(7), #tbltotalghbody td:nth-child(8), #tbltotalghbody td:nth-child(9), 
    #tbltotalghbody td:nth-child(10), #tbltotalghbody td:nth-child(11), #tbltotalghbody td:nth-child(12), #tbltotalghbody td:nth-child(13), 
    
    #tbltotalghbody td:nth-child(14)
    {
        min-width: 100px;
        border-top: 2px solid #AAAAAA;
        border-right: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tbltotalghbody td:nth-child(15) 
    {
        min-width: 100px;
        border-top: 2px solid #AAAAAA;
        border-bottom: 2px solid #AAAAAA;
    }

    #tbltotalghbody thead 
    {
        background-color: #3d561c; 
        color: white;
    }
    
    #tbltotalghbody thead tr 
    {
        display: block;
        position: relative;
    }
    
    #tbltotalghbody tbody 
    {
        display: block;
        overflow: auto;
        width: 100%;
        height: 290px;
    }
</style>
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
        jQuery(document).ready(function()
        {
            function toMoney(val,noprefix) 
            {
                var pre = 'PhP ';
                if(noprefix != undefined) 
                {
                    pre = '';
                }

                return accounting.formatMoney(val, pre, 2, ",", ".");
            }

            jQuery('#btnsearch').click(function()
            {
                if(!validateDateTopup()) 
                {
                    return false;
                }

                var siteid = '';
                var startdate = jQuery('#startdate').val();
                var comp1 = '';
                var comp2 = '';
                var num1 = '';
                var num2 = '';
                var servprovider = jQuery('#selserviceCode').val();

                document.getElementById('loading').style.display='block';
                document.getElementById('fade').style.display='block';

                $.ajax(
                {
                    url: 'process/ProcessTopUpPaginate.php?action=getghdatapagcor&sellocation='+''+'&comp1='+''+'&num1='+''+'&comp2='+''+'&num2='+''+'&servProviderID='+servprovider+'',
                    type: 'GET',
                    data : 
                    {
                        sord : function() {return "asc"; },
                        startdate : function(){return startdate;}
                    },
                    dataType: 'json',
                    success: function(data)
                    {
                        $("#tblgrosshold").css("display", "block");
                        $("#tbltotalgh").css("display", "block");

                        var header = "<thead ><tr><th  id='tblheader-banner' colspan='15' style='font-style: Arial, calibri, helvetica; font-size: 14px; font-weight: bold; background-color: #3d561c; color: white; height: 30px;'>Gross Hold Monitoring</th>"+
                            "</tr><tr style='font-style: Arial, calibri, helvetica; font-size: 12px; text-align: center;font-weight: bold; background-color: #D6EB99; color: black; height: 40px;'>"+
                            "<th>Site / PEGS Name</th><th>BCF</th><th>Deposit</th><th>e-SAFE Loads</th>"+
                            "<th>Reload</th><th>Redemptions</th><th>e-SAFE Withdrawal</th><th>Manual Redemption</th>"+
                            "<th>Printed Tickets</th><th>Active Tickets\n for the Day</th><th>Running Active\n Tickets</th><th>Coupons</th>"+
                            "<th>Cash on Hand</th><th>Replenishment</th><th>Collection</th><th>Ending\n Balance</th><th>Location</th>"+
                            "</tr></thead>";

                        $("#tblgrossholdbody").html("");
                        $("#tbltotalghbody").html("");
                        $("#tblgrossholdbody").html(header);

                        $('#loading').hide();
                        document.getElementById('loading').style.display='none';
                        document.getElementById('fade').style.display='none';
                        $("#tblgrossholdbody").append("<tbody>");

                        var totaldeposit = 0;
                        var totalewalletdeposit = 0;
                        var totalreload = 0;
                        var totalredemptions = 0;
                        var totalewalletwithdrawal = 0;
                        var totalmanualredemption = 0;
                        var totalprintedtickets = 0;
                        var totalactivetickets = 0;
                        var totalrunningtickets = 0;
                        var totalcoupons = 0;
                        var totalcoh = 0;
                        var totalgrosshold = 0;
                        var totalreplenishment = 0;
                        var totalcollection = 0;
                        var totalendingbalance = 0;

                        var totaldepositz = 0;
                        var totalewalletdepositz = 0;
                        var totalreloadz = 0;
                        var totalredemptionsz = 0;
                        var totalewalletwithdrawalz = 0;
                        var totalmanualredemptionz = 0;
                        var totalprintedticketsz = 0;
                        var totalactiveticketsz = 0;
                        var totalrunningticketsz = 0;
                        var totalcouponsz = 0;
                        var totalcohz = 0;
                        var totalgrossholdz = 0;
                        var totalreplenishmentz = 0;
                        var totalcollectionz = 0;
                        var totalendingbalancez = 0;

                        for(var itr = 0; itr < data.CountOfSites; itr++)
                        {
                            var part = "<tr id='"+data[itr].POS+"'><td>"+data[itr].SiteName+"</td><td style='text-align: right;'>"+data[itr].BCF+"</td>"+
                                "<td style='text-align: right;'>"+data[itr].Deposit+"</td><td style='text-align: right;'>"+data[itr].EwalletLoad+"</td><td style='text-align: right;'>"+data[itr].Reload+"</td><td style='text-align: right;'>"+data[itr].Withdrawal+"</td><td style='text-align: right;'>"+data[itr].EwalletWithdrawal+"</td><td style='text-align: right;'>"+data[itr].ManualRedemption+"</td>"+
                                "<td style='text-align: right;'>"+data[itr].PrintedTickets+"</td>"+"<td style='text-align: right;'>"+data[itr].UnusedTickets+"</td>"+"<td style='text-align: right;'>"+data[itr].RunningActiveTickets+"</td>"+"<td style='text-align: right;'>"+data[itr].Coupon+"</td>"+
                                "<td style='text-align: right;'>"+data[itr].CashonHand+"</td><td>"+data[itr].Replenishment+"</td><td>"+data[itr].Collection+"</td><td>"+data[itr].EndingBalance+"</td><td>"+data[itr].Location+"</td></tr>";

                            $("#tblgrossholdbody").append(part);

                            totaldepositz = data[itr].Deposit;
                            totaldeposit += Number(totaldepositz.replace(/[^0-9-\.]+/g,""));

                            totalewalletdepositz = data[itr].EwalletLoad;
                            totalewalletdeposit += Number(totalewalletdepositz.replace(/[^0-9-\.]+/g,""));

                            totalreloadz = data[itr].Reload;
                            totalreload += Number(totalreloadz.replace(/[^0-9-\.]+/g,""));

                            totalredemptionsz = data[itr].Withdrawal;
                            totalredemptions += Number(totalredemptionsz.replace(/[^0-9-\.]+/g,""));

                            totalewalletwithdrawalz = data[itr].EwalletWithdrawal;
                            totalewalletwithdrawal += Number(totalewalletwithdrawalz.replace(/[^0-9-\.]+/g,""));

                            totalmanualredemptionz = data[itr].ManualRedemption;
                            totalmanualredemption += Number(totalmanualredemptionz.replace(/[^0-9-\.]+/g,""));

                            totalprintedticketsz = data[itr].PrintedTickets;
                            totalprintedtickets += Number(totalprintedticketsz.replace(/[^0-9-\.]+/g,""));

                            totalactiveticketsz = data[itr].UnusedTickets;
                            totalactivetickets += Number(totalactiveticketsz.replace(/[^0-9-\.]+/g,""));

                            totalrunningticketsz = data[itr].RunningActiveTickets;
                            totalrunningtickets += Number(totalrunningticketsz.replace(/[^0-9-\.]+/g,""));

                            totalcouponsz = data[itr].Coupon;
                            totalcoupons += Number(totalcouponsz.replace(/[^0-9-\.]+/g,""));

                            totalcohz = data[itr].CashonHand;
                            totalcoh += Number(totalcohz.replace(/[^0-9-\.]+/g,""));

                            totalgrossholdz = data[itr].GrossHold;
                            totalgrosshold += Number(totalgrossholdz.replace(/[^0-9-\.]+/g,""));

                            totalreplenishmentz = data[itr].Replenishment;
                            totalreplenishment += Number(totalreplenishmentz.replace(/[^0-9-\.]+/g,""));

                            totalcollectionz = data[itr].Collection;
                            totalcollection += Number(totalcollectionz.replace(/[^0-9-\.]+/g,""));

                            totalendingbalancez = data[itr].EndingBalance;
                            totalendingbalance += Number(totalendingbalancez.replace(/[^0-9-\.]+/g,""));

                            var grosshold = data[itr].GrossHold;
                            var balance = data[itr].BCF;
                            var gh = grosshold.replace(/,/g,"");
                            var bcf = balance.replace(/,/g,"");
                            var threshold = data[itr].MinBalance;
                            var cazhonhand = data[itr].CashonHand;

                            //if site balance < minimum threshold, color will be red
                            if(parseFloat(bcf) < parseFloat('<?php echo _RED2_; ?>')) 
                            {
                                $('#' + data[itr].POS).css('background-color','red');
                            }
                            else
                            { //if gross hold >= 200K, color row will be green
                                if(parseFloat(cazhonhand) >= parseFloat('<?php echo _GREEN_1; ?>')) 
                                {
                                    $('#' + data[itr].POS).css('background-color','#32CD32');
                                } 
                            }
                        }

                        var totalgh = "<thead>"+
                            "<tr style='font-style: Arial, calibri, helvetica; font-size: 12px; text-align: center;font-weight: bold; background-color: #D6EB99; color: black; height: 40px;'>"+
                            "<th>Total</th><th>Deposit</th><th>e-SAFE Loads</th>"+
                            "<th>Reload</th><th>Redemptions</th><th>e-SAFE Withdrawal</th><th>Manual Redemption</th>"+
                            "<th>Printed Tickets</th><th>Active Tickets\n for the Day</th><th>Running Active\n Tickets</th><th>Coupons</th>"+
                            "<th>Cash on Hand</th><th>Replenishment</th><th>Collection</th><th>Ending Balance</th>"+
                            "</tr></thead>"+
                            "<tr>"+
                            "<td></td><td style='text-align: right;'>"+toMoney(totaldeposit,'')+"</td><td style='text-align: right;'>"+toMoney(totalewalletdeposit,'')+"</td>"+
                            "<td style='text-align: right;'>"+toMoney(totalreload,'')+"</td><td style='text-align: right;'>"+toMoney(totalredemptions,'')+"</td><td style='text-align: right;'>"+toMoney(totalewalletwithdrawal,'')+"</td><td style='text-align: right;'>"+toMoney(totalmanualredemption,'')+"</td>"+
                            "<td style='text-align: right;'>"+toMoney(totalprintedtickets,'')+"</td><td style='text-align: right;'>"+toMoney(totalactivetickets,'')+"</td><td style='text-align: right;'>"+toMoney(totalrunningtickets,'')+"</td><td style='text-align: right;'>"+toMoney(totalcoupons,'')+"</td>"+
                            "<td style='text-align: right;'>"+toMoney(totalcoh,'')+"</td><td style='text-align: right;'>"+toMoney(totalreplenishment,'')+"</td><td style='text-align: right;'>"+toMoney(totalcollection,'')+"</td><td style='text-align: right;'>"+toMoney(totalendingbalance,'')+"</td></tr>";

                            $("#tbltotalghbody").append(totalgh);
                            $("#tblgrossholdbody").append("</tbody>");          
                    }
                });
            });
        });
</script>
<?php  
    }
}
include "footer.php"; ?>