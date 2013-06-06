<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class CardStatus
{
    const INACTIVE = 0;
    const ACTIVE = 1;
    const DEACTIVATED = 2;
    
    /* Statuses from Old Loyalty */
    const OLD = 3; //Transferred from Old Loyalty
    const OLD_MIGRATED = 4; //Points were already migrated to new membership card
    
    /* Temporary Account Status */
    const ACTIVE_TEMPORARY = 5; 
    const INACTIVE_TEMPORARY = 6;
    
    const NEW_MIGRATED = 7; //Status of the new card migrated to the new member card  
    const TEMPORARY_MIGRATED = 8; //Temporay card has availed new membership card
    
    const BANNED = 9; //Banned cards
    const NOT_EXIST = 100; 
    const MIGRATION_ERROR = 101;
    
    public function CardStatus()
    {
        
    }
}
?>
