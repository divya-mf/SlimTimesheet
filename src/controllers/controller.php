<?php

namespace Src\Controllers;

class Controller {

    

    public function __construct( $container)
    {
       //$this->container = $container;
       
    }

    /**
	 * verifyToken
     * verifies the token.
     *
     * 
     * returns {array}
     */
	public function verifyToken($token)
	{
        if($token==bin2hex(openssl_random_pseudo_bytes(8)))
        return true;
    }

    /**
	 * createToken
     * creates a unique token.
     *
     * 
     * returns {array}
     */
	public function createToken()
	{
        $token=bin2hex(openssl_random_pseudo_bytes(8));
        return $token;
    }



}