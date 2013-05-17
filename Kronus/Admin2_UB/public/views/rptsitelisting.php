<?php
$pagetitle = "Site Listing";
include 'process/ProcessRptPegs.php';
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
    jQuery(document).ready(function(){   
        //onclick of site listing   
        jQuery("#btnlist").click(function(){
            jQuery("#sites").show();
            jQuery("#senchaexport1").show();
            getsitelist();
        });
        
        //ajax: islands onchange dropdown box
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
            $('#sites').GridUnload();
            $("#senchaexport1").hide();
        });

        //ajax: regions onchange dropdown box
        $('#cmbregions').live('change', function(){
            sendRegionID($(this).val());
            $('#cmbprovinces').empty();
            $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
            $('#cmbcity').empty();
            $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
            $('#cmbbrgy').empty();
            $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
            $('#sites').GridUnload();
            $("#senchaexport1").hide();
        });

        //ajax: provinces onchange dropdown box
        $('#cmbprovinces').live('change', function(){
            sendProvID($(this).val());
            $('#cmbcity').empty();
            $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
            $('#cmbbrgy').empty();
            $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
            $('#sites').GridUnload();
            $("#senchaexport1").hide();
        });    

        //ajax: cities onchange dropdown box
        $("#cmbcity").live('change', function(){
            $('#sites').GridUnload();
            $("#senchaexport1").hide();
        });
    });
    
    //get site list
    function getsitelist()
    {
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
       
        //this part displays the site details
           jQuery("#sites").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            rptpage: function(){return "RptSiteListing"}
                          },
                datatype: "json",
                colNames:['POS Account','Site / PEGS Code','Site / PEGS Name','Island', 'Region', 'Province', 'City', 'Barangay', 'Address', 'Contact No.','Status', 'Total Terminals'],
                colModel:[
                           {name:'POS', index:'POS', align:'center'},
                           {name:'SiteCode',index:'SiteCode', align: 'left'},
                           {name:'SiteName',index:'SiteName', align: 'left'},                           
                           {name:'IslandName',index:'IslandName', align: 'left'},
                           {name:'RegionName', index: 'RegionName', align: 'left'},
                           {name:'ProvinceName', index: 'ProvinceName', align: 'left'},
                           {name:'CityName', index:'CityName', align: 'left'},
                           {name:'BarangayName', index:'BarangayName', align: 'left'},
                           {name:'Address', index:'Address', align: 'left'},
                           {name:'ContactNo',index:'ContactNo', align:'left'},
                           {name:'Status',index:'Status', align: 'left'},
                           {name:'Terminals', index:'Terminals', align: 'center'}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 240,
                width: 1200,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Site Details",
                gridview: true
           });
           jQuery("#sites").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
    }
    
    //export to excel file
    function exportExcelDemog()
    {
          var IslandID = document.getElementById('cmbislands').value;
          var RegionID = document.getElementById('cmbregions').value;
          var ProvinceID = document.getElementById('cmbprovinces').value;
          var CityID = document.getElementById('cmbcity').value;
          var url;
          if(IslandID > 0)
          {
             url = "process/ProcessRptPegs.php?island="+IslandID+"&getpage=SiteListing&fn=SiteListing";
          }

          if(IslandID == 0)
          {
             url = "process/ProcessRptPegs.php?all="+IslandID+"&getpage=SiteListing&fn=SiteListing";
          }

          if(RegionID > 0)
          {
             url = "process/ProcessRptPegs.php?region="+RegionID+"&getpage=SiteListing&fn=SiteListing";
          }

          if(ProvinceID > 0)    
          {
             url = "process/ProcessRptPegs.php?province="+ProvinceID+"&getpage=SiteListing&fn=SiteListing";
          }

          if(CityID > 0)
          {
             url = "process/ProcessRptPegs.php?city="+CityID+"&getpage=SiteListing&fn=SiteListing";
          }

          window.location.href= url;
    }
    
    //export to PDF file
    function exportPdfDemog()
    {
          var IslandID = document.getElementById('cmbislands').value;
          var RegionID = document.getElementById('cmbregions').value;
          var ProvinceID = document.getElementById('cmbprovinces').value;
          var CityID = document.getElementById('cmbcity').value;
          var url;
          if(IslandID > 0)
          {
             url = "process/ProcessRptPegs.php?island="+IslandID+"&getpage=SiteListingPDF";
          }

          if(IslandID == 0)
          {
             url = "process/ProcessRptPegs.php?all="+IslandID+"&getpage=SiteListingPDF";
          }

          if(RegionID > 0)
          {
             url = "process/ProcessRptPegs.php?region="+RegionID+"&getpage=SiteListingPDF";
          }

          if(ProvinceID > 0)    
          {
             url = "process/ProcessRptPegs.php?province="+ProvinceID+"&getpage=SiteListingPDF";
          }

          if(CityID > 0)
          {
             url = "process/ProcessRptPegs.php?city="+CityID+"&getpage=SiteListingPDF";
          }

          window.location.href= url;
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
     <form method="post" id="frmdemog">
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
        <div id="submitarea">
          <input type="button" id="btnlist" name="btnlist" value="Site Listing" />
        </div>
     </form>
    <!-- jqgrid pagination -->
    <div align="center" id="pagination">
      <!-- for site listing -->
      <table border="1" id="sites"></table>
      <div id="pager1"></div>
      <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px; display: none;">
           <br />
           <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" onclick="exportExcelDemog();" style="float: right;margin-right: 10px;"/>
           <input type="button" name="exportExcel" id="exportPdf" value="Export to PDF File" onclick="exportPdfDemog();" style="float: right;margin-right: 10px;"/>
      </div>
    </div>
    
</div>

<?php  
    }
}
include "footer.php"; ?>