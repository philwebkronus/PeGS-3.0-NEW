<?php
include 'PDOLibrary.php';
class modelDBTimer extends PDOLibrary{
    
     function __construct($sconectionstring) 
    {
        parent::__construct($sconectionstring);          
    }   
    
    function getTime(){
          $stmt = "SELECT NOW(6)";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchData();
      
    }
}
?>
