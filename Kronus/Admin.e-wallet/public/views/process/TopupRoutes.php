<?php
// ROUTES

if(!isset($_GET['action'])) 
    die('Page not found');
switch($_GET['action']) {
    case 'grossholdbalanceoverview':
        $process->grossHoldBalanceOverview();
        break;
    case 'grossholdbalance':
        $process->grossHoldBalance();
        break;
    case 'grossholdbalanceviewdetails':
        if(isset($_POST['hdnsiteid']) && isset($_POST['hdnstartdate']) && isset($_POST['hdnenddate'])){
            $process->grossHoldBalanceViewDetails($_POST['hdnsiteid'], $_POST['hdnstartdate'], $_POST['hdnenddate']);
        } else {
            header("Location: GrossHoldBalance.php");
        }
        break;
    case 'confirmationoverview':
        $process->confirmationOverview();
        break;
    case 'confirmation':
        $process->confirmation();
        break;
    case 'replenishmentoverview':
        $process->replenishmentOverview();
        break;
    case 'replenishment':
        $process->replenishment();
        break;
    case 'grossholdmonitoring':
        $process->grossHoldMonitoring();
        break;
    case 'pagcorgrossholdmonitoring':
        $process->pagcorGrossHoldMonitoring();
        break;
    case 'getdata':
        $process->getdata();
        break;
    case 'getdata2':
        $process->getdata();
        break;
    case 'posteddeposit':
        $process->postedDepositOverview();
        break;
    case 'getdataposteddeposit':
        $process->getPostedDepositData();
        break;
    case 'topuphistoryoverview':
        $process->topUpHistoryOverview();
        break;
    case 'gettopuphistory':
        $process->getTopUpHistory();
        break;
    case 'reversalmanual':
        $process->reversalManual();
        break;
    case 'getreversalmanual':
        $process->getReversalManual();
        break;
    case 'playingbalance':
        $process->playingBalance();
        break;
    case 'sessioncount':
        $process->CountSession();
        break;
    case 'sessioncountter':
        $process->CountSessionTer();
        break;
    case 'sessioncountub':
        $process->CountSessionUB();
        break;
    case 'sessioncount1':
        $process->CountSession1();
        break;
    case 'sessioncountter1':
        $process->CountSessionTer1();
        break;
    case 'sessioncountub1':
        $process->CountSessionUB1();
        break;
    case 'playingbalanceub':
        $process->playingBalanceub();
        break;
    case 'getactiveterminals':
        $process->getActiveTerminals();
        break;
    case 'getcardnumber':
        $process->getCardNumber();
        break;
    case 'getactiveterminalsub':
        $process->getActiveTerminalsUb();
        break;
    case 'bettingcredit':
        $process->bettingCredit();
        break;
    case 'getbettingcredit':
        $process->getBettingCredit();
        break;
    case 'getsites':
        $process->getSitesDetail();
        break;
    case 'tumanualredemption':
        $process->manualRedemption();
        break;
    case 'getmanualredemption':
        $process->getManualRedemption();
        break;
    case 'tuewalletsitehistory':
        $process->ewalletsitehistory();
        break;
     
    case 'tuewalletcardhistory':
        $process->ewalletcardehistory();
        break;
    
    case 'getewalletsitehistory':
        $process->getewalletsitehistory();
        break;
    
    case 'getCardNumberStatus':
        $process->getCardNumberStatus();
        break;
    
    case 'getewalletcardhistory':
        $process->getewalletcardhistory();
        break;
    
    case 'cohadjustment':
        $process->cohAdjustmentOverview();
        break;
    
    case 'cohadjustmentdata':
        $process->getCohAdjustmentData();
        break;

    default :
        die('Page not found');
}

