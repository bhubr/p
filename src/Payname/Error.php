<?php
/**
 * File src/Payname/Error.php
 */

namespace Payname;

/**
 * Payname\Error class.
 *
 * Encapsulate error returns from API.
 *
 * @since v1.1
 */
class Error {

	const ERROR = 1;
	const WARNING = 2;


	/**
	 * Error code, e.g. WUC120.
	 */
	protected $_code;


	/**
	 * Error message.
	 */
	protected $_message;


	/**
	 * Error details, if any is given back by the API.
	 */
	protected $_details;


	/**
	 * Payload's data field, if set
	 */
	protected $_data;


	/**
	 * ID of the request
	 */
	protected $_requestId;


	/**
	 * Constructor: set private, since we build API Errors only from responses returned by API calls
	 */
	private function __construct($code, $message, $details, $requestId, $data)
	{
		$this->_code = $code; 
		$this->_type = substr($code, 0, 1) === 'W' ? self::WARNING : self::ERROR;
		$this->_message = $message;
		$this->_details = $details;
		$this->_requestId = $requestId;
		$this->_data = $data;
		return $this;
	}


	/**
	 * Build Error object from API response
	 *
	 * @param  array  $response  parsed response returned by the server
	 */
	public static function fromResponse($response)
	{
        $code = self::extractErrorCode($response);
        $message = $response['msg'];
        $details = array_key_exists('details', $response) ? $response['details'] : null;
        $requestId = self::extractRequestId($response);
        $data = array_key_exists('data', $response) ? $response['data'] : null;
        return new self($code, $message, $details, $requestId, $data);
	}


	/**
	 * Is the error a true error (not a warning)
	 *
	 * @return bool  true if the API call has returned a true error
	 */
	public function isError()
	{
		return $this->_type === self::ERROR;
	}


	/**
	 * Is the error only a warning.
	 *
	 * A warning usually means that the main API call's functionality has been delivered,
	 * but some issues were encountered.
	 *
	 * @return bool  true if the API call has returned a partial success with a warning
	 */
	public function isWarning()
	{
		return $this->_type === self::WARNING;
	}


	/**
	 * Return the error code of the last API call.
	 *
	 * @return string  6-characters error code.
	 */
	public function getCode()
	{
		return $this->_code;
	}


	/**
	 * Return the error message returned by the last API call.
	 *
	 * @return string  Error message
	 */
	public function getMessage()
	{
		return $this->_message;
	}


	/**
	 * Return the details associated to the error, if any.
	 *
	 * @return array  An array containing additional details on the error.
	 */
	public function getDetails()
	{
		return $this->_details;
	}


	/**
	 * Id of the API call. May be used to communicate with our support, so that we can retrieve and troubleshoot the call.
	 *
	 * @return int  API call ID
	 */
	public function getRequestId()
	{
		return $this->_requestId;
	}


	/**
	 * Data returned by the API call, if any. Usually empty.
	 *
	 * @return 
	 */
	public function getData()
	{
		return $this->_data;
	}


	/**
	 * Concatenate the object properties, in order to use as Exception message
	 *
	 * @return string  Message to be used as exception message
	 */
    public function formatAsExceptionMessage() {
        $exceptionMessage = sprintf("%s - %s (id requête : %d)", $this->_code, $this->_message, $this->_requestId);
        $exceptionMessage .= empty($this->_data) ? '' : ' - ' . json_encode($this->_data, JSON_UNESCAPED_UNICODE);
        return $exceptionMessage;
    }


    /**
     * Extract the error code from the payload returned by the API.
     *
     * New convention states that request id should be set in the payload's code field.
     * Old convention (still used in places) stated that it should be in error field.
     *
     * @param  array  $response  The parsed response sent back by the API
     * @return string            The 6-character error code string
     */
    private static function extractErrorCode($response) {
        if(array_key_exists('code', $response)) {
            return $response['code'];
        }
        else if(array_key_exists('error', $response)) {
            return $response['error'];
        }
        else {
            return 'n.a.';
        }

    }


    /**
     * Extract the request id from the payload returned by the API.
     *
     * New convention states that request id should be set in the payload's id field.
     * However there are still cases where the API follows the old convention, with the id sent under
     * the logs->log field
     *
     * @param  array  $response  The parsed response sent back by the API
     * @return int               The id of the request
     */
    private static function extractRequestId($response) {
        if(array_key_exists('id', $response)) {
            return $response['id'];
        }
        else if(array_key_exists('logs', $response) && array_key_exists('log', $response['logs'])) {
            return $response['logs']['log'];
        }
        else {
            return -1;
        }
    }

}