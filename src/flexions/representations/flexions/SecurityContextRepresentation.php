<?php

/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 23/07/2015
 * Time: 12:10
 */

class RelationToPermission{
    const UNDEFINED='undefined';
    const REQUIRES='requires';  // authentication required
    const PROVIDES='provides';  // e.g log in
    const DISCARDS='discards';  // e.g log out

}


class SecurityContextRepresentation {

    /**
     * @var PermissionRepresentation
     */
    public  $permission;


    /**
     * @var string one of RelationToPermission consts
     */
    public $relation=RelationToPermission::UNDEFINED;


}
