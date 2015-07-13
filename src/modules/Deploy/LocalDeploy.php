<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 13/07/15
 * Time: 16:23
 */

namespace Flexions;

require_once "Deploy.php";

class LocalDeploy extends Deploy {

    function __construct(\Hypotypose $hypotypose){
        $this->_hypothypose=$hypotypose;
    }

    /**
     * @param $package
     * @param $absoluteDestination
     * @param bool|true $removelastpackagecomponent most of the time you want to remove for example the /php/ folder
     * @throws \Exception
     */
    function copyFiles($package,$absoluteDestination,$removelastpackagecomponent=true){
        fLog(cr().'Local deploy is copying the package '.$package.cr().cr(),true);
        if (isset($this->_hypothypose)) {
            $list = $this->_hypothypose->getFlatFlexedList();
            /* @var $flexed Flexed */
            foreach ( $list as $flexed ) {
                $filePath=$flexed->packagePath.$flexed->fileName;
                $packPosition=stripos($flexed->packagePath.$flexed->fileName,$package);
                //fLog('  '.$flexed->packagePath.$flexed->fileName.' '.$package.'->'.$packPosition.cr(),true);
                // This file should be copied
                if ($removelastpackagecomponent==true){
                    $packagecomponents=explode('/',$flexed->package);
                    array_shift($packagecomponents); // remove the last component
                    $joinedpackage=join('/',$packagecomponents);
                    $destination=$absoluteDestination.$joinedpackage.$flexed->fileName;
                }else{
                    $destination=$absoluteDestination.$flexed->package.$flexed->fileName;
                }

                if($packPosition!=false){

                    if(!file_exists( dirname ($destination))) {
                        mkdir(dirname($destination), 0777, true);
                    }
                    fLog('COPYING FROM : '.$filePath.cr().'TO'.$destination.cr(),true);
                    copy($filePath,$destination);
                }else{
                }
            }
        }else{
            throw new \Exception('LocalDeploy requires a valid hypotypose');
        }
    }
}