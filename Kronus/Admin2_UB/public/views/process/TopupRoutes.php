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
    case 'getdata':
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
    default :
        die('Page not found');
}
