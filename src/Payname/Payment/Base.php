<?php
/**
 * File Base
 */
namespace Payname\Payment;

class Base {

	/**
	 * Class constructor
	 *
	 * @param  array  $fields  Field initial values
	 */
    protected function __construct($fields) {
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
}