<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EWalletTransModel
 *
 * @author jdlachica
 */
class EWalletTransModel extends MI_Model {
    public function getEWalletTransactionPerSite($startDate, $endDate, $siteID, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $site = "SELECT SiteCode FROM sites WHERE SiteID=:site_id";
        $param2 = array(
            ':site_id'=>$siteID, 
        );
        $this->exec($site, $param2);
        $siteresult = $this->find();
        $sitecode = isset($siteresult['SiteCode'])?$siteresult['SiteCode']:0;
        $len = strlen($sitecode)+1;
        
        $sql = "SELECT ew.StartDate, ew.LoyaltyCardNumber, ew.Amount, ew.TransType, SUBSTR(t.TerminalCode,$len) as TerminalCode, 
                    CASE ew.Source WHEN 0 THEN 'Cashier' ELSE 'Genesis' END as Source 
                    FROM ewallettrans ew 
                    LEFT JOIN terminals t ON ew.TerminalID = t.TerminalID 
                    WHERE ew.StartDate>=:startDate AND ew.EndDate<=:endDate 
                    AND ew.Status=1 AND ew.SiteID=:siteID".(!empty($limit)?" LIMIT $limit":"");
        $param1 = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, 
            //':limit'=>$limit
        );
        $this->exec($sql, $param1);
        $result = $this->findAll();

        return $result;
    }
    
    public function getEWalletTransactionPerSiteTotal($startDate, $endDate, $siteID, $aid, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT SUM(Amount) as Amount,  TransType FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status=1 AND SiteID=:siteID GROUP BY TransType".(!empty($limit)?" LIMIT $limit":"");

        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getEWalletTransactionPerCashier($startDate, $endDate, $siteID, $aid, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $site = "SELECT SiteCode FROM sites WHERE SiteID=:site_id";
        $param2 = array(
            ':site_id'=>$siteID, 
        );
        $this->exec($site, $param2);
        $siteresult = $this->find();
        $sitecode = isset($siteresult['SiteCode'])?$siteresult['SiteCode']:0;
        $len = strlen($sitecode)+1;
        
        $sql = "SELECT ew.StartDate, ew.LoyaltyCardNumber, ew.Amount,  ew.TransType, SUBSTR(t.TerminalCode,$len) as TerminalCode, 
                    CASE ew.Source WHEN 0 THEN 'Cashier' ELSE 'Genesis' END as Source 
                    FROM ewallettrans ew 
                    LEFT JOIN terminals t ON ew.TerminalID = t.TerminalID 
                    WHERE ew.StartDate>=:startDate AND ew.EndDate<=:endDate 
                    AND ew.Status=1 AND ew.SiteID=:siteID AND ew.CreatedByAID=:aid".(!empty($limit)?" LIMIT $limit":"");

        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getEWalletTransactionPerCashierTotal($startDate, $endDate, $siteID, $aid, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT SUM(Amount) as Amount,  TransType FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status=1 AND SiteID=:siteID AND CreatedByAID=:aid GROUP BY TransType".(!empty($limit)?" LIMIT $limit":"");

        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getEWalletTransactionPerCashierWithOrder($startDate, $endDate, $siteID, $aid, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $site = "SELECT SiteCode FROM sites WHERE SiteID=:site_id";
        $param2 = array(
            ':site_id'=>$siteID, 
        );
        $this->exec($site, $param2);
        $siteresult = $this->find();
        $sitecode = isset($siteresult['SiteCode'])?$siteresult['SiteCode']:0;
        $len = strlen($sitecode)+1;
        
        $sql = "SELECT ew.StartDate, ew.LoyaltyCardNumber, ew.Amount,  ew.TransType, SUBSTR(t.TerminalCode,$len) as TerminalCode, 
                    CASE ew.Source WHEN 0 THEN 'Cashier' ELSE 'Genesis' END as Source 
                    FROM ewallettrans ew 
                    LEFT JOIN terminals t ON ew.TerminalID = t.TerminalID 
                    WHERE ew.StartDate>=:startDate AND ew.EndDate<=:endDate 
                    AND ew.Status=1 AND ew.SiteID=:siteID AND ew.CreatedByAID=:aid ORDER BY ew.StartDate DESC".(!empty($limit)?" LIMIT $limit":"");

        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getEWalletTransactionPerSiteWithOrder($startDate, $endDate, $siteID, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $site = "SELECT SiteCode FROM sites WHERE SiteID=:site_id";
        $param2 = array(
            ':site_id'=>$siteID, 
        );
        $this->exec($site, $param2);
        $siteresult = $this->find();
        $sitecode = isset($siteresult['SiteCode'])?$siteresult['SiteCode']:0;
        $len = strlen($sitecode)+1;
        
        $sql = "SELECT ew.StartDate, ew.LoyaltyCardNumber, ew.Amount,  ew.TransType, SUBSTR(t.TerminalCode,$len) as TerminalCode, 
                    CASE ew.Source WHEN 0 THEN 'Cashier' ELSE 'Genesis' END as Source 
                    FROM ewallettrans ew 
                    LEFT JOIN terminals t ON ew.TerminalID = t.TerminalID 
                    WHERE ew.StartDate>=:startDate AND ew.EndDate<=:endDate 
                    AND ew.Status=1 AND ew.SiteID=:siteID ORDER BY ew.StartDate DESC".(!empty($limit)?" LIMIT $limit":"");

        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getDepositSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate 
                    AND EndDate<=:endDate AND Status IN (1,3) AND SiteID=:siteID AND TransType='D'";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCashDepositSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans 
                    WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D'
                    AND PaymentType = 1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCouponDepositSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans 
                    WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D'
                    AND PaymentType = 2";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getTicketDepositSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans 
                    WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D'
                    AND PaymentType = 3";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getDepositSumPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND Source=0";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCashDepositSumPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 1 AND Source=0";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCouponDepositSumPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 2 AND Source=0";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getTicketDepositSumPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 3 AND Source=0";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getDepositSumPerVCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCashDepositSumPerVCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType=1 AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getDepositSumPerVCashierPerTerminal($startDate, $endDate, $siteID, $aid,$transsumid,$terminalid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND TransactionSummaryID = :trans_sum_id AND TerminalID = :terminalid AND Status IN (1,3) AND SiteID=:siteID 
                    AND TransType='D' AND CreatedByAID=:aid AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':trans_sum_id'=>$transsumid,
            ':terminalid'=>$terminalid,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCashDepositSumPerVCashierPerTerminal($startDate, $endDate, $siteID, $aid,$transsumid,$terminalid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND TransactionSummaryID = :trans_sum_id AND TerminalID = :terminalid AND Status IN (1,3) AND SiteID=:siteID 
                    AND TransType='D' AND CreatedByAID=:aid AND PaymentType=1 AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':trans_sum_id'=>$transsumid,
            ':terminalid'=>$terminalid,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getTicketDepositSumPerVCashierPerTerminal($startDate, $endDate, $siteID, $aid,$transsumid,$terminalid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND TransactionSummaryID = :trans_sum_id AND TerminalID = :terminalid AND Status IN (1,3) AND SiteID=:siteID 
                    AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 3 AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':trans_sum_id'=>$transsumid,
            ':terminalid'=>$terminalid,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getCouponDepositSumPerVCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 2 AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getTicketDepositSumPerVCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid AND PaymentType = 3 AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getWithdrawalSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate 
                    AND EndDate<=:endDate AND Status IN (1,3) AND SiteID=:siteID AND TransType='W'";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getWithdrawalSumPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='W' AND CreatedByAID=:aid AND Source=0";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getWithdrawalSumPerVCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND Status IN (1,3) AND SiteID=:siteID AND TransType='W' AND CreatedByAID=:aid AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getWithdrawalSumPerVCashierPerTerminal($startDate, $endDate, $siteID, $aid,$transsumid,$terminalid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate 
                    AND TransactionSummaryID = :trans_sum_id AND TerminalID = :terminalid AND Status IN (1,3) AND SiteID=:siteID 
                    AND TransType='W' AND CreatedByAID=:aid AND Source=1";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':trans_sum_id'=>$transsumid,
            ':terminalid'=>$terminalid,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
   
}
