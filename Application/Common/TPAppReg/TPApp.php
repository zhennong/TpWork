<?php
/**
 * Created by PhpStorm.
 * User: wodrow
 * Date: 2/23/16
 * Time: 11:40 AM
 */

namespace Common\TPAppReg;


class TPApp
{
    public $version = '1.0';

    public function getVersion(){
        return $this->version;
    }

    public function __construct($config=[])
    {
        TP::$app = $this;
    }

    public function run(){

    }
}