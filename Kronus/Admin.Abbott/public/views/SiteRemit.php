<?php session_start(); ?>
<?php $pagetitle = "Cashier Deposit"?>
<?php include ("../process/ProcessReports.php?type=sr");?>
<?php include "header.php"; ?>
<!--
Created By: Arlene Salazar
Purpose: For PEGS Operators Report
Created On: May 27,2011
-->
    <div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <table class="listing" style="width:100%;">
            <thead>
                <tr>
                    <th colspan="7" class="header">As of <?php echo date("F d, Y h:i:s A")?></th>
                    <th class="paging">
                        <?php if($_SESSION['total_page_count'] == 0):?>

                        <?php else:?>
                        Page #:
                        <select id="srpage" onchange="javascript: return SRPaginate();">
                        <?php for($i = 1 ; $i <= $_SESSION['total_page_count'] ; $i++):?>
                            <?php if($_SESSION['page'] == $i):?>
                                <option value="<?php echo $i;?>" selected><?php echo $i;?></option>
                            <?php else:?>
                                <option value="<?php echo $i;?>"><?php echo $i;?></option>
                            <?php endif;?>
                        <?php endfor;?>
                        <?php endif;?>
                        </select>
                    </th>
                </tr>
                <tr>
                    <th>Site</th>
                    <th>Bank</th>
                    <th>Branch</th>
                    <th>Bank Transaction ID</th>
                    <th>Deposit Date</th>
                    <th>Cheque Number</th>
                    <th>Transaction Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
            <?php $siteremit = $_SESSION['siteRemit'];?>
            <?php if(count($siteremit) > 0):?>
            <?php for($i = 0 ; $i < count($_SESSION['siteRemit']) ; $i++):?>
            <tr>
                <td><?php echo $siteremit[$i]['siteName']?></td>
                <td><?php echo $siteremit[$i]['BankName']?></td>
                <td><?php echo $siteremit[$i]['Branch']?></td>
                <td align="center"><?php echo $siteremit[$i]['BankTransactionID']?></td>
                <td align="center"><?php echo $siteremit[$i]['BankTransactionDate']?></td>
                <td align="center"><?php echo $siteremit[$i]['ChequeNumber']?></td>
                <td align="center"><?php echo $siteremit[$i]['DateCreated']?></td>
                <td align="right" class="amount"><?php echo $siteremit[$i]['Amount']?></td>
            </tr>
            <?php endfor;?>
            <?php else:?>
            <tr>
                <td colspan="8">No Record Found</td>
            </tr>
            <?php endif;?>
            </tbody>
            <tfoot>
                <tr class="grandtotal">
                    <td colspan="7"><b>Total Page Summary</b></td>
                    <td class="amount">
                        <?php
                            echo $_SESSION['total_page_amount'];
                         ?>
                    </td>
                </tr>
                <tr class="grandtotal">
                    <td colspan="7"><b>Total Summary</b></td>
                    <td class="amount">
                        <?php
                             echo $_SESSION['siteRemitTotal'];
                         ?>
                    </td>
                </tr>
                <tr class="export">
                    <td colspan="8">
                        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' onclick="exportPDF('sr')"/>
                        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" onclick="exportExcel('sr','Site_Remittance_for_<?php echo date('Y-m-d_h:i:s_A')?>')"/>                            
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php  include "footer.php";?>
