<?php

/**
 * File Iban
 */

namespace Payname\User;

use \Payname\Payname;
use \Payname\Exception;
use \Payname\ProtectedConstructor;

/**
 * User IBANs
 *
 * @package  Payname
 * @subpackage  User
 */
class Iban extends ProtectedConstructor {

    /**
     * IBAN hash
     * @var  string
     */
    public $hash = null;


    /**
     * Parent user hash
     * @var  string
     */
    public $user = null;


    /**
     * IBAN code
     * @var  string
     */
    public $iban = null;


    /**
     * Is default IBAN ?
     * @var  boolean
     */
    public $master = null;


    /**
     * Is test or prod IBAN ?
     * @var  boolean
     */
    public $is_prod = null;


    /**
     * IBAN title
     * @var  string
     */
    public $title = null;

    public $status = null;


    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param  array  $fields  Field initial values
     */
    public function __construct($fields = array()) {
        parent::__construct($fields);
    }



    /**
     * DEPRECATION handling.
     *
     * Old SDK's Iban had a is_default propery, now replaced by master.
     * We still can access is_default through this magic method
     */
    public function __get($prop) {
        if ($prop === 'is_default') {
            return $this->master;
        }
    }

    // -------------------------------------------------------------------------
    // PUBLIC METHODS
    // -------------------------------------------------------------------------

    /**
     * Create a new IBAN
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Iban  IBAN created
     */
    public static function create($options) {
        $userHash = $options['user'];
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/iban',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);
        $ibanProperties = array_merge(['user' => $userHash], $response['data']);
        return new Iban($ibanProperties);
    }


    /**
     * Get an existing IBAN
     *
     * @param  string  $userHash  Hash of parent user
     * @param  string  $ibanHash      Hash of the IBAN to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Iban  Corresponding IBAN
     */
    public static function get($userHash, $ibanHash) {
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/iban/' . $ibanHash
        );
        $response = Payname::get($requestOptions);
        $iban = new Iban($response['data']);
        $iban->user = $userHash;
        return $iban;
    }


    /**
     * Get a list of IBANs
     *
     * @param  string  $userHash  Hash of parent user
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of IBANs
     */
    public static function getAll($userHash) {
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/iban'
        );
        $response = Payname::get($requestOptions);
        return array_map(function($iban) use($userHash) {
            $ibanProperties = array_merge(['user' => $userHash], $iban);
            return new Iban($ibanProperties);
        }, $response['data']);
     }


    /**
     * Update current IBAN
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function update() {
        $requestOptions = array(
            'url' => '/user/' . $this->user . '/iban/' . $this->hash,
            'postData' => get_object_vars($this)
        );
        $response = Payname::put($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }


    /**
     * Delete an existing IBAN
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/user/' . $this->user . '/iban/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }
}
