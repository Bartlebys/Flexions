<?php

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 23/07/2015
 * Time: 12:14
 */

require_once FLEXIONS_ROOT_DIR.'flexions/core/Enum.php';

class PermissionType extends Enum{

    const API_KEY='api_key';
    const OAUTH_2="OAUTH_2";

    static protected function possibleValues(){
        return array(API_KEY,OAUTH_2);
    }
}



class PermissionLocation{

    const UNDEFINED='undefined';
    const IN_HEADERS='in_headers';
    const IN_PARAMETERS='in_parameters';

    static protected function possibleValues(){
        return array(IN_HEADERS,IN_PARAMETERS);
    }
}


class PermissionRepresentation {

    const DEFAULT_PERMISSION_NAME="identification";

    /**
     * @var String the name of the permission.
     */
    public $permission_name=PermissionRepresentation::DEFAULT_PERMISSION_NAME;


    /* @var string in PermissionLocation */
    private $_location=PermissionLocation::UNDEFINED;

    /**
     * @return string
     */
    public function getLocation() {
        return $this->_location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location) {
        $p=new PermissionLocation();
        if($p->isValid($location)){
            $this->_location = $location;
        }else{
            throw new exception("invalid PermissionLocation ".$location);
        }
    }



    /**
     * @return String
     */
    public function getPermissionName() {
        return $this->permission_name;
    }

    /**
     * @param String $permission_name
     */
    public function setPermissionName($permission_name) {
        $this->permission_name = $permission_name;
    }



}


class PermissionRepresentationWithAccessRights extends PermissionRepresentation{

    /**
     * Inspired by UNIX files systems permissions.
     * https://en.wikipedia.org/wiki/File_system_permissions
     * @var int according to posix file systems permissions
     */
     private $_access_rights=777;

    /**
     * @return int
     */
    public function getAccessRights() {
        return $this->_access_rights;
    }

    /**
     * @param int $access_rights
     */
    public function setAccessRights($access_rights) {
        $this->_access_rights = $access_rights;
    }

    /**
     * @return string in PermissionType
     */
    public function getPermissionType(){
        return PermissionType::API_KEY;
    }

}



class PermissionRepresentationOauth extends PermissionRepresentation {

    protected $location=PermissionLocation::IN_HEADERS;

    /**
     * Scopes let you specify exactly what type of access you need.
     * Scopes limit access for OAuth tokens.
     *
     * e.g :
     *
     *  "user:email"=>"access to the user mail",
     *  "repo:status"=>"grants read/write access to public and private repository "
     *
     * @var array
     */
    public $scopes=array();



    /**
     * @return string in PermissionType
     */
    public function getPermissionType(){
        return PermissionType::OAUTH_2;
    }

}