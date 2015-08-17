<?php
/**
 * The Salesforce REST API PHP Wrapper
 *
 * This class connects to the Salesforce REST API and performs actions on that API
 *
 * @author Anthony Humes <jah.humes@gmail.com>
 * @license GPL, or GNU General Public License, version 2
 */
class SalesforceAPI extends APIAbstract {
    public
        $last_response;
    protected
        $client_id,
        $client_secret,
        $instance_url,
        $base_url,
        $headers,
        $return_type,
        $api_version;
    private
        $access_token,
        $handle;
    // Supported request methods
    const
        METH_DELETE = 'DELETE',
        METH_GET    = 'GET',
        METH_POST   = 'POST',
        METH_PUT    = 'PUT',
        METH_PATCH  = 'PATCH';
    // Return types
    const
        RETURN_OBJECT  = 'object',
        RETURN_ARRAY_K = 'array_k',
        RETURN_ARRAY_A = 'array_a';
    const
        LOGIN_PATH   = '/services/oauth2/token',
        OBJECT_PATH = 'sobjects/',
        GRANT_TYPE  = 'password';
    /**
     * Constructs the SalesforceConnection
     *
     * This sets up the connection to salesforce and instantiates all default variables
     *
     * @param string $instance_url The url to connect to
     * @param string|int $version The version of the API to connect to
     * @param string $client_id The Consumer Key from Salesforce
     * @param string $client_secret The Consumer Secret from Salesforce
     */
    public function __construct($instance_url,$version, $client_id, $client_secret, $return_type = self::RETURN_OBJECT)
    //public function __construct($instance_url,$client_id, $client_secret, $return_type = self::RETURN_OBJECT)
    {
        // Instantiate base variables
        $this->instance_url = $instance_url;
        $this->api_version = $version;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->return_type = $return_type;
        $this->base_url = $instance_url;
        $this->instance_url = $instance_url . '/services/data/v' . $version . '/';
        //$this->instance_url = $instance_url . '/services/data/v/';
        $this->headers = Array('Content-Type' => 'application/json');
        // If the cURL handle doesn't exist, create it
        if(is_null($this->handle)) {
            $this->handle = curl_init();
            $options = Array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 240,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_BUFFERSIZE => 128000
            );
            curl_setopt_array($this->handle, $options);
        }
    }
    /*========== Authorization =========*/
    /**
     * Logs in the user to Salesforce with a username, password, and security token
     *
     * @param string $username
     * @param string $password
     * @param string $security_token
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function login($username, $password, $security_token)
    {
        // Set the login data
        $login_data = Array(
            'grant_type' => self::GRANT_TYPE,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'username' => $username,
            'password' => $password . $security_token
        );
        // Change the content type to a form
//        $headers = [
//            'Content-Type' => 'application/x-www-form-urlencoded'
//        ];
        // TODO: Fix this to use the httpRequest function. There is an issue with the curl opt Custom Request
        $ch = curl_init();
        $http_params = http_build_query($login_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/services/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, 5);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type : application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, 4);
        $login = curl_exec($ch);
        $login = explode("\n", $login);
        $login = json_decode($login[count($login)-1]);
        //echo 'Auth response: '; print_r($data); echo '<br/>';
        curl_close($ch);
        // Send the request
//        $login = $this->httpRequest($this->base_url . self::LOGIN_PATH,$login_data, $headers, self::METH_POST);
        // Set the access token
//        if($this->return_type === self::RETURN_OBJECT) {
        $this->access_token = $login->access_token;
//        } elseif($this->return_type === self::RETURN_ARRAY_A) {
//            $this->access_token = $login['access_token'];
//        }
        // Return the login object
        // TODO: Should this be returned?
        return $login;
    }
    /*=========== Organization Information ===============*/
    /**
     * Get a list of all the API Versions for the instance
     *
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function getAPIVersions()
    {
        return $this->httpRequest( $this->base_url . '/services/data' );
    }
    /**
     * Lists the limits for the organization. This is in beta and won't return for most people
     *
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function getOrgLimits()
    {
        return $this->request('limits/');
    }
    /**
     * Gets a list of all the available REST resources
     *
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function getAvailableResources()
    {
        return $this->request('');
    }
    /**
     * Get a list of all available objects for the organization
     *
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function getAllObjects()
    {
        return $this->request( self::OBJECT_PATH );
    }
    /*========== Object Metadata ============*/
    /**
     * Get metadata about an Object
     *
     * @param string $object_name
     * @param bool $all Should this return all meta data including information about each field, URLs, and child relationships
     * @param DateTime $since Only return metadata if it has been modified since the date provided
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function getObjectMetadata($object_name, $all = false, DateTime $since = null)
    {
        $headers = '';
        // Check if the If-Modified-Since header should be set
        if($since !== null && $since instanceof DateTime) {
            $headers['IF-Modified-Since'] = $since->format('D, j M Y H:i:s e');
        } elseif($since !== null && !$since instanceof DateTime) {
            // If the $since flag has been set and is not a DateTime instance, throw an error
            throw new SalesforceAPIException('To get object metadata for an object, you must provide a DateTime object');
        }
        // Should this return all meta data including information about each field, URLs, and child relationships
        if($all === true)
            return $this->request(array(self::OBJECT_PATH . $object_name . '/describe/','',self::METH_GET, $headers));
        else
            return $this->request(array(self::OBJECT_PATH . $object_name,'',self::METH_GET,$headers));
    }
    /*========= Working with Records ==========*/
    /**
     * Create a new record
     *
     * @param string $object_name
     * @param array $data
     * @return mixed
     * @throws SalesforceAPIException
     */
//    public function create( $object_name, $data, $url)
//    {
//
//        return $this->request($url.'/services/data/v20.0/', self::OBJECT_PATH . (string)$object_name, $data, self::METH_POST);
//    }
   public function create_account($lname, $fname, $bdate, $salutation, $playertype, $cardnumber, $instance_url, $access_token) {
    $url = "$instance_url/services/data/v20.0/sobjects/Account/";
    $content = json_encode(array("FirstName" => $fname, "LastName" => $lname, "Type_of_Player__c" => $playertype, "Birthdate__c" => $bdate, "Salutation" => $salutation, "Membership_Card_Number__c" => $cardnumber, "RecordTypeId" => '012o0000000p8uHAAQ')); //prod: '012o0000000p8uHAAQ' staging: '01228000000JV06AAG'
    $curl = curl_init($url);
    //if production, uncomment the line below
    $this->_unsecure($curl);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ( $status != 201 ) {
        die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }
    //echo "HTTP status $status creating account<br/><br/>";
    curl_close($curl);
    $response = json_decode($json_response, true);
    $id = $response["id"];
    //echo "New record id $id<br/><br/>";
    return $id;
}
    /**
     * Update an existing object
     *
     * @param string $object_name
     * @param string $object_id
     * @param array $data
     * @return mixed
     * @throws SalesforceAPIException
     */
//    public function update( $object_name, $object_id, $data )
//    {
//        return $this->request( self::OBJECT_PATH . (string) $object_name . '/' . $object_id, $data, self::METH_PATCH );
//    }
    public function update_account($id, $fname = null, $lname = null, $bdate = null, $cardnumber = null, $membstatus = null, $membtype = null, $instance_url, $access_token) {
    $url = "$instance_url/services/data/v20.0/sobjects/Account/$id";
    $params = '';
    if ($fname != null)
    {
        $params['FirstName'] = $fname;
    }
    if ($lname != null)
    {
        $params['LastName'] = $lname;
    }
    if ($bdate != null)
    {
        $params['Birthdate__c'] = $bdate;
    }
    if ($cardnumber != null)
    {
        $params['Membership_Card_Number__c'] = $cardnumber;
    }
    if ($membstatus != null)
    {
        $params['Inactive'] = $membstatus;// TRUE or FALSE
    }
    if ($membtype != null)
    {
        $params['Type_of_Player__c'] = $membtype;// Regular, VIP, etc.
    }
    $content = json_encode($params);
    $curl = curl_init($url);
    //
    $this->_unsecure($curl);
    //
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ( $status != 204 ) {
        die("Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }
    //echo "HTTP status $status updating account<br/><br/>";
    curl_close($curl);
}
    /**
     * Delete a record
     *
     * @param string $object_name
     * @param string $object_id
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function delete( $object_name, $object_id )
    {
        return $this->request( self::OBJECT_PATH . (string) $object_name . '/' . $object_id, null, self::METH_DELETE );
    }
    /**
     * Get a record
     *
     * @param string $object_name
     * @param string $object_id
     * @param array|null $fields
     * @return mixed
     * @throws SalesforceAPIException
     */
//    public function get( $object_name, $object_id, $fields = null )
//    {
//        $params = [];
//        // If fields are included, append them to the parameters
//        if($fields !== null && is_array($fields)) {
//            $params['fields'] = implode(',', $fields);
//        }
//        return $this->request( self::OBJECT_PATH . (string) $object_name . '/' . $object_id, $params );
//    }
    public function show_accounts($instance_url, $access_token) {
    $query = "SELECT Name, Id, Birthdate__c, Salutation, Membership_Card_Number__c from Account LIMIT 100";
    $url = "$instance_url/services/data/v20.0/query?q=" . urlencode($query);
    $curl = curl_init($url);
    //if production, uncomment the line below
    $this->_unsecure($curl);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token"));
    $json_response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($json_response, true);
    $total_size = $response['totalSize'];
    echo "$total_size record(s) returned<br/><br/>";
    foreach ((array) $response['records'] as $record) {
        echo $record['Id'] . ", " . $record['Name'] . ", " . $record['Birthdate__c'] . ", " . $record['Salutation'] . ", " . $record['Membership_Card_Number__c'] . "<br/>";
    }
    echo "<br/>";
}
private function _unsecure($curl) {
        //
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($curl, CURLOPT_CAINFO, getcwd() . "\CAcerts\localhost.crt"); // die(getcwd() . "/CAcerts/localhost.crt");
        //
    }
    /**
     * Searches using a SOQL Query
     *
     * @param string $query The query to perform
     * @param bool $all Search through deleted and merged data as well
     * @param bool $explain If the explain flag is set, it will return feedback on the query performance
     * @return mixed
     * @throws SalesforceAPIException
     */
    public function searchSOQL($query, $all = false, $explain = false) {
        $search_data = Array(
            'q' => $query
        );
        // If the explain flag is set, it will return feedback on the query performance
        if($explain) {
            $search_data['explain'] = $search_data['q'];
            unset($search_data['q']);
        }
        // If is set, search through deleted and merged data as well
        if($all)
            $path = 'queryAll/';
        else
            $path = 'query/';
        return $this->request($path,$search_data,self::METH_GET);
    }
    public function getQueryFromUrl($query)
    {
        // Throw an error if no access token
        if(!isset($this->access_token))
            throw new SalesforceAPIException('You have not logged in yet.');
        // Set the Authorization header
        $request_headers = Array(
            'Authorization' => 'Bearer ' . $this->access_token
        );
        // Merge all the headers
        $request_headers = array_merge($request_headers, '');
        return $this->httpRequest( $this->base_url .$query,'',$request_headers);
    }
    /**
     * Makes a request to the API using the access key
     *
     * @param string $path The path to use for the API request
     * @param array $params
     * @param string $method
     * @param array $headers
     * @return mixed
     * @throws SalesforceAPIException
     */
    protected function request($url, $path, $params = '', $method = self::METH_GET, $headers = '')
    {
        // Throw an error if no access token
        if(!isset($this->access_token))
            throw new SalesforceAPIException('You have not logged in yet.');
        // Set the Authorization header
        $request_headers = Array(
            'Authorization' => 'Bearer ' . $this->access_token
        );
        // Merge all the headers
        $request_headers = array_merge($request_headers, $headers);
        //return $this->httpRequest($this->instance_url . $path, $params, $request_headers, $method);
        return $this->httpRequest($url. $path, $params, $request_headers, $method);
    }
    /**
     * Performs the actual HTTP request to the Salesforce API
     *
     * @param string $url
     * @param array|null $params
     * @param array|null $headers
     * @param string $method
     * @return mixed
     * @throws SalesforceAPIException
     */
    protected function httpRequest($url, $params = null, $headers = null, $method = self::METH_GET)
    {
        // Set the headers
        if(isset($headers) && $headers !== null && !empty($headers))
            $request_headers = array_merge($this->headers,$headers);
        else
            $request_headers = $this->headers;
        // Add any custom fields to the request
        if(isset($params) && $params !== null && !empty($params)) {
            if($request_headers['Content-Type'] == 'application/json') {
                $json_params = json_encode($params);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $json_params);
            } else {
                $http_params = http_build_query($params);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $http_params);
            }
        }
        // Modify the request depending on the type of request
        switch($method)
        {
            case 'POST':
//                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, null);
                curl_setopt($this->handle, CURLOPT_POST, true);
                break;
            case 'GET':
//                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, null);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, '');
                curl_setopt($this->handle, CURLOPT_POST, false);
                if(isset($params) && $params !== null && !empty($params))
                    $url .= '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                break;
            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->createCurlHeaderArray($request_headers));
        $response = curl_exec($this->handle);
        $response = $this->checkForRequestErrors($response, $this->handle);
        $result = json_decode($response);
        if($this->return_type === self::RETURN_OBJECT) {
            return $result;
        } elseif($this->return_type === self::RETURN_ARRAY_A) {
            return $this->objectToArray($result);
        }
    }
    /**
     * Makes the header array have the right format for the Salesforce API
     *
     * @param $headers
     * @return array
     */
    private function createCurlHeaderArray($headers) {
        $curl_headers = '';
        // Create the header array for the request
        foreach($headers as $key => $header) {
            $curl_headers[] = $key . ': ' . $header;
        }
        return $curl_headers;
    }
    /**
     * Checks for errors in a request
     *
     * @param string $response The response from the server
     * @param Resource $handle The CURL handle
     * @return string The response from the API
     * @throws SalesforceAPIException
     * @see http://www.salesforce.com/us/developer/docs/api_rest/index_Left.htm#CSHID=errorcodes.htm|StartTopic=Content%2Ferrorcodes.htm|SkinName=webhelp
     */
    private function checkForRequestErrors($response, $handle) {
        $curl_error = curl_error($handle);
        if($curl_error !== '') {
            throw new SalesforceAPIException($curl_error);
        }
        $request_info = curl_getinfo($handle);
        $message = 'message';
        $success = 'success';
        switch($request_info['http_code']) {
            case 304:
            //if($response === '')
            //return json_encode($message->'The requested object has not changed since the specified time');
            //    break;
            case 300:
            case 200:
            case 201:
            case 204:
                if($response === '')
                    return json_encode($success->true);
                break;
            default:
                if(empty($response) || $response !== '')
                    throw new SalesforceAPIException($response);
                else {
                    $result = json_decode($response);
                    if(isset($result->error))
                        throw new SalesforceAPIException($result->error_description);
                }
                break;
        }
        $this->last_response = $response;
        return $response;
    }
}
abstract class APIAbstract {
    /**
     * Converts objects returned into arrays.
     * This is necessary when returning complex objects.
     * For example, an object returned from a search using a cross-object reference cannot be displayed using methods to display simple objects...
     *   /api/task/search?fields=project:name
     *   /api/task/search?fields=DE:Parameter Name
     * both contain colons, which will result in a stdClass error when using the methods to reference simple objects.
     * The function below provides a way to convert the 'project:name' object into a usuable array
     *   i.e. $task['project:name'] can be used by placing the returned object into the function
     *
     */
    function objectToArray ( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
        {
            return $object;
        }
        if( is_object( $object ) )
        {
            $object = get_object_vars( $object );
        }
        return array_map( array($this, 'objectToArray'), $object );
    }
}
class SalesforceAPIException extends Exception {}
