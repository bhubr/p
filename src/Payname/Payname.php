<?php

/**
 * File Payname
 */

namespace Payname;

use \Payname\Exception;
use \Payname\Config;


/**
 * Main SDK class
 *
 * @package  Payname
 */
class Payname {


    /**
     * Auth token
     */
    private static $_token = '';


    /**
     * Latest parsed result
     */
    private static $_lastError;


    /**
     * cURL instance
     */
    private static $_curl = null;


    /**
     * Return the last error returned by the API.
     *
     * @return Payname\Error  the last error (or null if last request OK)
     */
    public static function lastError() {
        return self::$_lastError;
    }



    /*--------------------------------------------------------------------------
     | Private API methods
     *--------------------------------------------------------------------------
     |
     */


    /**
     * Call URL with cURL
     *
     * @param  string  $method    HTTP Method to use (GET, PUT, etc.)
     * @param  string  $url       Complete URL to call
     * @param  array   $payload  (Optional) Array of key/values to send via POST
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  boolean|string  Raw API response
     */
    private static function _call_curl($method, $url, $payload = null) {

        $requestOptions = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array('Authorization: ' . static::$_token)
        );

        if (!is_null($payload)) {
            $requestOptions[CURLOPT_POSTFIELDS] = http_build_query($payload);
        }

        if (is_null(self::$_curl)) {
            self::$_curl = curl_init();
        }

        curl_setopt_array(self::$_curl, $requestOptions);

        return curl_exec(self::$_curl);
    }


    /**
     * Call URL with 'vanilla' PHP functions
     *
     * @param  string  $method    HTTP Method to use (GET, PUT, etc.)
     * @param  string  $url       Complete URL to call
     * @param  array   $payload  (Optional) Array of key/values to send via POST
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  boolean|string  Raw API response
     */
    private static function _call_vanilla($method, $url, $payload = null) {

        $options = [
            'http' => [
                'method' => $method,
                'ignore_errors' => true,
                'header' => 'Authorization: ' . static::token()
                    . "\n" . 'Content-type: application/json'
            ]
        ];

        if (!is_null($payload)) {
            $options['http']['content'] = json_encode($payload);
        }

        $fh = fopen($url, 'rb', false, stream_context_create($options));
        if (!$fh) {
            $raw = false;
        } else {
            $raw = stream_get_contents($fh);
        }

        if ($raw === false) {
            throw new Exception(
                $method . ' ' . $url . ' ERROR: ' . $php_errormsg
            );
        }
        return $raw;
    }


    /**
     * Call URL
     *
     * @param  array  $options  Call options
     *
     * @throws  Exception  On API error
     *
     * @return  array  API response
     */
    private static function _call($options) {

        if (!Config::useOAuth()) {
            static::token(Config::secret());
        }


        /* PHP Vanilla version */
        $method = $options['method'];
        $url = Config::host() . $options['url'];
        $payload = (isset($options['postData']))
            ? $options['postData']
            : null;

        if (Config::useCURL()) {
            $raw = static::_call_curl($method, $url, $payload);

        } else {
            $raw = static::_call_vanilla($method, $url, $payload);
        }
        $response = json_decode($raw, true);

        if ($response === null) {
            throw new Exception(
                $method . ' ' . $url . ' did not send valid JSON: ' . $raw
            );
        }
        if (!$response['success']) {
            self::$_lastError = Error::fromResponse($response);
            throw new Exception(self::$_lastError->formatAsExceptionMessage());
        }
        else if((array_key_exists('code', $response) && substr($response['code'], 0, 1) === 'W') ||
                (array_key_exists('error', $response) && substr($response['error'], 0, 1) === 'W')) {
            // Don't throw Exception but populate error
            self::$_lastError = \Payname\Error::fromResponse($response);
        }
        else {
            self::$_lastError = null;
        }

        return $response;
    }



    /*--------------------------------------------------------------------------
     | Public API methods
     *--------------------------------------------------------------------------
     |
     */


    /**
     * Set and/or return current token
     *
     * @param  string  $token  (Optional) New token to set, obtained via \Payname\Auth methods
     *                          Default : null <=> only return current token without setting
     */
    public static function token($token = null) {
        if (!is_null($token)) {
            static::$_token = $token;
        }
        return static::$_token;
    }


    /**
     * GET request
     *
     * @param   array  $options  GET request options
     *
     * @throws  Exception  On API error
     *
     * @return  array  API response
     */
    public static function get($options) {
        $options['method'] = 'GET';
        return static::_call($options);
    }


    /**
     * POST request
     *
     * @param   array  $options  POST request options
     *
     * @throws  Exception  On API error
     *
     * @return  array  API response
     */
    public static function post($options) {
        $options['method'] = 'POST';
        return static::_call($options);
    }


    /**
     * PUT request
     *
     * @param   array  $options  PUT request options
     *
     * @throws  Exception  On API error
     *
     * @return  array  API response
     */
    public static function put($options) {
        $options['method'] = 'PUT';
        return static::_call($options);
    }


    /**
     * DELETE request
     *
     * @param   array  $options  DELETE request options
     *
     * @throws  Exception  On API error
     *
     * @return  array  API response
     */
    public static function delete($options) {
        $options['method'] = 'DELETE';
        return static::_call($options);
    }
}
