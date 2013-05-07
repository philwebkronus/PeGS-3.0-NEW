<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
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
	public function rules()
	{
		return array(
			// username and password are required
			array('UserName, Password', 'required'),
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('Password', 'authenticate'),
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
                    $this->_identity=new VMSUserIdentity($this->UserName,$this->Password);

                    if(!$this->_identity->authenticate())
                    {
                        
                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_USER_SUSPENDED)
                        {
                            $this->addError('Password','Account is temporarily suspended.');

                        }

                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_USER_LOCKED)
                        {
                             $this->addError('Password','Account is locked.');
                        }

                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_ADMIN_LOCKED)
                        {
                             $this->addError('Password','Account has been locked by an Administrator.');
                        }

                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_USER_TERMINATED)
                        {
                             $this->addError('Password','Account is already terminated.');
                        }

                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_PASSWORD_EXPIRED)
                        {
                             $this->addError('Password','Password has expired.');
                        }
                        
                        if($this->_identity->errorCode == VMSUserIdentity::ERROR_USER_PENDING)
                        {
                             $this->addError('Password','User account is not active.');
                        }
                        else
                            $this->addError('Password','Incorrect username or password.');
                         
                    }
                            
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
			$this->_identity=new VMSUserIdentity($this->UserName,$this->Password);
                        $this->_identity->authenticate();
		}
		if($this->_identity->errorCode===VMSUserIdentity::ERROR_USER_NONE)
		{
			$duration = 3600;        // 1 hour
			Yii::app()->user->login($this->_identity,$duration);
                                                
                        $user = SSO::model()->findByAttributes(array('UserName'=>$this->UserName));
                        
                        //Get user AID and Account Type type and set into session
                        Yii::app()->session['AccountType'] = $user->AccountTypeID;
                        Yii::app()->session['AID'] = $user->AID;
                        
                        //Get user default page
                        $DefaultPage = AccessRights::getDefaultPageURL($user->AccountTypeID);
                        if(isset($DefaultPage))
                        {
                            Yii::app()->session['homeUrl'] = AccessRights::getDefaultPageURL($user->AccountTypeID);
                        }
                        else
                            Yii::app()->session['homeUrl'] = '/site/index';
                        
			return true;
		}
		else
			return false;
	}
        
}
