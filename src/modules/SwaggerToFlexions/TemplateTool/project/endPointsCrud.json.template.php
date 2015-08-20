<?php

include  FLEXIONS_SOURCE_DIR.'/Shared.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $f->fileName = 'crudPathsFragment.json';
    $f->package = '';
}

/* TEMPLATES STARTS HERE -> */?>
{
    "paths" : {
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */

$counter=0;
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }


    //$pluralized=lcfirst(P
    //luralization::pluralize($name));
    $counter++;
    $lastEntity=false;
    if ($counter==count($d->entities)){
        $lastEntity=true;
    }

    // EXCLUSION Of CRUD
    // You can exclude entities containing a given string
    //
    $shouldBeExcluded=false;
    $exclusion=array();
    if(isset($excludeEntitiesWith)){
        $exclusion=$excludeEntitiesWith;
    }
    foreach ($exclusion as $exclusionString ) {
        if(strpos($name,$exclusionString)!==false){
            $shouldBeExcluded=true;
        }
    }
    if($shouldBeExcluded){
        continue;//Let's exclude this entity from the CRUD
    }

    // DO NO SUPPORT DELETION
    $isUndeletable=false;
    $undeletable=array();
    if(isset($unDeletableEntitiesWith)){
        $undeletable=$unDeletableEntitiesWith;
    }
    foreach ($undeletable as $undeletableString ) {
        if(strpos($name,$undeletableString)!==false){
            $isUndeletable=true;
        }
    }
    $deletionBlock=',
      "delete" : {
        "summary" : "Deletes a '.$name.'",
        "description" : "",
        "operationId" : "'.ucfirst($name).'",
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
              "name" : "api_key",
            "in" : "header",
            "required" : false,
            "type" : "string"
          },
          {
            "name" : "'.lcfirst($name).'Id",
            "in" : "path",
            "description" : "'.ucfirst($name).' id to delete",
            "required" : true,
            "type" : "integer",
            "format" : "int64"
          }
        ],
        "responses" : {
                "400" : {
                    "description" : "Invalid '.$name.' value"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      }';

    $block='
    "/'.$name.'" : {
        "post" : {
            "tags" : [
                "'.$name.'"
            ],
        "summary" : "Add a new '.$name.' to the system",
        "description" : "",
        "operationId" : "'.ucfirst($name).'",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
            "in" : "body",
            "name" : "'.lcfirst($name).'",
            "description" : "'.$name.' object that needs to be added to the store",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          }
        ],
        "responses" : {
                "405" : {
                    "description" : "Invalid input"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      },
      "put" : {
            "tags" : [
                "'.$name.'"
            ],
        "summary" : "Update an existing '.$name.'",
        "description" : "",
        "operationId" : "'.ucfirst($name).'",
        "consumes" : [
                "application/json"
            ],
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
              "in" : "body",
            "name" : "'.lcfirst($name).'",
            "description" : "'.ucfirst($name).' object that needs to be added to the store",
            "required" : true,
            "schema" : {
              "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          }
        ],
        "responses" : {
          "400" : {
             "description" : "Invalid ID supplied"
          },
          "404" : {
                    "description" : "'.ucfirst($name).' not found"
          },
          "405" : {
                    "description" : "Validation exception"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      }
    },
    "/'.$name.'/{'.lcfirst($name).'Id}" : {
        "get" : {
        "summary" : "Find '.$name.' by ID",
        "description" : "Returns a single '.$name.'",
        "operationId" : "'.ucfirst($name).'",
        "produces" : [
                "application/json"
            ],
        "parameters" : [
          {
             "name" : "'.lcfirst($name).'Id",
            "in" : "path",
            "description" : "ID of '.$name.' to return",
            "required" : true,
            "type" : "integer",
            "format" : "int64"
          }
        ],
        "responses" : {
                "200" : {
                    "description" : "successful operation",
            "schema" : {
                        "$ref" : "#/definitions/'.ucfirst($name).'"
            }
          },
          "400" : {
                    "description" : "Invalid ID supplied"
          },
          "404" : {
                    "description" : "'.ucfirst($name).' not found"
          }
        },
        "security" : [
          {
              "api_key" : []
          }
        ]
      }';
    if($isUndeletable==false){
        $block.=$deletionBlock;
    }
    $block.='
}';
    if(!$lastEntity){
        $block.=',';
    }
    echo $block;
}
?>


    }
}
<?php /*<- END OF TEMPLATE */?>