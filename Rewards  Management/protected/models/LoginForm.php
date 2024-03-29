<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'LoginController'.
 */
class LoginForm extends CFormModel
{
	public $UserName;
    public $Password;
	public $rememberMe;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
        return array(
            // username and password are required
            array('UserName, Password', 'required'),
            array('UserName', 'authenticate',),
            array('UserName', 'length', 'max' => 20),
            // rememberMe needs to be a boolean
            array('rememberMe', 'boolean'),
            // password needs to be authenticated
            array('Password', 'authenticate',),
            array('Password', 'length', 'max' => 12)
        );
    }

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->UserName,$this->Password);
			if(!$this->_identity->authenticate())
				$this->addError('password','Incorrect username or password.');
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->UserName,$this->Password);
			$this->_identity->authenticate();
		}
        $check = $this->checkAccount($this->UserName, $this->Password);
        
        $countcheck = count($check);
		if($countcheck > 0)
		{
            //for accoutsessions
//            foreach ($check as $value) {
//                $aid = $value['AID'];
//            }
//            
//            $session = new CHttpSession;
//            $session->open();
//            $session->clear();
//            $sessionmodel = new SessionForm();
//            $session_id = session_id();
//            $session->setSessionID($session_id);
//            $sessionmodel->checkSession($aid);
//            if(empty($sessionmodel->aid)) 
//            {
//                $sessionmodel->addSession($aid, $session_id);
//            }
//            else 
//            {
//                $sessionmodel->updateSession($aid, $session_id);
//            }
            
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
            
//            Yii::app()->session['AID'] = $aid;
//            Yii::app()->session['SessionID'] = $session_id;
            
			return true;
		}
		else
			return false;
	}
    public function loginPartner()
    {
        $partner = new PartnersModel();
        
        if($this->_identity===null)
        {
            $this->_identity=new UserIdentity($this->UserName,$this->Password);
            $this->_identity->authenticate();
        }
        $check = $partner->checkAccount($this->UserName, $this->Password);
        //Check if exist
        if (count($check) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    
    }
    
    /*
     * Description: check Account
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
    public function checkAccount($username, $password)
    {
        $connection = Yii::app()->db2;
        $password = sha1($password);
        
        $sql="SELECT AID, UserName, Password, Status FROM accounts 
            WHERE UserName = :username AND Password = :password AND Status = 1;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':username', $username);
        $command->bindValue(':password', $password);
        $result = $command->queryAll();
        
        return $result;
        

    }

    
}
