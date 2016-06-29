<?php

/**
 * File Auth
 */

namespace Payname\Auth;



/**
 * Authentification manager
 *
 * @package  Payname
 * @subpackage  Auth
 */
class Auth {

    /**
     * Ask for a token
     */
    public static function token() {
        $options = array(
            'url' => '/auth/token',
            'postData' => array(
                'ID' => \Payname\Config::id(),
                'secret' => \Payname\Config::secret()
            )
        );

        return \Payname\Payname::post($options);
    }


    /**
     * Refresh a token
     */
    public static function refreshToken() {
        $options = array(
            'url' => '/auth/refresh_token',
            'postData' => array(
                'ID' => \Payname\Config::id(),
                'token' => \Payname\Payname::token()
            )
        );

        return \Payname\Payname::post($options);
    }
}
