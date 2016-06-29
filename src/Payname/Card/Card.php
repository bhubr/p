<?php

/**
 * File card
 */

namespace Payname\Card;

use \Payname\Payname;
use \Payname\CRUDInterface;
use \Payname\ProtectedConstructor;


/**
 * Card
 *
 * @package  Payname
 * @subpackage  Card
 */
class Card extends ProtectedConstructor implements CRUDInterface {

    // no public fields, card data should not be stored
    // public function __construct($email, $fields = []) {
    //     parent::__construct();
    // }
    public $hash;
    public $number;
    public $email;
    public $is_prod;
    public $type;
    public $user;
    public $expiry_year;
    public $expiry_month;


    /**
     * Create a new card token
     *
     * @param  array  $props  Create-time properties:
     * - number: string, Number of card
     * - expiry: array, Expiry date of the card, with:
     *   - year: integer
     *   - month: integer
     * - security: string, Security code (CVV, CVC, etc.)
     * - user: string, Email or hash of the owner of the card
     *
     * @return  array  API response data
     */
    public static function create($props = []) {
        $requestOptions = array(
            'url' => '/token',
            'postData' => $props
        );
        $response = Payname::post($requestOptions);
        $data = $response['data'];
        return new Card($data);
    }

    /**
     * Get an existing Card
     *
     * @param  string  $userHash  Hash of parent user
     * @param  string  $cardHash      Hash of the Doc to get
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return  Doc  Corresponding Doc
     */
    public static function get($cardHash) {
        $requestOptions = array(
            'url' => '/card/' . $cardHash
        );
        $response = Payname::get($requestOptions);
        return new Card($response['data']);
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
            'url' => '/user/' . $userHash . '/card'
        );
        $response = Payname::get($requestOptions);
        return array_map(function($card) use($userHash) {
            $cardProperties = array_merge(['user' => $userHash], $card);
            return new Card($cardProperties);
        }, $response['data']);
     }


    /**
     * Delete an existing Card
     *
     * @throw  \Payname\Exception  On API error
     *
     * @return Card  Corresponding Card
     */
    public function delete() {
        $requestOptions = array(
            'url' => '/card/' . $this->hash
        );
        $response = Payname::delete($requestOptions);
        return (isset($response['data']) ? $response['data'] : null);
    }

    public function update() {
        throw new \Payname\Exception('A card cannot be updated');
    }


}