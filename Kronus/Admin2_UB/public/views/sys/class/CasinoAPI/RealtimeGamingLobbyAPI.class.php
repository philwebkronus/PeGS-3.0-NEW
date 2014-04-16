<?php
/**
 * Lobby API Calls
 *
 * @author elperez
 * @date 05-14-2012
 */
class RealtimeGamingLobbyAPI {
    /**
     * Holds the web service end point
     * @var string
     */
    private $_url = '';
    
    /**
     * Set caching of connection
     * @var boolean 
     */
    private $_caching = 0;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';

    /**
     * Path to certificate file
     * @var string
     */
    private $_certFilePath = '';

    /**
     * Path to certificate key file
     * @var string
     */
    private $_keyFilePath = '';

    /**
     * Certificate key file passphrase
     * @var string
     */
    private $_passPhrase = '';

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

    /**
     * Error message
     * @var string
     */
    private $_error;

    /**
     * Holds array response
     * @var array
     */
    private $_APIresponse;

    public function __construct()
    {
        $argv = func_get_args();

        switch ( func_num_args() )
        {
            default:
            case 4: self::__construct1( $argv[0], $argv[1], $argv[2], $argv[3] ); break;
            case 5: self::__construct2( $argv[0], $argv[1], $argv[2], $argv[3], $argv[4] ); break;
        }
    }
	
    public function __construct1( $url = '', $certFilePath = '', $keyFilePath = '', $passPhrase = '' )
    {
        $this->_url = $url;
        $this->_certFilePath = $certFilePath;
        $this->_keyFilePath = $keyFilePath;
        $this->_passPhrase = $passPhrase;
    }
	
    public function __construct2( $wsdlUrl, $certFilePath = '', $keyFilePath = '', $passPhrase = '', $caching = FALSE )
    {
        $this->_url = $url;
        $this->_certFilePath = $certFilePath;
        $this->_keyFilePath = $keyFilePath;
        $this->_passPhrase = $passPhrase;
        $this->_caching = $caching;        
    }
    
    public function getError()
    {
    	return $this->_error;
    }
    
    /**
     * This is used to get Last Games Description Only
     * @param string $url
     * @param big int $pid
     * @param int $gamesnum
     * @return object 
     */
    public function getLastGamesPlayed($url, $pid, $gamesnum){
        
        $data = array(
            'PID'=>$pid,
            'numGames'=>$gamesnum
        );
        
        $response = $this->submitRequest($url . '/GetLastGamesPlayed', http_build_query($data));
        
        if ( $response[0] == 200 )
        {
            $games = $this->xml2array2( $response[1] );
            $this->_APIresponse = $games['DataSet']['diffgr:diffgram']['NewDataSet']['Table'];
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }
        
        return $this->_APIresponse;
    }
    
    /**
     * This a requirement to get LastGamesPlayed in RTG Lobby APi
     * @param string $url
     * @param string $login
     * @return object 
     */
    public function GetPIDFromLogin($url, $login )
    {	
        $data = array( 'login' => $login );

        $response = $this->SubmitRequest( $url . '/GetPIDFromLogin', http_build_query( $data ) );
        
        if ( $response[0] == 200 )
        {                        
            $this->_APIresponse = $this->XML2Array( $response[1] );
            #$this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse[0] );
            if(count($this->_APIresponse) == 0)
            {
              $this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse);
            }
            
            else{
              $this->_APIresponse = array( 'GetPIDFromLoginResult' => $this->_APIresponse[0]);    
            }
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }

        return $this->_APIresponse;
    }
    
    private function submitRequest( $url, $data )
    {
        $curl = curl_init( $url . '?' . $data );

        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSLCERTTYPE, 'PEM' );
        curl_setopt( $curl, CURLOPT_SSLCERT, $this->_certFilePath );
        curl_setopt( $curl, CURLOPT_SSLKEYTYPE, 'PEM' );
        curl_setopt( $curl, CURLOPT_SSLKEY, $this->_keyFilePath );
        curl_setopt( $curl, CURLOPT_SSLKEYPASSWD, $this->_passPhrase );
        curl_setopt( $curl, CURLOPT_POST, FALSE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }

    /**
     * Using simplexml_load_string
     * @param str $xmlString
     * @return object 
     */
    private function XML2Array( $xmlString )
    {
        $xml = simplexml_load_string( $xmlString );

        $json = json_encode( $xml );

        return json_decode( $json, TRUE );
    }
    
    /**
     * xml2array() will convert the given XML text to an array in the XML structure.
     * Link: http://www.bin-co.com/php/scripts/xml2array/
     * Arguments : $contents - The XML text
     *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
     *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
     * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
     * Examples: $array =  xml2array(file_get_contents('feed.xml'));
     *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
     */ 
    function xml2array2($contents, $get_attributes=1, $priority = 'tag') {
        if(!$contents) return array();

        if(!function_exists('xml_parser_create')) {
            //print "'xml_parser_create()' function not found!";
            return array();
        }

        //Get the XML parser of PHP - PHP must have this module for the parser to work
        $parser = xml_parser_create('');
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if(!$xml_values) return;//Hmm...

        //Initializations
        $xml_array = array();
        $parents = array();
        $opened_tags = array();
        $arr = array();

        $current = &$xml_array; //Refference

        //Go through the tags.
        $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
        foreach($xml_values as $data) {
            unset($attributes,$value);//Remove existing values, or there will be trouble

            //This command will extract these variables into the foreach scope
            // tag(string), type(string), level(int), attributes(array).
            extract($data);//We could use the array by itself, but this cooler.

            $result = array();
            $attributes_data = array();

            if(isset($value)) {
                if($priority == 'tag') $result = $value;
                else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
            }

            //Set the attributes too.
            if(isset($attributes) and $get_attributes) {
                foreach($attributes as $attr => $val) {
                    if($priority == 'tag') $attributes_data[$attr] = $val;
                    else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }

            //See tag status and do the needed.
            if($type == "open") {//The starting of the tag '<tag>'
                $parent[$level-1] = &$current;
                if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                    $current[$tag] = $result;
                    if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag.'_'.$level] = 1;

                    $current = &$current[$tag];

                } else { //There was another element with the same tag name

                    if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                        $repeated_tag_index[$tag.'_'.$level]++;
                    } else {//This section will make the value an array if multiple tags with the same name appear together
                        $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                        $repeated_tag_index[$tag.'_'.$level] = 2;

                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                    }
                    $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                    $current = &$current[$tag][$last_item_index];
                }

            } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
                //See if the key is already taken.
                if(!isset($current[$tag])) { //New Key
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

                } else { //If taken, put all things inside a list(array)
                    if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                        // ...push the new element into that array.
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                        if($priority == 'tag' and $get_attributes and $attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag.'_'.$level]++;

                    } else { //If it is not an array...
                        $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                        $repeated_tag_index[$tag.'_'.$level] = 1;
                        if($priority == 'tag' and $get_attributes) {
                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                                $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                                unset($current[$tag.'_attr']);
                            }

                            if($attributes_data) {
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                    }
                }

            } elseif($type == 'close') { //End of tag '</tag>'
                $current = &$parent[$level-1];
            }
        }

        return($xml_array); 
    }
}

?>
