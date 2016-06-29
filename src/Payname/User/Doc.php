<?php

/**
 * File Doc
 */

namespace Payname\User;

use \Payname\Payname;


/**
 * User documents
 *
 * @todo  doc type a verifier
 * @todo  Methode de transformation binaire => dataURI
 *
 * @package  Payname
 * @subpackage  User
 */
class Doc {

    /**
     * Document hash
     * @var  string
     */
    public $hash = '';


    /**
     * Document type
     * @var  string
     */
    public $type = null;


    /**
     * File content, base64 encoded
     * @var  string
     */
    public $file = null;


    /**
     * Parent user hash
     * @var  string
     */
    public $user = null;

    public $status = null;


    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param  array  $fields  Field initial values
     */
    protected function __construct($fields = array()) {
        $this->_load($fields);
    }


    /**
     * Load instance fields, erase any existing value
     *
     * Used to create/update an instance at once
     *
     * @param  array  $fields  Field values to set
     */
    protected function _load($fields = array()) {
        foreach ($fields as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }



    // -------------------------------------------------------------------------
    // PUBLIC METHODS
    // -------------------------------------------------------------------------

    /**
     * Create a new Doc
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Doc  Doc created
     */
    public static function create($options) {
        $userHash = $options['user'];
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/doc',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);
        $docProperties = array_merge(['user' => $userHash], $response['data']);
        return new Doc($docProperties);
    }


    /**
     * Get an existing Doc
     *
     * @param  string  $userHash  Hash of parent user
     * @param  string  $docHash   Hash of the Doc to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Doc  Corresponding Doc
     */
    public static function get($userHash, $docHash) {
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/doc/' . $docHash
        );
        $response = Payname::get($requestOptions);
        $docProperties = array_merge(['user' => $userHash], $response['data']);
        return new Doc($docProperties);
    }


    /**
     * Get a list of docs
     *
     * @param  string  $userHash  Hash of parent user
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of Docs
     */
    public static function getAll($userHash) {
        $requestOptions = array(
            'url' => '/user/' . $userHash . '/doc'
        );
        $response = Payname::get($requestOptions);
        return array_map(function($doc) use($userHash) {
            $docProperties = array_merge(['user' => $userHash], $doc);
            return new Doc($docProperties);
        }, $response['data']);
     }


    /**
     * Delete a doc
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/user/' . $this->user . '/doc/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }
}
