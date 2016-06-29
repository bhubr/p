<?php
/**
 * Common constructor class for some of the classes (User, Iban, Card so far)
 */
namespace Payname;

abstract class ProtectedConstructor implements \ArrayAccess {

	protected function __construct($fields)
	{
        $trace = debug_backtrace();
        $lastTrace = $trace[1];

        if(!array_key_exists('class', $lastTrace) || !preg_match('/' . preg_quote(static::class, '/') . '(Builder+)?/', $lastTrace['class'])) {
            throw new Exception('cannot be called from outside Payname\\User\\User* classes...\\n' . print_r($lastTrace, true));
        }
        $this->_load($fields);
	}

    /**
     * Load instance fields, erase any existing value
     *
     * Used to create/update an instance at once
     *
     * @param  array  $fields  Field values to set
     */
    protected function _load($fields = []) {
        if (empty($fields)) {
            return;
        }
        foreach ($fields as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /*-------------------------
     | DEPRECATION INFORMATION
     *-------------------------
     | From version 1.1 of the SDK, we return Cards, Docs, Ibans, etc. as objects and not arrays.
     | However, in order not to break compatibility with 1.0 and before, we still allow access through
     | implementation of ArrayAccess interface.
     */


    /**
     * ArrayAccess::offsetExists implementation
     */
    public function offsetExists($prop) {
        return array_search($prop, get_class_vars(self::class)) !== false;
    }

    /**
     * ArrayAccess::offsetGet implementation
     */
    public function offsetGet($prop) {
        return $this->$prop;
    }

    /**
     * ArrayAccess::offsetSet implementation
     */
    public function offsetSet($prop, $value) {
        if (is_null($prop)) {
            throw new Exception("You are not supposed to set keys on this ArrayObject");
        }
        $this->$prop = $value;
    }

    /**
     * ArrayAccess::offsetUnset implementation
     */
    public function offsetUnset($prop) {
        throw new Exception("You are not supposed to unset keys from this ArrayObject");
    }


}