<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/**
 *  This is a special wrapper to allow to generate action without execution implementation.
 *
 * Sample usage in a project descriptor :
 *
 *   {
 *      "path": "${BARTLEBYS_MODULE_DIR}/templates/actions/base.operation.swift.template.php",
 *      "description": "Bartleby's base operation swift template",
 *      "variables": {
 *          "entityClass": "Block",
 *          "actionName":"UploadBlockBase"
 *      }
 *  }
 */


$shouldImplementExecuteBlock=false;
$entityClass=Registry::instance()->valueForKey('entityClass');// Block
$actionName=Registry::instance()->valueForKey('actionName');// UploadBlockBase
$entity=strtolower($entityClass); // block
$entities=Pluralization::pluralize($entity);//blocks

if(isset($entityClass) && isset($actionName)){

    // We create an action to be able to use cuds.swift

    $p=new PropertyRepresentation();
    $p->name=$entity;
    $p->instanceOf=$entityClass;
    $p->method=Method::IS_INSTANCE;
    $p->scope=Scope::IS_PUBLIC;
    $p->mutability=Mutability::IS_VARIABLE;
    $p->metadata=[];

    /* @var $d ActionRepresentation */
    $d=new ActionRepresentation();
    $d->class=$actionName;
    $d->name=NULL;
    $d->collectionName=$entities;
    $d->parameters=[$p];
    $d->metadata=['urdMode'=>false];

    include __DIR__ . '/cuds.swift.php';

}else{
    return NULL;
}


