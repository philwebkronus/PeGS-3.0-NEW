<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class VMSUserIdentity extends CUserIdentity
{
        private $_id;
        private $_accessrights;
        
        CONST ERROR_USER_NONE = 0;
        CONST ERROR_USER_PENDING = 1;
        CONST ERROR_USER_SUSPENDED = 2;
        CONST ERROR_USER_LOCKED = 3;
        CONST ERROR_ADMIN_LOCKED = 4;
        CONST ERROR_USER_TERMINATED = 5;
        CONST ERROR_PASSWORD_EXPIRED = 6;
        CONST ERROR_USERNAME_INVALID = 7;
        CONST ERROR_PASSWORD_INVALID = 8;
        CONST ERROR_USER_DENIED = 9;
        
                
	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
            $user=SSO::model()->findByAttributes(array('UserName'=>$this->username));
            
            //Check user status
            $userStatus = SSO::getUserStatus($this->username);
            
            /** 
             * 0 - Pending; 1 - Active; 2 - Suspended; 
             * 3 - Locked (Attempts); 4 - Locked (Admin);
             * 5 - Terminated; 6- Password ...
             */
            
            if($user===null)
            {
                $this->errorCode=self::ERROR_USERNAME_INVALID;
            }
            else
            {
                /**
                 * Check if user has a valid password
                 */
                if($user->Password !== $user->encrypt($this->password))
                {
                    if($userStatus == 3)
                    {
                        $this->_id = $user->AID;
                        $this->errorCode=self::ERROR_USER_DENIED;
                    }
                }
                else
                {
                    /**
                    * Check if user is active
                    */
                    
                    if($userStatus == 1)
                    {
                        $this->_id = $user->AID;
                        $this->errorCode=self::ERROR_USER_NONE;
                    }
                    else
                    {
                        switch($userStatus)
                        {
                            case 0:
                                $this->errorCode = self::ERROR_USER_PENDING;
                                break;
                            case 2:
                                $this->errorCode = self::ERROR_USER_SUSPENDED;
                                break;
                            case 3:
                                //$this->errorCode = self::ERROR_USER_LOCKED;
                                $this->errorCode = self::ERROR_USER_DENIED;
                                break;
                            case 4:
                                $this->errorCode = self::ERROR_ADMIN_LOCKED;
                                break;
                            case 5:
                                $this->errorCode = self::ERROR_USER_TERMINATED;
                                break;
                            case 6:
                                $this->errorCode = self::ERROR_PASSWORD_EXPIRED;
                                break;
                            
                        }
                    }
                    
                }
                
            }
            
            return !$this->errorCode;
            
	} 
        
        public function getId() {
            return $this->_id;
        }
         
}