<?php
include 'process/ProcessRptPegs.php';
$pagetitle = "Site Demographics";
include 'header.php';
$vaccesspages = array('8');
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
<script type="text/javascript">
    $(document).ready(function(){
        $('#cmbislands').live('change', function(){                    
            sendIslandID($(this).val());
            $('#cmbregions').empty();
            $('#cmbregions').append($("<option />").val("-1").text("Please Select"));
            $('#cmbprovinces').empty();
            $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
            $('#cmbcity').empty();
            $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
            $('#cmbbrgy').empty();
            $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
            $('#useraccs').GridUnload();
            $("#senchaexport1").hide();
        });

        $('#cmbregions').live('change', function(){
            sendRegionID($(this).val());
            $('#cmbprovinces').empty();
            $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
            $('#cmbcity').empty();
            $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
            $('#cmbbrgy').empty();
            $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
            $('#useraccs').GridUnload();
            $("#senchaexport1").hide();
        });

        $('#cmbprovinces').live('change', function(){
            sendProvID($(this).val());
            $('#cmbcity').empty();
            $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
            $('#cmbbrgy').empty();
            $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
            $('#useraccs').GridUnload();
            $("#senchaexport1").hide();
        });    

        $("#cmbcity").live('change', function(){
            $('#useraccs').GridUnload();
            $("#senchaexport1").hide();
        });
    });
    function demog(){
            var IslandID = document.getElementById('cmbislands').value;
            var RegionID = document.getElementById('cmbregions').value;
            var ProvinceID = document.getElementById('cmbprovinces').value;
            var CityID = document.getElementById('cmbcity').value;
            var url;

            if(IslandID > 0)
            {
                url = "process/ProcessRptPegs.php?island="+IslandID;
            }

            if(IslandID == 0)
            {
                url = "process/ProcessRptPegs.php?all="+IslandID;
            }


            if(RegionID > 0)
            {
                url = "process/ProcessRptPegs.php?region="+RegionID;
            }

            if(ProvinceID > 0)    
            {
                url = "process/ProcessRptPegs.php?province="+ProvinceID;
            }

            if(CityID > 0)
            {
                url = "process/ProcessRptPegs.php?city="+CityID;
            }

            jQuery("#useraccs").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            rptpage: function() {return $("#txtsitedemog").val();}
                              },
                    datatype: "json",
                    colNames:['POS Account','Site / PEGS Name','Site / PEGS Code'],
                    colModel:[
                            {name:'POSAccountNo', index:'POSAccountNo', width:200, align:'center'},
                            {name:'SiteName',index:'SiteName', width:150, align: 'left'},
                            {name:'SiteCode',index:'SiteCode', width:200, align: 'left'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 280,
                    width: 800,
                    pager: '#pager2',
                    sortname: 'SiteName',
                    viewrecords: true,
                    sortorder: "asc",
                    caption: "Site Demographics"
            });
            jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
            $('#useraccs').trigger("reloadGrid");
            $("#senchaexport1").show();
    }

    function exportDemogsPDF()
    {
          var IslandID = document.getElementById('cmbislands').value;
          var RegionID = document.getElementById('cmbregions').value;
          var ProvinceID = document.getElementById('cmbprovinces').value;
          var CityID = document.getElementById('cmbcity').value;
          var url;
          var date = document.getElementById('rptDate').value;
          if(IslandID > 0)
          {
             url = "process/ProcessRptPegs.php?island="+IslandID+"&getpage=PDFDemographics&date="+date;
          }

          if(IslandID == 0)
          {
             url = "process/ProcessRptPegs.php?all="+IslandID+"&getpage=PDFDemographics&date="+date;
          }

          if(RegionID > 0)
          {
             url = "process/ProcessRptPegs.php?region="+RegionID+"&getpage=PDFDemographics&date="+date;
          }

          if(ProvinceID > 0)    
          {
             url = "process/ProcessRptPegs.php?province="+ProvinceID+"&getpage=PDFDemographics&date="+date;
          }

          if(CityID > 0)
          {
             url = "process/ProcessRptPegs.php?city="+CityID+"&getpage=PDFDemographics&date="+date;
          }

          window.location.href= url;
    }

    function exportExcelDemog()
    {
          var IslandID = document.getElementById('cmbislands').value;
          var RegionID = document.getElementById('cmbregions').value;
          var ProvinceID = document.getElementById('cmbprovinces').value;
          var CityID = document.getElementById('cmbcity').value;
          var url;
          var date = document.getElementById('rptDate').value;
          if(IslandID > 0)
          {
             url = "process/ProcessRptPegs.php?island="+IslandID+"&getpage=ExcelDemographics&fn=SiteDemographics&date="+date;
          }

          if(IslandID == 0)
          {
             url = "process/ProcessRptPegs.php?all="+IslandID+"&getpage=ExcelDemographics&fn=SiteDemographics&date="+date;
          }

          if(RegionID > 0)
          {
             url = "process/ProcessRptPegs.php?region="+RegionID+"&getpage=ExcelDemographics&fn=SiteDemographics&date="+date;
          }

          if(ProvinceID > 0)    
          {
             url = "process/ProcessRptPegs.php?province="+ProvinceID+"&getpage=ExcelDemographics&fn=SiteDemographics&date="+date;
          }

          if(CityID > 0)
          {
             url = "process/ProcessRptPegs.php?city="+CityID+"&getpage=ExcelDemographics&fn=SiteDemographics&date="+date;
          }

          window.location.href= url;
    }
</script>
<link rel="stylesheet" type="text/css" href="css/senchadiv.css" />
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <input type="hidden" name="txtsitedemog" id="txtsitedemog" value="SiteDemographics" />
    <form method="post" id="frmdemog">
        <input type="hidden" name="pdf" value="PDFDemographics" />
        <table>
            <tr>
                <td>Island</td>
                <td>
                    <?php
                        //display all islands
                        $vislands = $_SESSION['resislands'];
                        echo "<select id=\"cmbislands\" name=\"cmbislands\">";
                        echo "<option value=\"0\">All</option>";

                        foreach ($vislands as $result){
                           $vname = $result['IslandName'];
                           $vIslandId = $result['IslandID'];
                           echo "<option value=\"".$vIslandId."\">".$vname."</option>";
                        }
                        echo "</select>";
                    ?>
                </td>
            </tr>
            <tr>
                <td>Region</td>
                <td>
                    <select id="cmbregions" name="cmbregions">
                       <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Province</td>
                <td>
                    <select id="cmbprovinces" name="cmbprovinces">
                       <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>City</td>
                <td>
                    <select id="cmbcity" name="cmbcity">
                       <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>
    <input type="hidden" name="rptDate" id="rptDate" value="<?php echo date("Y-m-d")?>"/>
    <div id="submitarea">
      <input type="button" id="btndemogs" name="btndemogs" value="Site Demographics" onclick="demog();"/>
    </div>
    <br />
    <div align="center" id="pagination">
        <table border="1" id="useraccs">

        </table>
        <div id="pager2"></div>
    </div>
    <div id="senchaexport1" style="display: none;">
        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' onclick="exportDemogsPDF();"/>
        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" onclick="exportExcelDemog();"/>
    </div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>