<?php
$pagetitle = "LaunchPad Transfer Transactions";
include 'process/ProcessAppSupport.php';
include 'header.php';
$vaccesspages = array('6', '9', '11', '12', '18');
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
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <form method="post" action="#">
        <br />
            <table align="left">
                <tr>
                    <td width="130px">Site / PEGS</td>
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

                                //removes Site HEad Office
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
                <tr>
                    <td>Terminals</td>
                    <td>
                        <select id="cmbterm" name="cmbterminal">
                            <option value="-1">Please Select</option>
                        </select>
                        <label id="txttermname"></label>
                    </td>
                </tr>
                <tr>
                    <td>Transaction Date</td>
                    <td>
                        <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                        <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                    </td>
                </tr>
            </table>
            <div id="submitarea" style="float: right;">
                <input type="button" id="btnquery" value="Query" />
            </div>
            <div id="lbldaterange" align="left" style="float: left;"></div>
            <!-- Transfer Transaction Logs-->
            <div align="center" style="float:left;margin-top: 20px;">
                <table border="1" id="transfertranslogs"></table>
                <div id="pager1"></div>
            </div>
        </form>
    </div>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        var url = 'process/ProcessAppSupport.php';

        function getDateRange(startdate)
        {
            var date1= new Date(startdate);
            var msg = "";
            var numberOfDaysToAdd = 1;
            date1.setDate(date1.getDate() + numberOfDaysToAdd); 

            //Format Date Year-Month-Day Hour:Minutes:Seconds
            var mm = date1.getMonth() + 1;
            var dd = date1.getDate();
            var yy = date1.getFullYear();

            //condition for formatting month
            mm = mm < 10 ? mm = "0"+mm:mm=mm;

            //condition for formatting day
            dd = dd < 10 ? dd = "0"+dd:dd=dd;

            var date2 =  yy + "-" + mm + "-" + dd + " 06:00:00";
            msg = "Report Date Range: " + startdate + " 06:00:00 AM  to  " + date2 + " AM";
            return msg;
        }

        //ajax call: loading of sites
        jQuery('#cmbsite').live('change', function()
        {
            jQuery("#txttermname").text(" ");
            sendSiteID($(this).val());
            $('#cmbterm').empty();
            $('#cmbterm').append($("<option />").val("-1").text("Please Select"));
            jQuery.ajax(
            {
                url: url,
                type: 'post',
                data: 
                {
                    cmbsitename: function(){return jQuery("#cmbsite").val();}
                },
                dataType: 'json',
                success: function(data)
                {
                    if(jQuery("#cmbsite").val() > 0)
                    {
                        jQuery("#txtsitename").text(jQuery("#cmbsite").val()+" / "+data.SiteName);
                    }
                    else
                    {   
                        jQuery("#txtsitename").text(" ");
                    }
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
        }); 

        //ajax call: get sites with transactions
        $('#cmbterm').live('change', function()
        {  
            jQuery.ajax(
            {
                url : url,
                type: 'get',
                data: 
                {
                    cmbterminal: function(){ return jQuery("#cmbterm").val();}
                },
                dataType : 'json',
                success: function(data)
                {
                    if(jQuery("#cmbterm").val() > 0)
                    {
                        jQuery("#txttermname").text(jQuery("#cmbterm").val()+" / "+data.TerminalName);
                    }
                    else
                    {
                        jQuery("#txttermname").text(" ");
                    }
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
        });

        //upon clicking of button; display the grid
        jQuery("#btnquery").click(function()
        {
            var date1 = $("#popupDatepicker1").val();
            var daterange = "";
            //get date range label
            daterange = getDateRange(date1);
            $("#lbldaterange").html("<p>"+daterange+"</p>");
            //for displaying transaction/s in the grid
            gettransferlogs();
        });
    });

    function gettransferlogs()
    {
        summaryid = 0;
        jQuery("#transfertranslogs").GridUnload();
        var url = 'process/ProcessAppSupport.php';
        jQuery("#transfertranslogs").jqGrid(
        {
            url: url,
            mtype: 'post',
            postData: 
            {
                cmbsite: function() {return $('#cmbsite').val(); },
                cmbterminal: function() { return $("#cmbterm").val(); },
                txtDate1: function() { return $("#popupDatepicker1").val(); },
                paginate: function() {return 'LPTransferLogs';}
            },
            datatype: "json",
            colNames:['Transfer ID', 'UB Card', 
                        'From Type', 'Amount', 'Service', 'Start Date', 'End Date', 
                        'Service Trans ID', 'Service Status', 'Status', 
                        'To Type', 'Amount', 'Service', 'Start Date', 'End Date', 
                        'Service Trans ID',  'Service Status', 'Status', 'Transfer Status',],
            colModel:
                [
                    {name:'TransferID',index:'TransferID',align:'left', width: 120},
                    {name:'LoyaltyCardNumber',index:'LoyaltyCardNumber', align: 'left', width: 80},
                    {name:'FromTransactionType',index:'FromTransactionType', align: 'left', width: 90},
                    {name:'FromAmount',index:'FromAmount',align:'right', width: 110},
                    {name:'FromServiceID',index:'FromServiceID',align:'left', width: 150},
                    {name:'FromStartTransDate',index:'FromStartTransDate', align: 'left', width: 120},
                    {name:'FromEndTransDate',index:'FromEndTransDate', align: 'left', width: 120},
                    {name:'FromServiceTransID',index:'FromServiceTransID',align:'left', width: 80},
                    {name:'FromServiceStatus',index:'FromServiceStatus',align:'left', width: 80},
                    {name:'FromStatus',index:'FromStatus',align:'left', width: 80},
                    {name:'ToTransactionType',index:'ToTransactionType', align: 'left', width: 80},
                    {name:'ToAmount',index:'ToAmount',align:'right', width: 110},
                    {name:'ToServiceID',index:'ToServiceID',align:'left', width: 150},
                    {name:'ToStartTransDate',index:'ToStartTransDate', align: 'left', width: 120},
                    {name:'ToEndTransDate',index:'ToEndTransDate', align: 'left', width: 120},
                    {name:'ToServiceTransID',index:'ToServiceTransID',align:'left', width: 80},
                    {name:'ToServiceStatus',index:'ToServiceStatus',align:'left', width: 80},
                    {name:'ToStatus',index:'ToStatus',align:'left', width: 80},
                    {name:'TransferStatus',index:'TransferStatus',align:'left', width: 190}                    
                ],
            rowNum:10,
            rowList:[10,20,30],
            height: 240,
            width: 2040,
            pager: '#pager1',
            refresh: true,
            viewrecords: true,
            sortorder: "asc",
            caption:"LaunchPad Transfer Transactions"
        });
        jQuery("#transfertranslogs").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false, refresh: true});
        $('#transfertranslogs').trigger("reloadGrid");
    }
</script>
<?php  
        }
    }
include "footer.php"; ?>