<?php
/*
 *  Copyright 2011 Bookboon.com Ltd.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * 
 */

if (!function_exists('curl_init')) {
  throw new Exception('Bookboon requires the curl PHP extension');
}
if (!function_exists('json_decode')) {
  throw new Exception('Bookboon requires the json PHP extension');
}


class Bookboon
{
    private $authenticated = array();
    
    private $url = "api.bookboon.com";
    
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'bookboon-php-0.1',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    );
    
    function __construct ($APIKEY = "", $UniqueUserIdentifier = "")
    {
        if ((!empty($APIKEY)) AND (!empty($UniqueUserIdentifier)))
        {
            if ($this->validateUserID($UniqueUserIdentifier))
            {
                $this->authenticated['apikey'] = $APIKEY;
                $this->authenticated['handle'] = $UniqueUserIdentifier; 
            } else
                throw new Exception('Handle is invalid (see documentation)'); 
        }
    }
    
    /**
    * Validate the UserID or Handle
    * Check if the input is RFC 2617 compatible
    * 
    * @param string $UniqueUserIdentifier The unique user id
    * @return boolean
    */
    private function validateUserID($UniqueUserIdentifier)
    {
        /* handle cannot be longer than 64 chars and cannot contain colon. 
         * See Documentation at http://api.bookboon.com/docs 
         */
        if (strstr($UniqueUserIdentifier, ':') OR strlen($UniqueUserIdentifier) > 64)
            return false;
        else
            return true;
    }
    
    /**
    * Makes the call to the api.
    * 
    * @param string $relative_url The url relative to the address. Must begin with '/'
    * @param array $vars must contain subarray called either 'post' or 'get' depend on HTTP method
    * @return array results of call
    */
    public function api($relative_url, $vars = array())
    {
        if (!substr($relative_url, 1, 1) == '/')
        {
            throw new Exception('Location must begin with forward slash');
        }
        
        if (isset($vars['get']))
            $queryUrl = $this->url . $relative_url . "?" . $this->encodeVariables($vars['get']);
        else
            $queryUrl = $this->url . $relative_url;
            
        $http = curl_init();
        
        curl_setopt($http, CURLOPT_URL, "http://".$queryUrl);
        
        if (isset($vars['post']))
        {
            curl_setopt($http, CURLOPT_POST, count($vars['post']));
            curl_setopt($http, CURLOPT_POSTFIELDS, $this->encodeVariables($vars['post']));
        }
        
        foreach (self::$CURL_OPTS as $key=>$val) 
            curl_setopt($http, $key, $val);
        
        $response = curl_exec($http);
        
        $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

        /* Requires Authentication & SSL */
        if ($http_status == 401) 
        {
            if (empty($this->authenticated)) 
                throw new Exception('Function call requires authenticated class');
            
            curl_setopt($http, CURLOPT_CAINFO, dirname(__FILE__) . '/bookboon_ca.crt');
            curl_setopt($http, CURLOPT_USERPWD, $this->authenticated['handle'].":".$this->authenticated['apikey']);
            curl_setopt($http, CURLOPT_URL, "https://" . $queryUrl);
            
            $response = curl_exec($http);
            $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);
            if ($http_status == 401)
                throw new Exception('Incorrect Authentication');
        }
        curl_close($http);
        
        if ($http_status == 200)
            return json_decode($response, true);
        else
            throw new Exception('Unhandled HTTP ('.$http_status.') response code');
            
    }
    
    /**
    * Encodes variable for post or get strings
    * 
    * @param array $vars An arrays of varibles to encode
    * @return string variable in string form var=data&var=data..
    */
    private function encodeVariables($vars = array())
    {
        $varsString = "";
        if (!empty($vars) && is_array($vars))
        {
            foreach($vars as $key=>$value) 
                $varsString .= preg_replace("/\[[0-9]\]$/", "", $key).'='.$value.'&'; 
                
            $varsString = rtrim($varsString,'&');

            return $varsString;
        }
        return false;
    }   
    
    /**
    * Useful GUID validator to validate input in scripts
    * 
    * @param string $guid GUID to validate
    * @return boolean true if valid, false if not
    */
    public function isValidGUID($guid)
    {
       if (!preg_match("^(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})$^", $guid))
          return false;
       else
          return true;
    }
    
}
?>

