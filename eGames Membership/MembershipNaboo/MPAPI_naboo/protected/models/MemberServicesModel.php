<?php

/**
 * @author taalcatara
 *
 * @date 08-12-2015
 */
class MemberServicesModel
{

    public static $_instance = null;
    public $_connection;

    public function __construct()
    {
        $this->_connection = Yii::app()->db;
    }

    public static function model()
    {
        if (self::$_instance == null)
            self::$_instance = new MemberServicesModel();
        return self::$_instance;
    }

    public function AddMemberServices($serviceid, $MID, $ServiceUsername, $ServicePassword, $HashedServicePassword, $UserMode, $DateCreated, $isVIP, $VIPLevel, $PlayerCode, $Status, $amount)
    {
        $startTrans = $this->_connection->beginTransaction();

        try
        {
            $query = "INSERT INTO memberservices (ServiceID, MID, ServiceUsername, ServicePassword,
            HashedServicePassword, UserMode, DateCreated, isVIP, VIPLevel, PlayerCode, Status, CurrentBalance)
            VALUES ($serviceid, $MID, '$ServiceUsername', '$ServicePassword', '$HashedServicePassword', $UserMode,
                NOW(6), $isVIP, $VIPLevel, '$PlayerCode', $Status, $amount)";
            $command = $this->_connection->createCommand($query);
            $command->execute();

            try
            {
                $startTrans->commit();
                return 1;
            }
            catch (PDOException $e)
            {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        }
        catch (Exception $e)
        {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    //@date 12-11-2015
    public function checkMobileNumberIfExist($mobileNo)
    {
        $query = "SELECT * FROM memberservices where ServiceUsername like '%$mobileNo'";
        $command = $this->_connection->createCommand($query);
        $result = $command->queryRow();

        return $result;
    }

}