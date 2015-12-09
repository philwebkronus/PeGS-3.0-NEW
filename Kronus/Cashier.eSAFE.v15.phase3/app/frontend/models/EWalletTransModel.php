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
                    WHERE ew.StartDate >= :startDate AND ew.StartDate < :endDate 
                    AND ew.Status=1 AND ew.SiteID=:siteID ORDER BY ew.StartDate DESC".(!empty($limit)?" LIMIT $limit":"");
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
        
        $sql = "SELECT SUM(Amount) as Amount,  TransType FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
                    WHERE ew.StartDate >= :startDate AND ew.StartDate < :endDate 
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
    
    public function getEWalletTransactionPerCashierTotal($startDate, $endDate, $siteID, $aid, $limit=null){
        $cutoff_time = Mirage::app()->param['cut_off'];
        
        $sql = "SELECT SUM(Amount) as Amount,  TransType FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
                    WHERE ew.StartDate >= :startDate AND ew.StartDate < :endDate 
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
                    WHERE ew.StartDate >= :startDate AND ew.StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate 
                    AND StartDate < :endDate AND Status IN (1,3) AND SiteID=:siteID AND TransType='D'";
        
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
                    WHERE StartDate >= :startDate AND StartDate < :endDate 
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
                    WHERE StartDate >= :startDate AND StartDate < :endDate 
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
                    WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate 
                    AND StartDate < :endDate AND Status IN (1,3) AND SiteID=:siteID AND TransType='W'";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
    public function getWithdrawalTicketSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate 
                    AND StartDate < :endDate AND Status IN (1,3) AND SiteID=:siteID AND TransType='W' AND Source = 1";
        
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate >= :startDate AND StartDate < :endDate 
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
    
    /**
     * @Description: For Site Cash On Hand Reports in Cashier. Function to get eSAFE Loads group by type of load (Cash, Bancnet, Coupon or Ticket) and eSAFE redemption grouped into Cash and Ticket redemption.
     * @DateCreated: 2015-10-28
     * @Author: aqdepliyan
     * @param string $startdate
     * @param string $enddate
     * @param int $siteid
     * @return array
     */
    public function geteSAFELoadsAndWithdrawals($startdate,$enddate,$siteid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $result = array();
        $eSAFELoadCashsql="SELECT IFNULL(SUM(Amount),0) as eSAFELoadCash FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 1 AND Status IN (1,3) AND TransType='D' 
                                                AND (TraceNumber IS NULL OR TraceNumber = '') AND (ReferenceNumber IS NULL OR ReferenceNumber = '')";
        $eSAFELoadBancnetsql="SELECT IFNULL(SUM(Amount),0) as eSAFELoadBancnet FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 1 AND Status IN (1,3) AND TransType='D' 
                                                AND TRIM(IFNULL(TraceNumber, '')) > '' AND TRIM(IFNULL(ReferenceNumber, '')) > ''";
        $eSAFELoadCouponsql="SELECT IFNULL(SUM(Amount),0) as eSAFELoadCoupon FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 2 AND Status IN (1,3) AND TransType='D' 
                                                AND (TraceNumber IS NULL OR TraceNumber = '') AND (ReferenceNumber IS NULL OR ReferenceNumber = '')";
        $eSAFELoadTicketsql="SELECT IFNULL(SUM(Amount),0) as eSAFELoadTicket FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 3 AND Status IN (1,3) AND TransType='D' 
                                                AND (TraceNumber IS NULL OR TraceNumber = '') AND (ReferenceNumber IS NULL OR ReferenceNumber = '')";
        $eSAFECashierRedemptionsql="SELECT IFNULL(SUM(Amount),0) as eSAFECashierRedemption FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 1 AND Status IN (1,3) AND TransType='W' 
                                                AND (TraceNumber IS NULL OR TraceNumber = '') AND (ReferenceNumber IS NULL OR ReferenceNumber = '')
                                                AND Source = 0";
        $eSAFEGenesisRedemptionsql="SELECT IFNULL(SUM(Amount),0) as eSAFEGenesisRedemption FROM npos.ewallettrans WHERE StartDate >= :startdate AND StartDate < :enddate
                                                AND SiteID = :siteid AND PaymentType = 3 AND Status IN (1,3) AND TransType='W' 
                                                AND (TraceNumber IS NULL OR TraceNumber = '') AND (ReferenceNumber IS NULL OR ReferenceNumber = '')
                                                AND Source = 1";
        
        $param = array(
            ':startdate'=>$startdate.' '.$cutoff_time,
            ':enddate'=>$enddate.' '.$cutoff_time,
            ':siteid'=>$siteid
        );
        
        $this->exec($eSAFELoadCashsql, $param);
        $eSAFELoadCash = $this->find();
        
        $this->exec($eSAFELoadBancnetsql, $param);
        $eSAFELoadBancnet = $this->find();
        
        $this->exec($eSAFELoadCouponsql, $param);
        $eSAFELoadCoupon = $this->find();
        
        $this->exec($eSAFELoadTicketsql, $param);
        $eSAFELoadTicket = $this->find();
        
        $this->exec($eSAFECashierRedemptionsql, $param);
        $eSAFECashierRedemption = $this->find();
        
        $this->exec($eSAFEGenesisRedemptionsql, $param);
        $eSAFEGenesisRedemption = $this->find();
        
        $result['eSAFELoadCash'] = isset($eSAFELoadCash['eSAFELoadCash'])?$eSAFELoadCash['eSAFELoadCash']:0;
        $result['eSAFELoadBancnet'] = isset($eSAFELoadBancnet['eSAFELoadBancnet'])?$eSAFELoadBancnet['eSAFELoadBancnet']:0;
        $result['eSAFELoadCoupon'] = isset($eSAFELoadCoupon['eSAFELoadCoupon'])?$eSAFELoadCoupon['eSAFELoadCoupon']:0;
        $result['eSAFELoadTicket'] = isset($eSAFELoadTicket['eSAFELoadTicket'])?$eSAFELoadTicket['eSAFELoadTicket']:0;
        $result['eSAFECashierRedemption'] = isset($eSAFECashierRedemption['eSAFECashierRedemption'])?$eSAFECashierRedemption['eSAFECashierRedemption']:0;
        $result['eSAFEGenesisRedemption'] = isset($eSAFEGenesisRedemption['eSAFEGenesisRedemption'])?$eSAFEGenesisRedemption['eSAFEGenesisRedemption']:0;
        
        return $result;
        
    }
    
    /**
     * @Description: Check if the transaction summary made was for eSAFE transaction.
     * @Author: aqdepliyan
     * @DateCreated: 2015-11-12 10:30 AM
     * @param int $transsumid
     */
    public function CheckIfeSAFETrans($transsumid){
        $checkifesafetrans = "SELECT COUNT(EwalletTransID) as esafecounter FROM npos.ewallettrans WHERE TransactionSummaryID = :transsumid";
        $param = array(
            ':transsumid'=>$transsumid, 
        );
        $this->exec($checkifesafetrans, $param);
        $IseSAFETransResult = $this->find();

        $IseSAFETrans = $IseSAFETransResult['esafecounter'] > 0 ? 1:0;
        return $IseSAFETrans;
    }
    
    
    public function getSessionDetails($trans_summary_id) {
        $sql = "SELECT ew.Amount,DATE_FORMAT(ew.StartDate,'%Y-%m-%d %h:%i:%s %p') DateCreated,t.TerminalCode,
                    DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %h:%i:%s %p') DateStarted
                    FROM ewallettrans ew 
                    INNER JOIN transactionsummary ts ON ew.TransactionSummaryID = ts.TransactionsSummaryID 
                    INNER JOIN terminals t ON t.TerminalID = ew.TerminalID 
                    INNER JOIN accounts acc ON ew.CreatedByAID = acc.AID
                    WHERE ts.TransactionsSummaryID = :trans_summary_id AND ew.Status IN (1,3)";
        $param = array(':trans_summary_id'=>$trans_summary_id);
        $this->exec($sql, $param);
        return $this->findAll();
    }
    
   
}
