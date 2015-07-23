<?php

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 23/07/2015
 * Time: 12:14
 */


class PermissionType{
    const API_KEY='api_key';
    //const OAUTH_2="OAUTH_2";
}

class PermissionLocation{

    const IN_HEADERS='in_headers';

    const IN_PARAMETERS='in_parameters';
}


class PermissionRepresentation {

    const DEFAULT_PERMISSION_NAME="identification";

    /**
     * @var String the name of the permission.
     */
    public $permission_name=PermissionRepresentation::DEFAULT_PERMISSION_NAME;

    /* @var string in PermissionLocation */
    public $location=PermissionLocation::IN_HEADER;


    /**
     * Inspired by UNIX files systems permissions.
     * https://en.wikipedia.org/wiki/File_system_permissions
     * @var int according to posix file systems permissions
     */
    public $access_rights=777;

}