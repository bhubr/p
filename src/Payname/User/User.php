<?php

/**
 * File User
 */

namespace Payname\User;

use \Payname\Package;
use \Payname\Payname;
use \Payname\User\Doc;
use \Payname\User\Iban;
use \Payname\Card\Card;
use \Payname\Exception;
use \Payname\CRUDInterface;
use \Payname\ProtectedConstructor;

/**
 * User
 *
 * @package  Payname
 * @subpackage  User
 */
class User extends ProtectedConstructor implements CRUDInterface {

    /**
     * User hash
     * @var  string
     */
    public $hash = '';


    /**
     * User Email address
     * @var  string
     */
    public $email = null;


    /**
     * User phone number
     * @var  string
     */
    public $phone = null;


    /**
     * User first name
     * @var  string
     */
    public $first_name = null;


    /**
     * User last name
     * @var  string
     */
    public $last_name = null;


    /**
     * User address
     * @var  string
     */
    public $address = null;


    /**
     * User city
     * @var  string
     */
    public $city = null;


    /**
     * User postal_code
     * @var  string
     */
    public $postal_code = null;


    /**
     * User birthdate
     * @var  date
     */
    public $birthdate = null;


    /**
     * User secu
     * @var  string
     */
    public $secu = null;

    public $status = null;


    /**
     * User ibans
     * @var  string
     */
    protected $_ibans = [];


    /**
     * User documents
     * @var  string
     */
    protected $_docs = [];


    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param  array  $fields  Field initial values
     */
    public function __construct($email, $fields = []) {
        parent::__construct($fields);
        $this->email = $email;
        $this->ibans = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
    }


    // -------------------------------------------------------------------------
    // PUBLIC METHODS
    // -------------------------------------------------------------------------




    /*--------------
     | Accessors
     *--------------
     |
     */
    public function getEmail() {
        return $this->email;
    }

    public function getCity() {
        return $this->city;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function getBirthDate() {
        $birthdate = new \DateTime();
        $birthdate->setTimestamp($this->birthdate);
        return $birthdate->format('Y-m-d');
    }

    public function getIbans() {
        return $this->ibans;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getZipCode() {
        return $this->postal_code;
    }

    public function getSocialSecurityNumber() {
        return $this->secu;
    }

    public function getAddress() {
        return $this->address;
    }


    /**
     * Creates a new User
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  User  User created
     */
    public static function create($options = []) {
        $requestOptions = array(
            'url' => '/user',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);
        $user = new User($response['data']['email'], $response['data']);
        if (array_key_exists('iban', $response['data'])) {
            foreach($response['data']['iban'] as $returnedIban) {
                $user->_ibans[] = new Iban($returnedIban);
            }
        }
        return $user;
    }


    /**
     * Get an existing User
     *
     * @param  string  $hash  Hash of the User to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  User  Corresponding User
     */
    public static function get($hash) {
        $requestOptions = array(
            'url' => '/user/' . $hash
        );
        $response = Payname::get($requestOptions);
        return new User($response['data']['email'], $response['data']);
    }


    /**
     * Get a list of users
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of User objects
     */
    public static function getAll() {
        $requestOptions = array(
            'url' => '/user'
        );
        $response = Payname::get($requestOptions);
        return array_map(function($userProperties) {
            return new User($userProperties['email'], $userProperties);
        }, $response['data']);

     }


    /**
     * Update user in the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function update() {
        $requestOptions = array(
            'url' => '/user/' . $this->hash,
            'postData' => get_object_vars($this)
        );
        $response = Payname::put($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }


    /**
     * Delete user on the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/user/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }



    // -------------------------------------------------------------------------
    // SUB-ENTITIES METHODS
    // -------------------------------------------------------------------------

    /**
     * Get all cards
     *
     * @param  string  $hash  (Optional) Hash of document to get
     *                         Default : null <=> get all related docs
     *
     * @throw  \Payname\Exception  On API Error
     *
     * @return  Doc|array  Requested Doc, or list of all related docs
     */
    public function cards() {
        return Card::getAll($this->hash);
    }

    /**
     * Get a related document, or all documents
     *
     * @param  string  $hash  (Optional) Hash of document to get
     *                         Default : null <=> get all related docs
     *
     * @throw  \Payname\Exception  On API Error
     *
     * @return  Doc|array  Requested Doc, or list of all related docs
     */
    public function doc($hash = null) {
        if ($hash) {
            // hash given => get one
            $response = Doc::get($this->hash, $hash);
        } else {
            // no hash => get all
            $response = Doc::getAll($this->hash);
        }
        return $response;
    }


    /**
     * Get a related IBAN, or all IBANs
     *
     * @param  string  $hash  (Optional) Hash of IBAN to get
     *                         Default : null <=> get all related ibans
     *
     * @throw  \Payname\Exception  On API Error
     *
     * @return  Iban|array  Requested Iban, or list of all related ibans
     */
    public function iban($hash = null) {
        if ($hash) {
            // hash given => get one
            $response = Iban::get($this->hash, $hash);
        } else {
            // no hash => get all
            $response = Iban::getAll($this->hash);
        }
        return $response;
    }
}
