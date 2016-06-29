<?php

/**
 * File credit
 */

namespace Payname\Payment;

use \Payname\Payname;


/**
 * Credit
 *
 * @package  Payname
 * @subpackage  Credit
 */
class Credit extends Base {

    /**
     * Credit hash, public ID
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
     * Credit transfert method
     *
     * Avalable methods:
     * - `iban` Credit via IBAN
     * - `Marketplace` *Reserved* Represents marketplace commission
     * - `Payname` *Reserved* Represents payname commission
     * - `URSSAF` *Reserved* Represents URSSAF part
     *
     * @var  string  Enumeration
     */
    public $method = '';


    /**
     * Credit status
     *
     * Supported values:
     * - `W_USER` -> User not validated. Either because of missing required data
     *   or pending support validation
     * - `W_METHOD` -> Validating transfer method and authorizations
     *   Ex. IBAN authorization, etc.
     * - `W_EXEC` -> User and authorizations OK, waiting for actual transfer
     *   order.
     *   <br/>Ex. if `due_at` is set, credit will stay in this state till due
     *   date is reached.
     * - `F_SENT` -> Transfer order send to bank
     * - `F_DONE` -> *Definitive state.* Transfer confirmed by bank, Credit finished
     * - `D_CANCELLED` -> *Definitive state.* Credit cancelled
     *
     * @var  string  Enumeration
     */
    public $status = '';


    /**
     * Credit planned due date
     *
     * Used to block credit execution before a specific date
     *
     * @var  date
     */
    public $due_at = null;


    /**
     * Credit actual transfert date
     *
     * @var  date
     */
    public $paid_at = null;


    /**
     * Credit amount
     *
     * @var  float
     */
    public $amount = null;




    // -------------------------------------------------------------------------
    // PUBLIC METHODS
    // -------------------------------------------------------------------------

    /**
     * Creates a new Credit
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Credit  Credit created
     */
    public static function create($options) {
        $paymentHash = $options['payment'];
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/credit',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);

        $credit = new Credit($response['data']);
        $credit->payment = $options['payment'];
        return $credit;
    }


    /**
     * Get an existing Credit
     *
     * @param  string  $paymentHash  Hash of parent payment
     * @param  string  $creditHash   Hash of the Credit to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Credit  Corresponding Credit
     */
    public static function get($paymentHash, $creditHash) {
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/credit/' . $creditHash
        );
        $response = Payname::get($requestOptions);
        $credit = new Credit($response['data']);
        $credit->payment = $paymentHash;
        return $credit;
    }


    /**
     * Get a list of credits
     *
     * @param  string  $paymentHash  Hash of parent payment
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of Credits
     */
    public static function getAll($paymentHash) {
        $requestOptions = array(
            'url' => '/payment/' . $paymentHash . '/credit'
        );
        $response = Payname::get($requestOptions);
        return $response['data'];
     }


    /**
     * Update credit in the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Credit  Current Credit instance
     */
    public function update() {
        $requestOptions = array(
            'url' => '/payment/' . $this->payment . '/credit/' . $this->hash,
            'postData' => get_object_vars($this)
        );
        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $this;
    }


    /**
     * Delete credit on the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Credit  Current credit instance
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/payment/' . $this->payment . '/credit/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        $this->_load($response['data']);
        return $this;
    }
}
