<?php

/**
 * Description of BTAmpapiInvokerController
 *
 * @author fdlsison
 * @date 09/15/2014
 */
class BTAmpapiInvokerController extends Controller {
    /**
     * Set default action
     * @var string
     */
    public $defaultAction = 'overview';

    /**
     * Set default layout
     * @var string
     */
    public $layout = 'main';

    public $pageTitle;

    /**
     * Set caching of connection
     * @var boolean
     */
    private $_caching = FALSE;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';

    /**
     * Maximum number of seconds to wait while trying to connect
     * @var integer
     */
    private $_connectionTimeout = 10;

    /**
     * Maximum number of seconds before a call timeouts
     * @var integer
     */
    private $_timeout = 500;

    public function actionOverview()
    {
            // renders the view file 'protected/views/site/index.php'
            // using the default layout 'protected/views/layouts/main.php'
        $this->render('overview');

    }
    //Function that authenticates session
    public function actionAuthenticateSession(){
        $this->pageTitle= $this->genTitlePage('Authenticate Session');
        $result='';
        $moduleName ='authenticatesession';

        if(isset($_POST['Username']) && isset($_POST['Password'])){
            $username = $_POST['Username'];
            $password = $_POST['Password'];
            $result = $this->_AuthenticateSession($username, $password, $moduleName);
        }
        
        $this->render($moduleName, array('result'=>$result));
    }

    private function _AuthenticateSession($username, $password, $moduleName){
        $url = $this->genURL($moduleName);
               
        $postData = CJSON::encode(array('Username'=>$username, 'Password'=>$password));
        $result = $this->SubmitData($url, $postData);
        return $result[1];
    }

    //Retrieves active session
    public function actionGetActiveSession(){
        $this->pageTitle= $this->genTitlePage('Get Active Session');
        $result='';

        $moduleName ='getactivesession';

        if(isset($_POST['TPSessionID']) && isset($_POST['Username'])){
            $TPSessionID = $_POST['TPSessionID'];
            $Username = $_POST['Username'];
            $result = $this->_GetActiveSession($TPSessionID,$Username, $moduleName);
        }

        $this->render('getactivesession', array('result'=>$result));
    }
    
    private function _GetActiveSession($TPSessionID,$Username, $moduleName){   
        $url = $this->genURL($moduleName);
        $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'Username'=>$Username));

        $result = $this->SubmitData($url, $postData);
        
        return $result[1];
    }

    public function actionRegisterMemberBT(){
        $this->pageTitle= $this->genTitlePage('Register Member BT');
        $result='';

        $moduleName ='registermemberbt';

        if(isset($_POST['TPSessionID']) || isset($_POST['FirstName']) || isset($_POST['LastName']) ||isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) || isset($_POST['Birthdate'])){
            $TPSessionID = $_POST['TPSessionID'];
            $FirstName = $_POST['FirstName'];
            $LastName = $_POST['LastName'];
            $MobileNo = $_POST['MobileNo'];
            $EmailAddress = $_POST['EmailAddress'];
            $Birthdate = $_POST['Birthdate'];
            $result = $this->_registerMemberBT($TPSessionID ,$FirstName ,$LastName ,$MobileNo ,$EmailAddress ,$Birthdate ,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }

    private function _registerMemberBT($TPSessionID ,$FirstName ,$LastName,$MobileNo ,$EmailAddress ,$Birthdate, $moduleName){
        $url = $this->genURL($moduleName);
        $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID,'FirstName'=>$FirstName ,'LastName'=>$LastName,'MobileNo'=>$MobileNo , 'EmailAddress'=>$EmailAddress , 'Birthdate'=>$Birthdate));
        $result = $this->SubmitData($url, $postData);
        return $result[1];
    }


    //-------------------------Generator Functions-------------

    //This function dynamically generates string for title page
    private function genTitlePage($moduleName = null){
        return "BTAMPAPI - ".$moduleName;
    }
    //This function dynamically generates URL string for the use of certain method
    private function genURL($moduleName = null){
        return Yii::app()->params['urlBTAMPAPI'].$moduleName;
    }
    //----------------------------------------------

    private function SubmitData( $uri, $postdata)
    {
            $curl = curl_init( $uri );
            curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
            curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
            curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
            curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt( $curl, CURLOPT_POST, TRUE );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
            // Data+Files to be posted
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            return array( $http_status, $response );
    }    
}

?>
