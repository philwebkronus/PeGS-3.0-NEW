<script type="text/javascript">
    if ($('#siteamountinfo').val() == 0) {
        showLightbox(function() {
            updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                    ''
                    );
        });
    }
    $(document).ready(function() {
        $('#txtDate').datepicker({
            inline: true,
            changeMonth: false,
            changeYear: false,
            dateFormat: 'yy-mm-dd',
            maxDate: '<?php echo date('Y-m-d') ?>',
//            minDate: -1,
        });
        $('#txtDate').change(function() {

            var url = "<?php echo Mirage::app()->createUrl('viewtrans/overview3'); ?>";
            var d = $('#txtDate').val();
            var data = 'date=' + d;

            var res = d.split("-");
            var formattedDate = res[1] + res[2] + res[0];

            $.ajax({
                url: url,
                data: data,
                success: function(data) {
                    try {
                        if (data !== '') {
                            var details = JSON.parse(data);

                            var fileName = details.site_code + '_' + formattedDate + '_' + details.generated_date;
                            JSONToCSVConvertor(details.trans_details, details.manual_redemption, details.coverageStart, details.coverageEnd, details.genreated_date_formatted, fileName, true);
                        }
                    } catch (e) {
                        alert('Oops! Something went wrong parsing data. Please try again');
                    }
                    hideLightbox();
                },
                error: function(e) {
                    alert(e);
                    hideLightbox();
                }
            });
        });
    });

    function JSONToCSVConvertor(JSONData1, JSONData2, coverageStart, coverageEnd, timegenerated, fileName, ShowLabel) {

        var CSV = '';

        CSV += 'File Name : ' + fileName + '\r\n' + '\r\n';

        CSV += 'Report Coverage : ' + coverageStart + ' to ' + coverageEnd + '\r\n' + '\r\n';

        if (JSON.stringify(JSONData1).length > 2) {
            var arrData = typeof JSONData1 != 'object' ? JSON.parse(JSONData1) : JSONData1;
            if (ShowLabel) {
                var row = "";
                for (var index in arrData[0]) {
                    row += index + ',';
                }
                row = row.slice(0, -1);
                CSV += row + '\r\n';
            }
            for (var i = 0; i < arrData.length; i++) {
                var row = "";
                for (var index in arrData[i]) {
                    var arrValue = arrData[i][index] == null ? "" : '="' + arrData[i][index] + '"';
                    row += arrValue + ',';
                }
                row.slice(0, row.length - 1);
                CSV += row + '\r\n';
            }

            CSV += '\r\n';
        }
        else {
            CSV += 'No data retrived for Transaction Details.' + '\r\n' + '\r\n';
        }

        if (JSON.stringify(JSONData2).length > 4) {
            var arrData = typeof JSONData2 != 'object' ? JSON.parse(JSONData2) : JSONData2;
            if (ShowLabel) {
                var row = "";
                for (var index in arrData[0]) {
                    row += index + ',';
                }
                row = row.slice(0, -1);
                CSV += row + '\r\n';
            }
            for (var i = 0; i < arrData.length; i++) {
                var row = "";
                for (var index in arrData[i]) {
                    var arrValue = arrData[i][index] == null ? "" : '="' + arrData[i][index] + '"';
                    row += arrValue + ',';
                }
                row.slice(0, row.length - 1);
                CSV += row + '\r\n';
            }
        } else {
            CSV += 'No data retrived for Manual Redemtion.' + '\r\n' + '\r\n';
        }
        CSV += '\r\n' + 'System Rundate : ' + timegenerated + '\r\n';

        if (CSV == '') {
            alert('Oops! Something went wrong with empty data. Please try again');
        }

        if (msieversion()) {
            var IEwindow = window.open("", "", 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=0,height=0');
            IEwindow.resizeTo(0, 0);
            IEwindow.moveTo(1000000, 1000000);
            IEwindow.document.write('sep=,\r\n' + CSV);
            IEwindow.document.close();
            IEwindow.document.execCommand('SaveAs', true, fileName + ".csv");
            IEwindow.focus();
            IEwindow.close();
        } else {
            var uri = 'data:application/csv;charset=utf-8,' + escape(CSV);
            var link = document.createElement("a");
            link.href = uri;
            link.style = "visibility:hidden";
            link.download = fileName + ".csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    function msieversion() {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");
        if (msie != -1 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return version number 
        {
            return true;
        } else { // If another browser, 
            return false;
        }
        return false;
    }
</script>


<input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
<h1>Transaction Details Per Cut-off</h1>
<input id="txtDate" type="text" readonly="readonly" value="<?php echo date('Y-m-d'); ?>"/>

