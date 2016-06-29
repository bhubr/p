<?php

/**
 * File payment
 */

namespace Payname\Payment;

use \Payname\Payname;
use \Payname\Payment\Debit;
use \Payname\Payment\Credit;


/**
 * Payment
 *
 * @package  Payname
 * @subpackage  Payment
 */
class Payment extends Base {

    /**
     * Payment hash, public ID
     * @var  string
     */
    public $hash = '';


    /**
     * Payment order, external ID defined by customer
     * @var  string
     */
    public $order = '';


    /**
     * Payment status
     *
     * Supported values:
     * - Debit phase
     *   - `W_DEBIT` --> At least one debit execution is not finished
     * - Confirmation phase
     *   - `C_BALANCE` --> Total debit amount and total credit amount don`t match.
     *   - `C_DOUBLE_WAITING` --> Awaiting 1st confirmation (`double` confirmation mode only)
     *   - `C_WAITING` --> Awaiting confirmation (`simple` confirmation mode)
     *      or 2nd confirmation (`double` confirmation mode)
     * - Credit execution phase
     *   - `F_CREDIT` --> At least one Credit execution is not finished
     *   - `F_DONE` --> *Definitive state.* Payment finished
     * - Payment deleted
     *   - `D_ADMIN` --> *Definitive state.*
     *      Payment deleted by shop/marketplace owner
     * - Older deprecated states
	 *   - `W_WAITING` --> Moved to debit states
	 *   - `W_SENDING` --> Moved to debit states
	 *   - `W_TIMEOUT` --> Moved to debit states
	 *   - `W_3DS` --> Moved to debit states
	 *   - `W_IBAN` --> Moved to debit states as W_USER
	 *   - `F_IBAN` --> Moved to credit states as W_USER
	 *   - `F_SEND` --> Moved to credit states as F_SENT
	 *   - `F_RECEIVED` --> moved to credit states as F_DONE
     *
     * @var  string  Enumeration
     */
    public $status = '';


    /**
     * Payment confirmation strategy to apply
     *
     * Available strategies:
     * - `double`: Double confirmation. Requires 2 calls to confirm method to
     *   start credit execution process.
     * - `simple`: Simple confirmation. Require only one call to confirm method
     *   to start credit execution process
     * - `none`: No confirmation. Automatically start credit execution process
     *    when all Debits are executed
     *
     * @var  string  Enumeration
     */
    public $confirmation = '';


    /**
     * Commission rate to apply to payment
     * @var float
     */
    public $commission = 0;


    /**
     * Commission fixed to apply to payment
     * @var float
     */
    public $comm_fixed = 0;


    /**
     * External data
     *
     * Free to use field
     *
     * @var string
     */
    public $external_data = '';


    /**
     * Option : URSSAF
     *
     * Enables / Disables URSSAF management
     *
     * @link  http://api.payname.fr/documentation/#/how_to/sap
     *
     * @var boolean
     */
    public $option_urssaf = false;


    /**
     * Option URSSAF : Number of worked hours
     *
     * Used to calculate URSSAF amount
     *
     * @link  http://api.payname.fr/documentation/#/how_to/sap
     *
     * @var float
     */
    public $urssaf_nb_hours = 0;


    /**
     * Array of debits
     */
    protected $_debits = [];


    /**
     * Array of debits
     */
    protected $_credits = [];



    // -------------------------------------------------------------------------
    // DEPRECATED FIELDS
    // -------------------------------------------------------------------------

    /**
     * Payment amount
     * @deprecated  Use Debit->amount instead
     * @var  float
     */
    public $amount = 0;


    /**
     * Payment urssaf amount
     * @deprecated  Use Credit->amount for URSSAF credit instead
     * @var  float
     */
    public $urssaf = 0;


    /**
     * Payment payname commission amount
     * @deprecated  Use Credit->amount for Payname credit instead
     * @var  float
     */
    public $payname = 0;
    

    /**
     * Payment tax commission amount
     * @deprecated  Use Credit->amount for Marketplace credit instead
     * @var  float
     */
    public $tax = 0;


    /**
     * Payment presentation date
     * @deprecated  Use due_at instead
     * @var  string
     */
    public $presentationDate = '';


    /**
     * 3DS Test data
     * @deprecated  Use each Debit->test_3DS instead
     */
    public $test_3DS = [];



    // -------------------------------------------------------------------------
    // PROTECTED METHODS
    // -------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param  array  $fields  Field initial values
     */
    protected function __construct($fields = array()) {
        parent::__construct($fields);
        $this->_extractDebitsCredits($fields);
    }


    protected function _extractDebitsCredits($data) {
        if(array_key_exists('debit', $data)) {
            foreach($data['debit'] as $debitData) {
                $this->_debits[] = new Debit($debitData);
            }
        }
        if(array_key_exists('credit', $data)) {
            foreach($data['credit'] as $creditData) {
                $this->_credits[] = new Credit($creditData);
            }
        }
    }


    /*--------------------------------------------------------------------------
     | Public methods
     *--------------------------------------------------------------------------
     |
     */

    /**
     * Get credits associated to the payment.
     *
     * @return array  Array of Payname\Payment\Credit objects
     */
    public function getCredits()
    {
        return $this->_credits;
    }


    /**
     * Get debits associated to the payment.
     *
     * @return array  Array of Payname\Payment\Debit objects
     */
    public function getDebits()
    {
        return $this->_debits;
    }


    /**
     * Is the payment waiting for 3D-Secure Form validation by user
     *
     * @return bool  true if the payment is awaiting 3DS validation
     */
    public function isWaiting3DS()
    {
        foreach($this->_debits as $debit) {
            if($debit->isWaiting3DS()) {
                return true;
            }
        }
    }


    /**
     * Simulate amounts from a desired credit amount
     *
     * @param  float   $credit      Desired credit amount
     * @param  float   $commFixed   (Optional) Fixed commission to apply
     *                               Default = marketplace value
     * @param  float   $commRate    (Optional) Commission rate to apply
     *                               Default = marketplace value
     * @param  float   $nbHours     (For URSSAF only) Number of hours worked
     * @param  string  $postalCode  (For URSSAF only) Employee's postal code
     * @param  string  $birthDate   (For URSSAF only) Employer's birth date
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  array  Amounts simulated
     */
    public static function simulate($credit, $commFixed = null, $commRate = null, $nbHours = null, $postalCode = null, $birthDate = null)
    {
        $requestOptions = array(
            'url' => '/payment/simulate',
            'postData' => array(
                'credit' => $credit,
                'commission' => $commRate,
                'comm_fixed' => $commFixed,
                'nb_hours' => $nbHours,
                'postal_code' => $postalCode,
                'birthdate' => $birthDate
            )
        );
        $response = Payname::post($requestOptions);
        return $response['data'];
    }


    /**
     * Simulate amounts from a desired debit amount
     *
     * @param  float   $debit      Desired debit amount
     * @param  float   $commFixed   (Optional) Fixed commission to apply
     *                               Default = marketplace value
     * @param  float   $commRate    (Optional) Commission rate to apply
     *                               Default = marketplace value
     * @param  float   $nbHours     (For URSSAF only) Number of hours worked
     * @param  string  $postalCode  (For URSSAF only) Employee's postal code
     * @param  string  $birthDate   (For URSSAF only) Employer's birth date
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  array  Amounts simulated
     */
    public static function simulate_reverse($debit, $commFixed = null, $commRate = null, $nbHours = null, $postalCode = null, $birthDate = null)
    {
        $requestOptions = array(
            'url' => '/payment/simulate',
            'postData' => array(
                'debit' => $debit,
                'commission' => $commRate,
                'comm_fixed' => $commFixed,
                'nb_hours' => $nbHours,
                'postal_code' => $postalCode,
                'birthdate' => $birthDate
            )
        );
        $response = Payname::post($requestOptions);
        return $response['data'];
    }


    /**
     * Creates a new Payment
     *
     * @param  array  $options  Initial values
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Payment  Payment created
     */
    public static function create($options) {
        $requestOptions = array(
            'url' => '/payment',
            'postData' => $options
        );
        $response = Payname::post($requestOptions);

        if (isset($options['datas'])) {
            // Deprecated old way to create paymant
            if (isset($options['datas']['general'])) {
                $payment = static::get($options['datas']['general']['order_id']);
            } else {
                $payment = static::get($options['datas']['order_id']);
            }
            $payment->test_3DS = $response['data'];
        } else {
            // Official way: details returned in the response
            $payment = new Payment($response['data']);
        }

        return $payment;
    }


    /**
     * Get an existing Payment
     *
     * @param  string  $hash  Hash of the Payment to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Payment  Corresponding Payment
     */
    public static function get($hash) {
        $requestOptions = array(
            'url' => '/payment/' . $hash
        );
        $response = Payname::get($requestOptions);
        return new Payment($response['data']);
    }


    /**
     * Get a list of payments
     *
     * @todo  Implementer pagination
     *
     * @return  array  List of Payments
     */
    public static function getAll() {
        $requestOptions = array(
            'url' => '/payment'
        );
        $response = Payname::get($requestOptions);
        return $response['data'];
     }


    /**
     * Update payment in the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Payment  Current Payment instance
     */
    public function update() {
        $requestOptions = array(
            'url' => '/payment/' . $this->hash,
            'postData' => get_object_vars($this)
        );
        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $this;
    }


    /**
     * Proceed to debit execution
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function exec_debits() {
        $requestOptions = array(
            'url' => '/payment/' . $this->hash . '/exec_debits'
        );

        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $response['data'];
    }


    /**
     * Check payment balance
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function balance() {
        $requestOptions = array(
            'url' => '/payment/' . $this->hash . '/balance'
        );
        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $response['data'];
    }


    /**
     * Confirm payment
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function confirm() {
        if (count($this->credit()) > 0) {
            // Credits => "new" payment => new API
            $requestOptions = array(
                'url' => '/payment/' . $this->hash . '/confirm'
            );
        } else {
            // "old" API
            $requestOptions = [
                'url' => '/payment',
                'postData' => [
                    'action' => 'confirm',
                    'datas' => [
                        'order_id' => $this->order
                    ]
                ]
            ];
        }
        $response = Payname::put($requestOptions);
        if (isset($response['data'])) {
            $this->_load($response['data']);
        }
        return (isset($response['data']) ? $response['data'] : null);
    }


    /**
     * Proceed to credit execution
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  mixed  API response, if any
     */
    public function exec_credits() {
        $requestOptions = array(
            'url' => '/payment/' . $this->hash . '/exec_credits'
        );

        $response = Payname::put($requestOptions);
        $this->_load($response['data']);
        return $response['data'];
    }


    /**
     * Finalize 3DS test for payment
     *
     * Works only on direct payments (without debit/credits).
     * For payment with debits, see Debit
     *
     * In order to enable 3D-Secure confirmation, one has to provide
     * a 3DS callback URL in the Payname API Administration dashboard.
     *
     * After 3DS finalization, the bank website POSTs to the PHP script
     * that resides at the 3DS callback URL, passing it the PaRes and MD fields.
     *
     * The Payment::finalize3DS call is to be executed from this PHP script,
     * passing the PaRes and MD that have been received from the bank.
     *
     * @throw  \Payname\Exception  On API error
     *
     * @param  string  $pares PaRes returned by 3DS test form
     * @param  string  $md    Transaction number (MD) returned by 3DS test form
     *
     * @return  mixed  API response, if any
     */
    public static function finalize_3DS($pares, $md) {
        $requestOptions = [
            'url' => '/payment/finalize3ds',
            'postData' => [
                'PaRes' => $pares,
                'MD' => $md
            ]
        ];
        $response = Payname::post($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }


    /**
     * Delete payment on the platform
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Payment  Current payment instance
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/payment/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        $this->_load($response['data']);
        return $this;
    }



    // -------------------------------------------------------------------------
    // SUB-ENTITIES METHODS
    // -------------------------------------------------------------------------

    /**
     * Get a related debit, or all debits
     *
     * @param  string  $hash  (Optional) Hash of debit to get
     *                         <br/> Default : null <=> get all related debits
     *
     * @throw  \Payname\Exception  On API Error
     *
     * @return  Debit|array  Requested Debit, or list of all related debits
     */
    public function debit($hash = null) {
        if ($hash) {
            // hash given => get one
            $response = Debit::get($this->hash, $hash);
        } else {
            // no hash => get all
            $response = Debit::getAll($this->hash);
        }
        return $response;
    }


    /**
     * Get a related credit, or all credits
     *
     * @param  string  $hash  (Optional) Hash of credit to get
     *                         <br/> Default : null <=> get all related credits
     *
     * @throw  \Payname\Exception  On API Error
     *
     * @return  Credit|array  Requested Credit, or list of all related credits
     */
    public function credit($hash = null) {
        if (!is_null($hash)) {
            // hash given => get one
            $response = Credit::get($this->hash, $hash);
        } else {
            // no hash => get all
            $response = Credit::getAll($this->hash);
        }
        return $response;
    }
}
