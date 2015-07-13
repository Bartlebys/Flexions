<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 16:23
 */

// Dependency


namespace Flexions;

require_once FLEXIONS_MODULES_DIR."Deploy/Deploy.php";
//require_once FLEXIONS_MODULES_DIR."Deploy/dependencies/FtpClient/autoload.php";

class FTPDeploy extends  Deploy{

    function __construct(\Hypotypose $hypotypose){
        $this->_hypothypose=$hypotypose;
    }



}