<?php
/**
 * View Generated Reports
 * Mark Kenneth Esguerra
 * Sep-03-13
 * Philweb Corp.
 */

$this->pageTitle = Yii::app()->name." - Reports";
$rewardtype     = "";
$filter         = "";;
$particular     = "";
$playerclass    = "";
$exportheader   = "";
$exportvalues   = "";
if (isset($reporttype) && isset($exportHeader) && isset($exportHeader))
{
    $rewardtype     = $additionalInfo['RewardType'];
    $filter         = $additionalInfo['Filter'];
    $particular     = $additionalInfo['Particular'];
    $playerclass    = $additionalInfo['PlayerClass'];
    $header         = $exportHeader;
    $values         = $exportValues;
?>
<h2>Manage Reports</h2>
<table>
    <tr>
        <td id="" style="width: 500px;">
            <span style="font-size: 15px;"><?php echo $reporttype; ?></span><br />
            <?php echo $coverage." Statistics"; ?> <br />
            <?php
            //Change date format to mm-dd-YYY
            $start = date("m-d-Y", strtotime($start));
            $end = date("m-d-Y", strtotime($end));
            ?>
            From: <?php echo $start; ?>&nbsp;&nbsp;&nbsp;To: <?php echo $end; ?><br /><br />
            <div id="row">
                <?php
                    echo CHtml::image(Yii::app()->request->baseUrl."/images/graph.png",'',array(
                                            'width'=>'710',
                                            'height'=> '600',                    ));
                ?>
            </div>
            <br />
            <br />
            <br />
            <table>
                <tr>
                    <td style="width: 50px;"><b>Reward Type:</b></td>    
                    <td><?php echo $rewardtype; ?></td>
                </tr>
                <tr>
                    <td style="width: 120px;"><b>Filter By:</b></td>    
                    <td><?php echo $filter; ?></td>
                </tr>
                <tr>
                    <td style="width: 120px;"><b>Particular:</b></td>    
                    <td><?php echo $particular; ?></td>
                </tr>
                <tr>
                    <td style="width: 120px;"><b>Player Classification:</b></td>    
                    <td><?php echo $playerclass; ?></td>
                </tr>
            </table>
        </td>
        <td style="vertical-align: top;">
            <br /><br /><br /><br />
            <div id="row">
                <?php
                    echo CHtml::button('Export Graph to PDF',array('submit' => array('reports/export','type'=>'pdf', 
                                                                                                       'title' => $reporttype, 
                                                                                                       'datefrom' => $start, 
                                                                                                       'dateto' => $end, 
                                                                                                       'coverage' => $coverage, 
                                                                                                       'rewardtype' => $rewardtype, 
                                                                                                       'filter' => $filter, 
                                                                                                       'particular' => $particular, 
                                                                                                       'playerclass' => $playerclass)));
                ?>
            </div>
            <br />
            <div id="row">
                <?php
                    echo CHtml::button('Export Data to Excel',array('submit' => array('reports/export','type'=>'excel', 
                                                                                                       'title' => $reporttype,
                                                                                                       'header' => $header,
                                                                                                       'values' => $values)));
                ?>
            </div>
            <br />
            <div id="row">
                <?php
                    echo CHtml::button('Generate Report Again',array('submit' => array('reports/index')));
                ?>
            </div>
        </td>    
    </tr>    
</table> 
<?php
}
else
{
    $this->redirect('index');
}
?>
