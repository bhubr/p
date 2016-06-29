<?php
/**
 * File Popup
 */

namespace Payname\Popup;



/**
 * Popup management
 *
 * @package  Payname
 * @subpackage  Popup
 */
class Popup {

    /**
     * Create a popup for current shop
     *
     * @param  array  $options  Creation options:
     * - `amount`           (float)   *Required.* Amount to pay in the popup
     * - `callback_ok`      (string)  *Optional.* Once payment is finished,
     *   redirect to this URL instead of closing the popup
     * - `callback_cancel`  (string)  *Optional.* If user cancels (button),
     *   redirect to this URL instead of closing the popup
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  string  URL to open in popup
     */
    public static function create($options) {
        $aRes = \Payname\Payname::post(
            array(
                'url' => '/popup',
                'postData' => $options
            )
        );
        return $aRes['data']['url'];
    }
}
