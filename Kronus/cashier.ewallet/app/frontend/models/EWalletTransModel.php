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
    public function getEWalletTransactionPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT StartDate, LoyaltyCardNumber, Amount, TransType FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getEWalletTransactionPerCashier($startDate, $endDate, $siteID, $aid){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT StartDate, LoyaltyCardNumber, Amount, TransType FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID AND CreatedByAID=:aid";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->findAll();
        return $result;
    }
    
    public function getDepositSumPerSite($startDate, $endDate, $siteID){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID AND TransType='D'";
        
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID AND TransType='D' AND CreatedByAID=:aid";
        
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID AND TransType='W'";
        
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
        $sql = "SELECT SUM(Amount) as Amount FROM ewallettrans WHERE StartDate>=:startDate AND EndDate<=:endDate AND Status=1 AND SiteID=:siteID AND TransType='W' AND CreatedByAID=:aid";
        
        $param = array(
            ':startDate'=>$startDate.' '.$cutoff_time,
            ':endDate'=>$endDate.' '.$cutoff_time,
            ':siteID'=>$siteID, ':aid'=>$aid
        );
        $this->exec($sql, $param);
        $result = $this->find();
        return isset($result['Amount'])?$result['Amount']:0;
    }
    
   
}
