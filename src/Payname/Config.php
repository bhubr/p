<?php

/**
 * Configuration file
 */
namespace Payname;


/**
 * Configuration class
 *
 * @package  Payname
 */
class Config {


    /**
     * Shop ID
     * @var  string
     */
    protected static $_id = '';


    /**
     * Secret key
     * Use the "test" one for sandbox testing
     * Will be used as auth token on simple auth mode
     * @var  string
     */
    protected static $_secret = '';


    /**
     * API host
     * @var  string
     */
    protected static $_host = 'https://api.payname.fr/v2';

    /**
     * Enable OAuth yes/no
     * false = simple auth mode, automatically handled by \Payname class
     * true = OAuth only.
     *     Must use \Payname\Auth\Auth::token()
     *     then \Payname\Payname::token() to enamble the SDK.
     * @var boolean
     */
    protected static $_useOAuth = false;


    /**
     * Enable use of cURL for HTTP calls
     * Requires cURL installed
     * @var boolean
     */
    protected static $_useCURL = false;


    /**
     * Bulk set parameters
     */
    public static function setup($id, $secret, $useOAuth = true, $useCURL = true)
    {
        self::$_id = $id;
        self::$_secret = $secret;
        self::$_useOAuth = $useOAuth;
        self::$_useCURL = $useCURL;
        if(defined('PAYNAME_API_HOST_OVERRIDE')) {
            self::$_host = PAYNAME_API_HOST_OVERRIDE;
        }
    }

    /**
     * Set Payname account ID
     *
     * @param string $secret Payname account ID
     */
    public static function setId($id)
    {
        self::$_id = $id;
    }


    /**
     * Check that API has been configured
     */
    protected static function check()
    {
        if (empty(self::$_id) || empty(self::$_secret)) {
            throw new Exception('Payname API is not configured. Use Payname\Config::setup($id, $secret, ...) to do it');
        }
    }

    /**
     * Get Payname account ID
     *
     * @return string Payname account ID
     */
    public static function id()
    {
        self::check();
        return self::$_id;
    }


    /**
     * Set secret key
     *
     * @param string $secret Payname secret key
     */
    public static function setSecret($secret)
    {
        self::$_secret = $secret;
    }


    /**
     * Get secret key
     *
     * @return string Payname secret key
     */
    public static function secret()
    {
        self::check();
        return self::$_secret;
    }


    /**
     * Get host
     *
     * @return string Payname API HOST
     */
    public static function host()
    {
        return self::$_host;
    }


    /**
     * Set OAuth
     *
     * @param bool $useOAuth flag to enable/disable OAuth
     */
    public static function setOAuth($useOAuth)
    {
        self::$_useOAuth = $useOAuth;
    }


    /**
     * Check if OAuth is enabled
     *
     * @param bool true if OAuth enabled
     */
    public static function useOAuth()
    {
        return self::$_useOAuth;
    }


    /**
     * Set cURL
     *
     * @param bool $useOAuth flag to enable/disable cURL
     */
    public static function setCURL($useCURL)
    {
        self::$_useCURL = $useCURL;
    }


    /**
     * Has cURL
     *
     * @param bool true if cURL enabled
     */
    public static function useCURL()
    {
        return self::$_useCURL;
    }
}
