<?php

namespace Src\Controllers;

class Controller {

    protected $container;

    public function __construct( $container)
    {
       $this->container = $container;
       
    }
}