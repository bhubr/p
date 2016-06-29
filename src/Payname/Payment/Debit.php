<?php
/**
 * File debit
 */

namespace Payname\Payment;

use \Payname\Payname;


/**
 * Debit
 *
 * @package  Payname
 * @subpackage  Debit
 */
class Debit extends Base {

    /**
     * Debit hash, public ID
     * @var  string
     */
    public $hash = '';


    /**
     * Parent payment hash
     * @var  string
     */
    public $payment = '';


    /**
     * Related user hash
     * @var  string
     */
    public $user = '';


    /**
     * Debit transfert method
     *
     * Avalable methods:
     * - `card` Debit via payment card
     * - `iban` Debit via IBAN
     *
     * @var  string  Enumeration
     */
    public $method = '';


    /**
     * Token to use
     * @var  string
     */
    public $token = '';


    /**
     * Debit status
     *
     * Supported values:
     * - `W_USER` -> User not validated. Either because of missing required data
     *   or pending support validation
     * - `W_METHOD` -> Validating transfer method and authorizations
     *   Ex. 3DSecure, IBAN authorization, etc.
     * - `W_EXEC` -> User and authorizations OK, waiting for actual transfer
     *   order.
     *   <br/>Ex. if `due_at` is set, debit will stay in this state till due
     *   date is reached.
     * - `F_SENT` -> Transfer order send to bank
     * - `F_DONE` -> *Definitive state.* Transfer confirmed by bank, Debit finished
     * - `D_CANCELLED` -> *Definitive state.* Debit cancelled
     *
     * @var  string  Enumeration
     */
    public $status = '';


    /**
     * Debit planned due date
     *
     * Used to block debit execution before a specific date
     *
     * @var  date
     */
    public $due_at = null;


    /**
     * Debit actual transfert date
     *
     * @var  date
     */
    public $paid_at = null;


    /**
     * Debit amount
     *
     * @var  float
     */
    public $amount = null;


    /**
     * Method Data (for 3DS payments)
     */
    protected $method_data = null;



    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param  array  $fields  Field initial values
     */


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
     * Creates a new Debit
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Debit  Debit created
     */
    public static function create($options) {
        $paymentHash = $options['payment'];
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/debit',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);

        $debit = new Debit($response['data']);
        $debit->payment = $options['payment'];
        return $debit;
    }


    /**
     * Get an existing Debit
     *
     * @param  string  $paymentHash  Hash of parent payment
     * @param  string  $debitHash    Hash of the Debit to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Debit  Corresponding Debit
     */
    public static function get($paymentHash, $debitHash) {
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/debit/' . $debitHash
        );
        $response = Payname::get($requestOptions);
        $debit = new Debit($response['data']);
        $debit->payment = $paymentHash;
        return $debit;
    }


    /**
     * Get a list of debits
     *
     * @param  string  $paymentHash  Hash of parent payment
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of Debits
     */
    public static function getAll($paymentHash) {
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/debit'
        );
        $response = Payname::get($requestOptions);
        return array_map(function($debitProperties) {
            return new Debit($debitProperties);
        }, $response['data']);
     }


    /**
     * Update debit in the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Debit  Current Debit instance
     */
    public function update() {
        $requestOptions = array(
            'url' => '/payment/' . $this->payment . '/debit/' . $this->hash,
            'postData' => get_object_vars($this)
        );
        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $this;
    }


    /**
     * Delete debit on the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Debit  Current debit instance
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/payment/' . $this->payment . '/debit/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        $this->_load($response['data']);
        return $this;
    }


    /**
     * Is the payment waiting for 3D-Secure Form validation by user
     *
     * @return bool  true if the payment is awaiting 3DS validation
     */
    public function isWaiting3DS()
    {
        return $this->status === 'W_METHOD' && !is_null($this->method_data);
    }


    /**
     * Return params to use to redirect the user to 3D-Secure form
     *
     * @return array  Fields for 3DS validation
     */
    public function get3DSInfo() {
        if(is_null($this->method_data)) {
            return [];
        }
        return json_decode($this->method_data, true);
    }
}
