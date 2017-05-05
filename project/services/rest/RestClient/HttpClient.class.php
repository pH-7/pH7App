<?php

/*
 * Copyright 2015 KikApp
 * @version 0.1.5
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class HttpClient {

    private $curl;
    private $error = null;

    protected static $_JSON_messages = array(JSON_ERROR_NONE => 'No error has occurred', JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded', JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON', JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded', JSON_ERROR_SYNTAX => 'Syntax error', JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded. You should use utf8_encode().');

    public static function getInstance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct() {
        $this -> curl = curl_init();
    }

    function __destruct() {
        curl_close($this -> curl);
    }

    /**
     * Create a http request
     *
     * @access public
     * @since 1.0
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return array
     */
    public function getURL($method, $url, $data = false, $headers = false) {

        $curl = $this -> curl;

        switch ($method) {
            case "POST" :
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    if($headers){
                        $this->setHeaders($headers);
                    }
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this -> _cleanInputs($data));
                }
                break;
            case "PUT" :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this -> _cleanInputs($data)));
                }
                break;
            case "DELETE" :
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this -> _cleanInputs($data));
                }
                break;
            case "GET" :
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($this -> _cleanInputs($data)));
                }
                break;
            default :
                $this -> setError('Invalid Method', 405);
                break;
        }

        if (is_null($this -> getError())) {

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);

            if ($result === false) {
                $info = curl_getinfo($curl);
                $this -> setError('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
                return false;
            } else {

                return $result;
            }
        }
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this -> _cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    public function getHeaders($url) {

        // Create a curl handle
        $ch = $this -> curl;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Execute
        curl_exec($ch);

        // Check if any error occurred
        if (!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            return $info;

        }

        //Close handle
        curl_close($ch);
    }

    /**
     *
     */
    public function setHeaders($headers) {
        curl_setopt($this -> curl, CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * Recursive renameJsonKeys of json keys
     *
     * @access public
     * @since 1.0
     * @param array $originalArray, given array
     * @param string $mainElement, useful for recursion
     * @param array $merge, new key values
     * @return array $returnArray, result of recursive rename
     */
    public function renameJsonKeys($originalArray, $mainElement = null, $merge, &$returnArray) {

        if (!empty($originalArray)) {

            foreach ($originalArray as $key => $value) {

                if (is_array($value)) {//if is array, recursion continue
                    $this -> renameJsonKeys($value, $key, $merge, $returnArray);

                } else {//if is an element, raname it

                    if (!is_null($mainElement)) {

                        if (array_key_exists($key, $merge)) {
                            $returnArray[$mainElement][ucfirst($merge[$key])] = $value;
                        } else {
                            $returnArray[$mainElement][$key] = $value;
                        }

                    } else {

                        if (array_key_exists($key, $merge)) {
                            $returnArray[($merge[$key])] = $value;
                        } else {
                            $returnArray[$key] = $value;
                        }

                    }

                }

            }

        }

    }

    /**
     * Paginate json
     *
     * @access public
     * @since 1.0
     * @param int $start
     * @param int $count
     * @param array $returnArray
     */

    public function paginate($start, $count, &$returnArray) {
        if ($start != '' && $count != '') {
            $returnArray = array_slice($returnArray, $start, $count, false);
        }
    }

    /**
     * Recursive search in json
     *
     * @access public
     * @since 1.0
     * @param array $array, original array
     * @param array $key, array of keys to find
     * @param data $value, value to find
     * @return array with elements found
     */

    public function search($array, $key, $value) {
        $results = array();
        if (!empty($key) && $value != '') {
            $this -> search_r($array, $key, $value, $results);
            return $results;
        } else {
            return $array;
        }

    }

    public function search_r($array, $key, $value, &$results) {
        if (!is_array($array)) {
            return;
        }

        foreach ($key as $keyIndex => $keyValue) {

            if (isset($array[$keyValue]) && !in_array($array, $results)) {

                $found = strpos(strtolower($array[$keyValue]), strtolower($value));
                if ((strtolower($array[$keyValue]) == strtolower($value) || $found !== false)) {
                    $results[] = $array;
                }

            }
        }
        foreach ($array as $subarray) {
            $this -> search_r($subarray, $key, $value, $results);
        }
    }

    public function setError($errorMsg) {
        $this -> error = $errorMsg;
    }

    public function getError() {
        return $this -> error;
    }

    /**
     * String Json decode
     *
     * @access public
     * @since 1.0
     * @param string $string, string to decode
     * @return string decoded
     */

    public function jsonDecode(&$string) {
        $string = json_decode($string, true);
        if ($string) {
            return;
        }
        echo __FUNCTION__ . ">" . static::$_JSON_messages[json_last_error()];

    }

    /**
     * String Json encode
     *
     * @access public
     * @since 1.0
     * @param string $string, string to encode
     * @return string decoded
     */

    public function jsonEncode(&$string) {
        $string = json_encode($string, true);
        $string = str_replace("\\", "", $string);
        if ($string) {
            return;
        }
        echo __FUNCTION__ . ">" . static::$_JSON_messages[json_last_error()];
    }

}

/**
 * getLastKey - return the las element key of array
 *
 * @access public
 * @since 1.0
 * @param array $array
 * @return string
 */

function getLastKey($array) {
    end($array);
    return key($array);
}


/**
 * Create services for Dynamic Combo Box.
 * @access public
 * @since 1.0
 * @param array $array, original array
 * @param array $key, name of key
 * @param data $val, name to value
 * @return String with elements found
 */
function createSvc($result, $key, $val) {
    $print = "[";
    foreach ($result as $value) {
        $print .= "[\"" . $value -> $key . "\"" . ",\"" . $value -> $val . "\"],";
    }
    $print .= "]";
    return $print;
}

?>