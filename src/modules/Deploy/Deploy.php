<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 17:11
 */

namespace Flexions;

require_once FLEXIONS_ROOT_DIR.'flexions/core/Hypotypose.class.php';
require_once FLEXIONS_ROOT_DIR.'flexions/core/Flexed.class.php';

class Deploy {

    /*@var $_hypothypose Hypotypose */
    protected $_hypothypose;

    function __construct(\Hypotypose $hypotypose){
        $this->_hypothypose=$hypotypose;
    }


}